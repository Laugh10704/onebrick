<?php
// $Id: brick_rsvp.php,v 1.37 2013/12/18 03:08:56 jordan Exp $

function brick_get_rsvp_list($eid) {
  $q = "
  SELECT DISTINCT
    field_revision_field_rsvp_person.field_rsvp_person_uid as uid,
    field_revision_field_rsvp_role.field_rsvp_role_value as role,
    users.mail,
    users.signature as fullname,
    field_revision_field_rsvp_event.entity_id as entity_id
  FROM field_revision_field_rsvp_event
    left join field_revision_field_rsvp_person
      on field_revision_field_rsvp_event.entity_id=field_revision_field_rsvp_person.entity_id
    left join field_revision_field_rsvp_role
      on field_revision_field_rsvp_event.entity_id=field_revision_field_rsvp_role.entity_id
    left join users
      on field_revision_field_rsvp_person.field_rsvp_person_uid=users.uid
    join node
      on node.nid = field_revision_field_rsvp_event.entity_id
  WHERE node.status = 1 AND field_rsvp_event_nid = " . $eid;
  return (db_query($q));
}

function brick_get_attendee_list($eid) {
  $q = "
  SELECT DISTINCT
    field_revision_field_rsvp_person.field_rsvp_person_uid as uid,
    field_revision_field_rsvp_role.field_rsvp_role_value as role,
    users.mail,
    users.signature as fullname,
    field_revision_field_rsvp_event.entity_id as entity_id
  FROM field_revision_field_rsvp_event
    left join field_revision_field_rsvp_person
      on field_revision_field_rsvp_event.entity_id=field_revision_field_rsvp_person.entity_id
    left join field_revision_field_rsvp_role
      on field_revision_field_rsvp_event.entity_id=field_revision_field_rsvp_role.entity_id
    left join users
      on field_revision_field_rsvp_person.field_rsvp_person_uid=users.uid
    join node
      on node.nid = field_revision_field_rsvp_event.entity_id
		left join field_revision_field_rsvp_attended on field_revision_field_rsvp_attended.entity_id = field_revision_field_rsvp_event.entity_id
		where field_rsvp_attended_value = 1 and
  		node.status = 1 AND field_rsvp_event_nid = " . $eid;

  return (db_query($q));
}

function brick_remap_manager_coordinator_rsvps($eid, $managers, $coordinators) {
  $currentRSVPs = brick_get_rsvp_list($eid)->fetchAll();

  $idToRSVPId = array();

  $currentManagers = NULL;
  $currentCoordinators = NULL;
  $currentVolunteers = NULL;
  $wasManagerAVolunteer = NULL;

  // first entry is uid, second entry is their role
  foreach ($currentRSVPs as $rsvp) {
    if ($rsvp->role == 'Manager') {
      $currentManagers[] = $rsvp->uid;
    }
    else {
      if ($rsvp->role == 'Coordinator') {
        $currentCoordinators[] = $rsvp->uid;
      }
      else {
        $currentVolunteers[] = $rsvp->uid;
      }
    }
    // a map from uid to rsvp id
    $idToRSVPId[$rsvp->uid] = $rsvp->entity_id;
  }

  remap_manager_data($eid, $managers, $currentManagers, $currentVolunteers, 'Manager', $idToRSVPId);
  remap_manager_data($eid, $coordinators, $currentCoordinators, $currentVolunteers, 'Coordinator', $idToRSVPId);
}

