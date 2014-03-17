<?php

/*
 * For a given eid/uid, returns:
 * 'Cancelled' - The event has been canceled.
 * 'Manager' - This event is assigned to that user as 'Manager'.
 * 'Coordinator' - This event is assigned to that user as 'Coordinator'.
 * 'Unassigned' - The user opted in to this event, but is not assigned.
 * ''  - The user did not opt in to this event.
 */
function brick_get_event_assigned_status($eid, $uid = NULL) {
  if (brick_event_is_cancelled($eid)) {
    return 'Cancelled';
  }

  if (!$uid) {
    global $user;
    $uid = $user->uid;
  }

  $r = db_query('SELECT field_optin_selected_value, field_optin_role_value
			FROM field_data_field_optin_selected selected
			INNER JOIN field_data_field_optin_event event ON (selected.entity_id = event.entity_id)
			INNER JOIN field_data_field_optin_person person ON (selected.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_role role ON (selected.entity_id = role.entity_id)
			WHERE	field_optin_person_uid = :uid AND
				field_optin_event_nid = :nid
			ORDER BY field_optin_selected_value DESC', // We grab the first, so prefer Yes to Staged
    array(
      ':uid' => $uid,
      ':nid' => $eid
    ));

  $row = $r->fetchAll();
  // If we get any result, we know they've opted in.
  if ($row) {
    if ($row[0]->field_optin_selected_value == 'Yes') {
      return $row[0]->field_optin_role_value;
    }
    return 'Unassigned';
  }

  return '';
}

/**
 * Return a map of role to userId actually assigned to that role for a given event.
 * Staged users are not returned.
 */
function brick_get_assignment_map($eid) {
  $r = db_query('SELECT field_optin_selected_value, field_optin_role_value, field_optin_person_uid
                        FROM field_data_field_optin_selected selected
                        INNER JOIN field_data_field_optin_event event ON (selected.entity_id = event.entity_id)
                        INNER JOIN field_data_field_optin_person person ON (selected.entity_id = person.entity_id)
                        INNER JOIN field_data_field_optin_role role ON (selected.entity_id = role.entity_id)
                        WHERE field_optin_event_nid = :nid',
    array(':nid' => $eid));

  $rows = $r->fetchAll();
  $ret = array();
  // If we get any result, we know they've opted in.
  foreach ($rows as $row) {
    if ($row->field_optin_selected_value == 'Yes') {
      $ret[$row->field_optin_role_value] = $row->field_optin_person_uid;
    }
  }

  return $ret;
}

function brick_ajax_stage($nid) {
  $optin_node = node_load($nid);
  $uid = $optin_node->field_optin_person['und'][0]['uid'];

  $status = brick_stage($nid, 0);
  $num = brick_get_num_assigned_from_pager($uid);

  echo json_encode(array(
    'uid' => $uid,
    'status' => $status,
    'num_assigned' => $num
  )), "\n";
}

// Data is passed in via $_POST params
function brick_ajax_check_staged() {
  $bad = array();

  foreach ($_POST as $status => $nids) {
    foreach ($nids as $nid) {
      if (!brick_check_stage($nid, $status)) {
        $bad[] = $nid;
      }
    }
  }

  if (empty($bad)) {
    echo json_encode(array(
      'status' => 'success',
    )), "\n";
  }
  else {
    echo json_encode(array(
      'status' => 'fail',
      'bad_nids' => $bad,
    )), "\n";
  }
}

// Returns whether the given NID matches the given staged status
function brick_check_stage($nid, $status) {
  $optin_node = node_load($nid);

  $selected = 'No';
  if (array_key_exists('field_optin_selected', $optin_node) &&
    array_key_exists('und', $optin_node->field_optin_selected)
  ) {
    $selected = $optin_node->field_optin_selected['und'][0]['value'];
  }

  return ($selected == $status);
}

function brick_stage($nid, $redirect = 1) {
  $optin_node = node_load($nid);

  $save = 1;
  $selected = '';
  if (array_key_exists('field_optin_selected', $optin_node) &&
    array_key_exists('und', $optin_node->field_optin_selected)
  ) {
    $selected = $optin_node->field_optin_selected['und'][0]['value'];
  }

  switch ($selected) {
    case 'No':
    case '':
      $selected = 'StagedYes';
      break;
    case 'StagedYes':
      $selected = 'No';
      break;
    case 'StagedNo':
      $selected = 'Yes';
      break;
    case 'Yes':
      $selected = 'StagedNo';
      break;
    default:
      $save = 0; // If we're not changing it, don't save it
  }

  if ($save) {
    $optin_node->field_optin_selected['und'][0]['value'] = $selected;
    node_save($optin_node);
  }

  if ($redirect) {
    brick_go_back();
  }

  return $selected;
}

function brick_assign_staged() {
  brick_change_staged('StagedYes', 'Yes');
  brick_change_staged('StagedNo', 'No');

  brick_add_assigned_rsvps();
  brick_remove_unassigned_rsvps();

  brick_go_back();
}

function brick_clear_staged() {
  brick_change_staged('StagedYes', 'No');
  brick_change_staged('StagedNo', 'Yes');

  brick_go_back();
}

function brick_email_assigned($eid, $userid, $role) {
  $event = node_load($eid);
  global $user;
  $account = user_load($userid);

  $res = db_query("SELECT field_data_body.body_value as text FROM node
      left join field_data_body on node.nid = entity_id
      WHERE node.title LIKE 'Assign " . $role . " Email Template'");
  $template = $res->fetchField();


  $params['body'] = brick_expand($template, $eid, $account, NULL);
  $params['subject'] = "One Brick Event Assignment - " . $event->title . "(";
  $params['email'] = $user->mail;

  drupal_mail('brick', 'assign', $account->mail, language_default(), $params, $user->mail);
}

function brick_change_staged($oldVal, $newVal) {
  $chapterId = $_SESSION['CHAPTER'];

  $startDate = brick_get_referer_date();
  $endDate = clone $startDate;
  $endDate->add(new DateInterval('P1M'));

  $res = db_query('SELECT selected.entity_id
			FROM field_data_field_optin_selected selected
			INNER JOIN field_data_field_optin_event event ON (selected.entity_id = event.entity_id)
			INNER JOIN field_data_field_event_chapter chapter ON (field_optin_event_nid = chapter.entity_id)
			INNER JOIN field_data_field_event_date date ON (field_optin_event_nid = date.entity_id)
			WHERE field_optin_selected_value = :oldVal
				AND field_event_chapter_nid = :chapterId
				AND field_event_date_value >= :startDate
				AND field_event_date_value < :endDate',
    array(
      ':oldVal' => $oldVal,
      ':chapterId' => $chapterId,
      ':startDate' => $startDate->format('Y-m-d'),
      ':endDate' => $endDate->format('Y-m-d')
    ));

  $nids = $res->fetchCol(0);

  foreach (node_load_multiple($nids) as $node) {
    if ($newVal === 'Yes' and $oldVal === 'StagedYes') {
      watchdog('email', '<pre>' . print_r($node, TRUE) . '</pre>');
      brick_email_assigned($node->field_optin_event['und']['0']['nid'],
        $node->field_optin_person['und']['0']['uid'],
        $node->field_optin_role['und']['0']['value']);
    }
    $node->field_optin_selected['und'][0]['value'] = $newVal;
    node_save($node);
  }
}

function brick_go_back() {
  // Do a full page-refresh for now.
  if ($_SERVER['HTTP_REFERER']) {
    drupal_goto($_SERVER['HTTP_REFERER']);
  }
  else {
    drupal_goto('webmgr/assign-events');
  }
}

function brick_get_referer_date() {
  $referer = $_SERVER['HTTP_REFERER'];
  if (!$referer) {
    drupal_goto('webmgr/assign-events');
  }

  $date = new DateTime(date('Y-m'));

  // Mine the month out of the URL, if we can
  $parts = parse_url($referer);
  if (array_key_exists('query', $parts)) {
    parse_str($parts['query'], $query);
    if ($query && array_key_exists('date', $query)) {
      $date = new DateTime($query['date']);
    }
  }

  return $date;
}

function brick_get_num_assigned_from_pager($uid) {
  $month = brick_get_referer_date()->format('Y-m');
  return brick_get_num_assigned($uid, $month);
}

function brick_get_num_assigned_from_monthid($uid, $monthId) {
  //drupal_set_message("Month: " . $monthId);
  $month = node_load($monthId)->field_availability_month['und'][0]['value'];
  return brick_get_num_assigned($uid, $month);
}

function brick_get_num_assigned($uid, $month) {
  $chapterId = $_SESSION['CHAPTER'];

  $startDate = new DateTime($month);
  $endDate = clone $startDate;
  $endDate->add(new DateInterval('P1M'));

  $res = db_query('SELECT COUNT(*)
			FROM field_data_field_optin_selected selected
			INNER JOIN field_data_field_optin_event event ON (selected.entity_id = event.entity_id)
			INNER JOIN field_data_field_optin_person person ON (selected.entity_id = person.entity_id)
			INNER JOIN field_data_field_event_date date ON (field_optin_event_nid = date.entity_id)
			INNER JOIN field_data_field_event_chapter chapter ON (field_optin_event_nid = chapter.entity_id)
			INNER JOIN field_data_field_event_status status ON (field_optin_event_nid = status.entity_id)
			WHERE field_optin_person_uid = :uid
				AND field_optin_selected_value IN (\'StagedYes\', \'Yes\')
				AND field_event_chapter_nid = :chapterId
				AND field_event_date_value >= :startDate
				AND field_event_date_value < :endDate
				AND field_event_status_value = \'Open\'',
    array(
      ':uid' => $uid,
      ':chapterId' => $chapterId,
      ':startDate' => $startDate->format('Y-m-d'),
      ':endDate' => $endDate->format('Y-m-d')
    ));

  $count = $res->fetchCol();
  return $count[0];
}

function brick_add_assigned_rsvps() {
  $chapterId = $_SESSION['CHAPTER'];

  $startDate = brick_get_referer_date();
  $endDate = clone $startDate;
  $endDate->add(new DateInterval('P1M'));

  $res = db_query('SELECT field_optin_person_uid,
				field_optin_event_nid,
				field_optin_role_value
			FROM field_data_field_optin_selected selected
			INNER JOIN field_data_field_optin_event event ON (selected.entity_id = event.entity_id)
			INNER JOIN field_data_field_event_chapter chapter ON (field_optin_event_nid = chapter.entity_id)
			INNER JOIN field_data_field_event_date date ON (field_optin_event_nid = date.entity_id)
			INNER JOIN field_data_field_optin_person person ON (person.entity_id = selected.entity_id)
			INNER JOIN field_data_field_optin_role role ON (role.entity_id = selected.entity_id)
			WHERE field_optin_selected_value = \'Yes\'
				AND field_event_chapter_nid = :chapterId
				AND field_event_date_value >= :startDate
				AND field_event_date_value < :endDate',
    array(
      ':chapterId' => $chapterId,
      ':startDate' => $startDate->format('Y-m-d'),
      ':endDate' => $endDate->format('Y-m-d')
    ));

  foreach ($res as $row) {
    $eid = $row->field_optin_event_nid;
    $uid = $row->field_optin_person_uid;
    $role = $row->field_optin_role_value;
    $note = '';
    $created_when_assigned = '1';

    $rsvp_id = brick_get_rsvp_id($eid, $uid);
    if (!$rsvp_id) {
      brick_add_rsvp($eid, $uid, $note, 1, $role, $created_when_assigned);
    }
    else {
      $node = node_load($rsvp_id);
      $node->field_rsvp_role['und'][0]['value'] = $role;
      node_save($node);
    }
  }
}

function brick_remove_unassigned_rsvps() {
  $chapterId = $_SESSION['CHAPTER'];

  $startDate = brick_get_referer_date();
  $endDate = clone $startDate;
  $endDate->add(new DateInterval('P1M'));

  $res = db_query('SELECT field_optin_person_uid,
				field_optin_event_nid
			FROM field_data_field_optin_selected selected
			INNER JOIN field_data_field_optin_event event ON (selected.entity_id = event.entity_id)
			INNER JOIN field_data_field_event_chapter chapter ON (field_optin_event_nid = chapter.entity_id)
			INNER JOIN field_data_field_event_date date ON (field_optin_event_nid = date.entity_id)
			INNER JOIN field_data_field_optin_person person ON (person.entity_id = selected.entity_id)
			WHERE field_optin_selected_value = \'No\'
				AND field_event_chapter_nid = :chapterId
				AND field_event_date_value >= :startDate
				AND field_event_date_value < :endDate',
    array(
      ':chapterId' => $chapterId,
      ':startDate' => $startDate->format('Y-m-d'),
      ':endDate' => $endDate->format('Y-m-d')
    ));

  foreach ($res as $row) {
    $eid = $row->field_optin_event_nid;
    $uid = $row->field_optin_person_uid;

    $rsvp_id = brick_get_rsvp_id($eid, $uid);
    if (!$rsvp_id) {
      continue;
    }

    $node = node_load($rsvp_id);

    // If auto-created, delete it entirely
    if (array_key_exists('und', $node->field_rsvp_created_when_assigned) &&
      $node->field_rsvp_created_when_assigned['und'][0]['value']
    ) {
      node_delete($rsvp_id);

      // Otherwise, just change the role back to Volunteer
    }
    else {
      $node->field_rsvp_role['und'][0]['value'] = 'Volunteer';
      node_save($node);
    }
  }
}

?>