function remap_manager_data($eid, $newList, $oldList, $volunteers, $userType, $idToRSVPId) {
  $toAdd = $oldList == NULL ? $newList : array_diff($newList, $oldList);
  $toRemove = $oldList == NULL ? array() : array_diff($oldList, $newList);

  // people we need to add
  foreach ($toAdd as $addMe) {
    $assigned = NULL;

    // if this manager WAS a volunteer, we remove the old rsvp and add them with the "assigned" flag to "TRUE"
    if ($volunteers && in_array($addMe, $volunteers)) {
      $assigned = 'TRUE';

      $rsvpId = $idToRSVPId[$addMe];

      brick_delete_rsvp($rsvpId);
    }

    brick_add_rsvp($eid, $addMe, '', 1, $userType, $assigned);


    // is there an existing opt-in? if not, we create one with "assigned on created" set to true
    $optinRes = brick_optin_get_existing($addMe, $eid, $userType);

    if ($optinRes->rowCount() == 0) {
      // opt-in created wtih asc set to true
      brick_optin_add($toAdd, $eid, $userType, brick_optin_pref_val_to_string(1), 'TRUE');
    }
  }

  // people we need to remove -> we just re-add them as a volunteer and set assigned flag to false
  foreach ($toRemove as $removeMe) {
    $rsvpId = $idToRSVPId[$removeMe];

    brick_delete_rsvp($rsvpId);

    brick_add_rsvp($eid, $removeMe, '', 1, 'Volunteer', 'FALSE');

    // remove the opt-in if the 'assigned when created' is set to true (the user didn't manually create the opt-in)
    $optinRes = brick_optin_get_existing($removeMe, $eid, $userType);
    $optinId = $optinRes->fetchColumn();

    $optNd = node_load($optinId);

    if ($optNd && $optNd->field_optin_created_when_assign &&
      $optNd->field_optin_created_when_assign['und'][0]['value'] == 'TRUE'
    ) {
      node_delete($optinId);
    }
  }
}

function brick_rsvp_count($eid) {
  $q = "SELECT count(field_data_field_rsvp_event.entity_id) FROM field_data_field_rsvp_person
	  LEFT JOIN field_data_field_rsvp_event ON field_data_field_rsvp_person.entity_id = field_data_field_rsvp_event.entity_id
		INNER JOIN node on node.nid = field_data_field_rsvp_event.entity_id
		WHERE node.status and field_data_field_rsvp_event.field_rsvp_event_nid = " . $eid;
  return (db_query($q)->fetchField());
}

function brick_attended_count($eid) {
  $q = "
		SELECT count(*) FROM `field_revision_field_rsvp_attended`
		LEFT JOIN field_data_field_rsvp_event ON field_revision_field_rsvp_attended.entity_id = field_data_field_rsvp_event.entity_id
		where field_rsvp_attended_value = 1 and
		field_data_field_rsvp_event.field_rsvp_event_nid = " . $eid;
  return (db_query($q)->fetchField());
}

function brick_event_full($eid) {
  $c = brick_rsvp_count($eid);
  $event_node = node_load($eid);
  return ($c >= $event_node->field_event_max_rsvp_capacity['und'][0]['value']);
}

function brick_manager_add_rsvp_ajax($eid, $uid) {
  brick_add_rsvp($eid, $uid, '', 0);

  $commands = array();
  $commands[] = ajax_command_replace('.row-user-' . $uid . ' .add-user-link', 'User Added!');

  return array('#type' => 'ajax', '#commands' => $commands);
}

function brick_manager_add_rsvp($eid, $uid) {
  brick_add_rsvp($eid, $uid, '', 0);
  drupal_set_message(t('User added to event'));

  // go back to the same page
  drupal_goto($_SERVER['HTTP_REFERER']);
}

/**
 * Add an RSVP to the database. Uses a semaphore to prevent duplicate rsvps from being added.
 *
 * returns the RSVP node if the RSVP was successfully added to the database. Returns NULL if the
 * RSVP already exists.
 */
function brick_add_rsvp($eid, $uid, $note, $public = 1, $role = 'Volunteer', $assigned = '') {

  // synchronization
  $key = 928313;

  $semaphore = sem_get($key);

  try {
    sem_acquire($semaphore); //blocking
    $node = NULL;

    // check to see if one already exists before adding
    if (brick_get_rsvp_id($eid, $uid) == 0) {
      $node = new StdClass();
      $node->type = 'rsvp';
      $node->status = 1;
      $node->title = 'new rsvp';
      $node->uid = $uid;
      $node->created = REQUEST_TIME;
      $node->changed = REQUEST_TIME;
      $node->comment = 2;
      $node->language = 'und';
      $node->field_rsvp_event['und'][0]['nid'] = $eid;
      $node->field_rsvp_person['und'][0]['uid'] = $uid;
      $node->field_rsvp_role['und'][0]['value'] = $role;
      $node->field_rsvp_note['und'][0]['value'] = $note;
      $node->field_public['und'][0]['value'] = $public;

      if ($assigned) {
        $node->field_rsvp_created_when_assigned['und'][0]['value'] = $assigned;
      }

      $node = node_submit($node);
      node_save($node);
    }
    sem_release($semaphore);
    return $node;

  } catch (Exception $e) {
    sem_release($semaphore);
    throw $e;
  }
}

function brick_rsvp_get_attended($rsvp_id) {
  $node = node_load($rsvp_id);
  return $node->field_rsvp_attended['und'][0]['value'];
}

function brick_rsvp_set_attended($rsvp_id, $attended) {
  $node = node_load($rsvp_id);
  $node->field_rsvp_attended['und'][0]['value'] = $attended;
  node_save($node);
}

function brick_dounrsvp() {
  $eid = $_POST["nid"];
  $uid = $_POST["uid"];

  $r = brick_get_rsvp_query($eid, $uid);

  $rsvpId = $r->fetchField();

  watchdog("INFO", "DELETING" . $rsvpId);

  brick_delete_rsvp($rsvpId);

  $user_node = user_load($uid);
  send_rsvp_emails($eid, $user_node->signature, $user_node->mail, "unRSVP note", TRUE);


  drupal_set_message(t('You have been removed from this event. Thank you for letting us know.'), 'status');

  // go back to the same page
  drupal_goto($_SERVER['HTTP_REFERER']);
}

function brick_delete_rsvp($rsvpId) {
  node_delete($rsvpId);
}

function brick_get_rsvp_status($eid, $uid) {
  return brick_get_rsvp_id($eid, $uid) > 0;
}

function brick_get_rsvp_id($eid, $uid) {
  $r = brick_get_rsvp_query($eid, $uid);
  if ($r->rowCount() < 1) {
    return 0;
  }
  $arr = $r->fetchCol();
  if (count($arr) > 0) {
    return $arr[0];
  }
  return 0;
}

function brick_get_rsvp_query($eid, $uid) {
  $q = "SELECT field_data_field_rsvp_event.entity_id FROM field_data_field_rsvp_person
        LEFT JOIN field_data_field_rsvp_event ON field_data_field_rsvp_person.entity_id
          = field_data_field_rsvp_event.entity_id
        INNER JOIN node on node.nid = field_data_field_rsvp_event.entity_id
        WHERE field_data_field_rsvp_person.field_rsvp_person_uid = " . $uid . "
        AND field_data_field_rsvp_event.field_rsvp_event_nid = " . $eid . "
        AND node.status = 1";
  return (db_query($q));
}

function brick_is_full_user($user) {
  // uid = 0 is anonymous user
  return !empty($user) && $user->uid != 0 && !in_array('guest_user', array_values($user->roles));
}

function brick_rsvp_form($form, $form_state, $nid) {
  global $user;

  $account = user_load($user->uid);
  $name = brick_get_user_name($account);
  $email = $user && property_exists($user, "mail") ? $user->mail : "";

  $submittedEmail = NULL;
  $checkForPassword = FALSE;

  if (array_key_exists('values', $form_state)) {
    $submittedEmail = $form_state['values']['email'];

    if ($submittedEmail) {
      $currentUser = user_load_by_mail($submittedEmail);

      $checkForPassword = check_if_user_needs_login($currentUser);
    }
  }

  $form['rsvp'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => 'rsvpFormWrap'
    )
  );

  $form['rsvp']['email'] = array(
    '#type' => "textfield",
    '#title' => t('Your Email (so we can contact you):'),
    '#size' => 40,
    '#required' => FALSE,
    '#default_value' => $email
  );


  if ($checkForPassword) {
    $form['rsvp']['password'] = array(
      '#type' => "password",
      '#title' => t('Your Password:'),
      '#size' => 40,
      '#required' => FALSE
    );

    $form['rsvp']['name'] = array(
      '#type' => "hidden",
      '#required' => FALSE,
      '#value' => ''
    );
  }
  else {
    $form['rsvp']['name'] = array(
      '#type' => "textfield",
      '#title' => t('Your Name (so we know what to call you!):'),
      '#size' => 40,
      '#required' => FALSE,
      '#default_value' => $name
    );
  }

  // you can't edit your email / name if you're a full user
  if ($user && brick_is_full_user($user)) {
    $form['rsvp']['email']['#disabled'] = TRUE;

    if (array_key_exists('name', $form['rsvp'])) {
      $form['rsvp']['name']['#disabled'] = TRUE;
    }
  }

  $form['rsvp']['public'] = array(
    '#type' => "checkbox",
    '#title' => t('Allow my attendance to be public'),
    '#size' => 40,
    '#required' => FALSE,
    '#default_value' => TRUE
  );

  $rsvpAgree = "By submitting my RSVP, I acknowledge that I am over 21 years of age.
        I have read, and agree to <a href='/waiver' 
        target='_blank'>​One Brick's Waiver, Assumption of Risk and Release.​</a>​";

  $form['rsvp']['rsvpAgree'] = array(
    '#markup' => $rsvpAgree
  );

  $form['rsvp']['message'] = array(
    '#type' => "textarea",
    '#title' => t('A message for the event managers (optional):'),
    '#rows' => 2,
    '#required' => FALSE
  );

  $form['rsvp']['nid'] = array(
    '#type' => 'hidden',
    '#value' => $nid,
    '#default_value' => $nid
  );

  $form['rsvp']['buttonArea']['submitRSVP'] = array(
    '#type' => "button",
    '#value' => t('RSVP'),
    '#ajax' => array(
      'wrapper' => 'rsvpFormWrap',
      'callback' => 'brick_rsvp_ajax',
      'method' => 'html'
    )
  );


  return $form;
}

// returns TRUE if the passed in user needs to login to the system in order to RSVP
function check_if_user_needs_login($checkUser) {
  $needsLogin = FALSE;
  global $user;

  // if this user is a full user, and it's not the current user, then we can't allow to use it
  if ($checkUser) {
    $needsLogin = TRUE;

    if ($user) {
      if ($checkUser->uid == $user->uid) {
        $needsLogin = FALSE;
      }
    }

    if (!brick_is_full_user($checkUser)) {
      $needsLogin = FALSE;
    }
  }

  return $needsLogin;
}

function brick_rsvp_ajax($form, $form_state) {
  global $user;

  $eid = $form_state['values']['nid'];
  $mail = $form_state['values']['email'];
  $name = $form_state['values']['name'];
  $note = $form_state['values']['message'];
  $public = $form_state['values']['public'];
  $emailUser = NULL;

  if (brick_event_full($eid)) {
    // Someone else grabbed the last RSVP slot between the time the user
    // loaded this page and the time they hit the RSVP button.
    // -- You snooze, you loose!
    $commands = brick_build_refresh_page_command();
    return $commands;
  }

  if (form_get_errors()) {
    return $form['rsvp'];
  }

  // unfortunately I have to do all the validation in the SUBMIT method so that the form will be rebuilt on every submit, even
  // if validation would fail. This is due to the password and name fields dropping in and out

  if (!valid_email_address($mail)) {
    form_set_error('email', "Invalid email addresss");
  }

  if ($mail) {
    // see if a user with this email already exists
    $emailUser = user_load_by_mail($mail);
  }

  // validation for the user here
  if ($emailUser) {
    if (brick_get_rsvp_status($eid, $emailUser->uid)) {
      form_set_error('email', "You have already RSVPd for this event");
    }
    else {
      if (!check_user_verified($emailUser)) {
        form_set_error('email', "You'll need to verify your email before you can continue to RSVP");
      }
      else {
        if (check_if_user_needs_login($emailUser)) {
          if ($form_state['values']['password']) {
            require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
            if (!user_check_password($form_state['values']['password'], $emailUser)) {
              form_set_error('password', "Invalid password");
            }
          }
          else {
            form_set_error('password', "<b>Looks like you have an account with us!</b> Please provide your password to continue RSVPing");
          }
        }
      }
    }
  }
  else {
    if (!$name) {
      form_set_error('name', 'Please provide a name');
    }
  }

  if (form_get_errors()) {
    return $form['rsvp'];
  }

  // end validation - begin actual RSVP process
  $loginUser = $user;

  // if a user isnt logged in, we make sure to not use the anonymous user
  if (!user_is_logged_in()) {
    $loginUser = NULL;
  }

  // you get a brand new user if you changed emails on us...
  if ($loginUser && $loginUser->mail != $mail) {
    $loginUser = NULL;
  }

  if ($emailUser) {
    $loginUser = $emailUser;
  }

  if (!$loginUser) {
    // we create a temporary user in guest user mode, and login
    $loginUser = brick_create_guest_account($mail, $name);
  }

  // switch the logged in user if that changed
  if ($user->uid != $loginUser->uid) {
    $user = $loginUser;
    user_login_finalize();
  }

  brick_add_rsvp($eid, $user->uid, $note, $public);
  send_rsvp_emails($eid, $user->signature, $user->mail, $note);

  //trigger_action('brick_rsvp', $event_node);

  drupal_set_message(t('You have successfully RSVP\'d for this event. We\'ll see you there!'), 'status');

  $commands = brick_build_refresh_page_command();

  return $commands;
}


/*
 * Send emails to the managers notifying them of an rsvp for this event
 */
function send_rsvp_emails($eid, $rsvpname, $rsvpemail, $note, $unrsvp = FALSE) {
  $managers = brick_get_management_list($eid); // This should only go to the EM (not the EC)

  $email_list = array();
  foreach ($managers as $mgrId) {
    $mgr = user_load($mgrId);
    $email_list[] = $mgr->mail;
  }

  // If there are no managers send an email to the events team instead.
  if (!$email_list) {
    $email_list[] = brick_events_email();
  }

  foreach ($email_list as $email) {
    $params['eid'] = $eid;
    $params['note'] = $note;
    $params['rsvpname'] = $rsvpname;
    $params['rsvpemail'] = $rsvpemail;

    if ($unrsvp) {
      drupal_mail('brick', 'unrsvp', $email, language_default(), $params, $rsvpemail);
    }
    else {
      drupal_mail('brick', 'rsvp', $email, language_default(), $params, $rsvpemail);

    }
  }
}

function brick_mail($key, &$message, $params) {
  switch ($key) {
    case 'rsvp':
      $eid = $params['eid'];
      $event_node = node_load($eid);
      $variables['@title'] = $event_node->title;
      $variables['@date'] = brick_event_from_to($eid);
      $variables['@rsvpname'] = $params['rsvpname'];
      $variables['@note'] = $params['note'];

      $message['subject'] = strtr("Event RSVP: @title", $variables);
      $message['body'][] = strtr("@rsvpname has RSVPed to the following event:\n @title\n@date.\n---\nNote from volunteer:\n@note", $variables);
      break;
    case 'unrsvp':
      $eid = $params['eid'];
      $event_node = node_load($eid);
      $variables['@title'] = $event_node->title;
      $variables['@date'] = brick_event_from_to($eid);
      $variables['@rsvpname'] = $params['rsvpname'];
      $variables['@note'] = $params['note'];

      $message['subject'] = strtr("Event unRSVP: @title", $variables);
      $message['body'][] = strtr("@rsvpname has unRSVPed from the following event:\n @title\n@date", $variables);
      break;
    case 'assign':
    case 'unassign':
      $message['subject'] = $params['subject'];
      $message['body'][] = $params['body'];
      break;
  }
}

// Return the number of people who have RSVPed but are not PUBLIC people
function brick_private_rsvps($eid) {
  $q = "SELECT count(field_data_field_rsvp_person.field_rsvp_person_uid) FROM node

	LEFT JOIN field_data_field_rsvp_person ON node.nid = field_data_field_rsvp_person.entity_id AND (field_data_field_rsvp_person.entity_type = 'node' AND field_data_field_rsvp_person.deleted = '0' AND field_data_field_rsvp_person.delta = '0')

	LEFT JOIN  field_data_field_rsvp_event ON node.nid = field_data_field_rsvp_event.entity_id AND (field_data_field_rsvp_event.entity_type = 'node' AND field_data_field_rsvp_event.deleted = '0' AND field_data_field_rsvp_event.delta = '0')

	LEFT JOIN  field_data_field_public ON field_data_field_rsvp_person.field_rsvp_person_uid = field_data_field_public.entity_id AND (field_data_field_public.entity_type = 'user' AND field_data_field_public.deleted = '0')

	WHERE (( (field_data_field_rsvp_event.field_rsvp_event_nid = '" . $eid . "' ) )AND(( (node.status = '1') AND (node.type IN  ('rsvp')) AND (field_data_field_public.field_public_value IN  ('0')) )))

	LIMIT 1000 OFFSET 0";

  $r = db_query($q);
  return $r->fetchField();
}

?>
