<?php

function brick_can_optin() {
	return brick_manager_access();
}

function brick_optin_string($eid, $uid = null) {
	global $user;

	if ($uid == null)
		$uid = $user->uid;

	$res = db_query('SELECT field_optin_role_value, field_optin_preference_value
		FROM field_data_field_optin_event event
			INNER JOIN field_data_field_optin_person person ON (event.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_preference preference ON (preference.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_role role ON (role.entity_id = preference.entity_id)
		WHERE field_optin_person_uid = :uid AND field_optin_event_nid = :eid',
		array(':uid' => $uid, ':eid' => $eid));

	$prefs = array();

	foreach ($res as $rec) {
		$star = '';
		if ($rec->field_optin_preference_value == 'Preferred')
			$star = '*';

		if ($rec->field_optin_role_value == 'Manager')
			array_push($prefs, "M$star");
		else
			array_push($prefs, "C$star");
	}

	$str = join(', ', $prefs);

	if ($str)
		$str = "($str)";

	return $str;
}

function brick_optin_string_long($eid, $uid = null) {
	$str = brick_optin_string($eid, $uid);
	$str = preg_replace('/[()]/', '', $str);
	$str = preg_replace('/M/', 'Manager: Available', $str);
	$str = preg_replace('/C/', 'Coordinator: Available', $str);
	$str = preg_replace('/Available\*/', 'Preferred', $str);
	return $str;
}

function brick_optin_get_availability($date, $uid = null) {
	global $user;

	if ($uid == null)
		$uid = $user->uid;

	$res = db_query('SELECT field_availability_count_value
		FROM field_data_field_availability_month month
			INNER JOIN field_data_field_availability_person person ON (month.entity_id = person.entity_id)
			INNER JOIN field_data_field_availability_count count ON (person.entity_id = count.entity_id)
		WHERE field_availability_person_uid = :uid AND
			DATE_FORMAT(field_availability_month_value, \'%Y-%m\') =
			DATE_FORMAT(FROM_UNIXTIME(:date), \'%Y-%m\')',
		array(':uid' => $uid, ':date' => $date));

	if ($res->rowCount() < 0)
		return -1;

	return $res->fetchColumn();
}

// Convert to a timestamp in the user's timezone
function brick_optin_fix_date($str, $from_tz, $to_tz)
{
	$obj = new DateObject($str, $from_tz);
	if ($to_tz)
		date_timezone_set($obj, timezone_open($to_tz));
	return $obj->getTimestamp();
}

function brick_optin_page($nid) {
	echo <<<EOF
<html>
<head>
	<link rel='stylesheet' type='text/css' href='/sites/all/themes/onebrick_tarski/css/optin.css' />
</head>
<body>
EOF;
	echo drupal_render(drupal_get_form('brick_optin_form', $nid)),
		'</body></html>';
}

function brick_optin_form($form, $form_state, $nid) {
	global $user;

	$manager_default = 0;
	$coordinator_default = 0;
	$manager_locked = 0;
	$coordinator_locked = 0;
	$availability_default = 0;

	// Look up existing opt-in preferences
	$res = db_query('SELECT field_optin_role_value, field_optin_preference_value, field_optin_selected_value
		FROM field_data_field_optin_event event
			INNER JOIN field_data_field_optin_person person ON (event.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_preference preference ON (preference.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_role role ON (role.entity_id = preference.entity_id)
			INNER JOIN field_data_field_optin_selected selected ON (selected.entity_id = preference.entity_id)
		WHERE field_optin_person_uid = :uid AND field_optin_event_nid = :eid',
		array(':uid' => $user->uid, ':eid' => $nid));

	foreach ($res as $rec) {
		$val = brick_optin_pref_string_to_val($rec->field_optin_preference_value);
		$locked = ($rec->field_optin_selected_value == 'Yes');

		if ($rec->field_optin_role_value == 'Manager') {
			$manager_default = $val;
			$manager_locked = $locked;
		} else {
			$coordinator_default = $val;
			$coordinator_locked = $locked;
		}
	}

	// Look up event details
	$event = node_load($nid);
	// print_r($event);
	$title = $event->title;
	$title_fmt = "<a href='/node/$nid' target='_blank'>$title</a>";
	$body = $event->body['und'][0]['value'];
	$summary = $event->body['und'][0]['safe_summary'];
	$from_tz = $event->field_event_date['und'][0]['timezone_db'];
	$to_tz = $event->field_event_date['und'][0]['timezone'];
	$start = brick_optin_fix_date($event->field_event_date['und'][0]['value'], $from_tz, $to_tz);
	$end = brick_optin_fix_date($event->field_event_date['und'][0]['value2'], $from_tz, $to_tz);

	$site = node_load($event->field_event_site['und'][0]['nid']);
	$address = location_address2singleline($site->location);
	$address_fmt = "$address <a title='Google Map' href='http://maps.google.com/maps?q=$address' target='_blank'><img width='20' src='/sites/default/files/images/google_maps.png' /></a>";

	$date_fmt = date('Y-m-d', $start);
	$start_fmt = date('g:ia', $start);
	$end_fmt = date('g:ia', $end);

	// Look up existing max availability
	$date = brick_optin_get_event_date($nid);
	$availability_val = brick_optin_get_availability($date, $user->uid);
	if ($availability_val >= 0)
		$availability_default = $availability_val;

	$options = array(
		0 => t('Opt-Out'),
		1 => t('Available'),
		2 => t('Preferred'),
	);

	$form['optin'] = array(
		'#type' => 'container',
		'#id' => 'brick-opt-in-form',
		'#attributes' => array(
			'id' => 'optinFormWrap'
		)
	);

	$form['optin']['optin_title'] = array(
		'#type' => 'container',
	);

	$form['optin']['optin_title']['value'] = array(
		'#markup' => $title_fmt,
	);

	$form['optin']['optin_date'] = array(
		'#type' => 'container',
	);

	$form['optin']['optin_date']['value'] = array(
		'#markup' => "$date_fmt, $start_fmt - $end_fmt",
	);

	$form['optin']['optin_address'] = array(
		'#type' => 'container',
	);

	$form['optin']['optin_address']['value'] = array(
		'#markup' => $address_fmt,
	);

	$form['optin']['optin_summary'] = array(
		'#type' => 'container',
	);

	$form['optin']['optin_summary']['value'] = array(
		'#markup' => $summary,
	);

	$form['optin']['nid'] = array(
		'#type' => 'hidden',
		'#value' => $nid,
		'#default_value' => $nid,
	);

	if (user_has_role('Manager')) {
		$form['optin']['optin_manager'] = array(
			'#type' => 'radios',
			'#title' => t('Manager'),
			'#options' => $options,
			'#default_value' => $manager_default,
			'#required' => FALSE
		);
	}

	$form['optin']['optin_coordinator'] = array(
		'#type' => 'radios',
		'#title' => t('Coordinator'),
		'#options' => $options,
		'#default_value' => $coordinator_default,
		'#required' => FALSE
	);

	if ($manager_locked) {
		$form['optin']['optin_manager']['#disabled'] = TRUE;
		$form['optin']['optin_manager']['#title'] = t('Manager - <b>Assigned, cannot edit</b>');
	}

	if ($coordinator_locked) {
		$form['optin']['optin_coordinator']['#disabled'] = TRUE;
		$form['optin']['optin_coordinator']['#title'] = t('Coordinator - <b>Assigned, cannot edit</b>');
	}

	$availability_options = array(
		0 => t('No events this month'),
		1 => '1 ' . t('EVEnt'),
	);

	for ($i = 2; $i <= 10; $i++) {
		$availability_options[$i] = "$i " . t('events');
	}

	$form['optin']['optin_availability'] = array(
		'#type' => 'select',
		'#title' => t('Max Availability for ') . date('F', $date),
		'#options' => $availability_options,
		'#default_value' => $availability_default,
	);

	$form['optin']['buttonArea']['submit'] = array(
		'#type' => 'button',
		'#value' => t('Save'),
		'#ajax' => array(
			'callback' => 'brick_optin_ajax',
			'wrapper' => 'optinFormWrap',
		),
	);


	return $form;
}

// TODO: Adjust for user timezone
function brick_optin_get_event_date($eid) {
	$res = db_query('SELECT UNIX_TIMESTAMP(field_event_date_value)
		FROM field_data_field_event_date
		WHERE entity_id = :eid', array(':eid' => $eid));

	return $res->fetchColumn(0);
}

function brick_optin_pref_val_to_string($val) {
	return ($val == 1) ? 'Available' : 'Preferred';
}

function brick_optin_pref_string_to_val($str) {
	return ($str == 'Available') ? 1 : 2;
}

// TODO: authentication
function brick_optin_ajax($form, $form_state) {
        global $user;

	$uid = $user->uid;
	$eid = $form_state['values']['nid'];
	$manager = $form_state['values']['optin_manager'];
	$coordinator = $form_state['values']['optin_coordinator'];
	$availability = $form_state['values']['optin_availability'];

	$manager_val = brick_optin_pref_val_to_string($manager);
	$coordinator_val = brick_optin_pref_val_to_string($coordinator);

	if ($manager > 0)
		brick_optin_set_preference($uid, $eid, 'Manager', $manager_val);
	else
		brick_optin_remove($uid, $eid, 'Manager');

	if ($coordinator > 0)
		brick_optin_set_preference($uid, $eid, 'Coordinator', $coordinator_val);
	else
		brick_optin_remove($uid, $eid, 'Coordinator');

	$date = brick_optin_get_event_date($eid);
	brick_optin_set_availability($date, $availability, $uid);

	drupal_set_message(t('Your opt-in preferences have been saved.'), 'status');

	$commands = array();

	// Reset the Drupal messages.
	$commands[] = ajax_command_remove('div.messages');
	$commands[] = ajax_command_before('#main-content', theme('status_messages'));

	// Close the colorbox popup.
	$commands[] = brick_ajax_command_close_colorbox();

	// Let's update the coloring.
	$status = brick_event_status_class($eid);
	$commands[] = ajax_command_data('a[href="/node/' . $eid . '"] span[data-event-status]', 'event-status', $status);

	// And the opt-in text.
	$optinText = 'Opt-In ' . brick_optin_string($eid);
	$commands[] = brick_ajax_command_raw_html('div.calendar\\.' . $eid . '\\.field_event_date\\.0\\.0 a[href$="optin"]', $optinText);

	return array('#type' => 'ajax', '#commands' => $commands);
}

function brick_optin_set_preference($uid, $eid, $role, $preference) {
	$res = brick_optin_get_existing($uid, $eid, $role);
	if ($res->rowCount() > 0) {
		$node = node_load($res->fetchColumn());
		$node->field_optin_preference['und'][0]['value'] = $preference;
		node_save($node);
	} else {
		brick_optin_add($uid, $eid, $role, $preference);
	}
}

function brick_optin_remove($uid, $eid, $role) {
	$res = brick_optin_get_existing($uid, $eid, $role);
	if ($res->rowCount() > 0) {
		node_delete($res->fetchCol());
	}
}

function brick_get_calendar_referer_date() {
	$referer = $_SERVER['HTTP_REFERER'];
	if (! $referer) {
		drupal_goto('calendar');
	}

	$date = new DateTime(date('Y-m'));

	// Mine the month out of the URL, if we can
	if (preg_match('/\/calendar\/(.*?)(\/|$)/', $referer, $matches)) {
		$date = new DateTime($matches[1]);
	}

	return $date;
}

function brick_optin_get_existing($uid, $eid, $role) {
	return db_query('SELECT event.entity_id
		FROM field_data_field_optin_event event
			INNER JOIN field_data_field_optin_person person ON (event.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_preference preference ON (preference.entity_id = person.entity_id)
			INNER JOIN field_data_field_optin_role role ON (role.entity_id = preference.entity_id)
		WHERE field_optin_person_uid = :uid AND
			field_optin_event_nid = :eid AND
			field_optin_role_value = :role',
		array(':uid' => $uid, ':eid' => $eid, ':role' => $role));
}

function brick_optin_add($uid, $eid, $role, $preference, $createdWhenAssigned = 'FALSE') {
	$node = new StdClass();
	$node->type = 'opt_in';
	$node->status = 1;
	$node->title = 'Opt In';
	$node->uid = $uid;
	$node->field_optin_person['und'][0]['uid'] = $uid;
	$node->field_optin_event['und'][0]['nid'] = $eid;
	$node->field_optin_role['und'][0]['value'] = $role;
	$node->field_optin_preference['und'][0]['value'] = $preference;
	$node->field_optin_selected['und'][0]['value'] = 'No';
	$node->field_optin_created_when_assign['und'][0]['value'] = $createdWhenAssigned;

	$node = node_submit($node);
	node_save($node);
}

// returns true if the availability was successfully changed, false if no change occurred.
function brick_optin_set_availability($date, $count, $uid = null) {
	global $user;

	if ($uid == null)
		$uid = $user->uid;
	
	// See if an entry already exists
	$res = db_query('SELECT count.entity_id
		FROM field_data_field_availability_count count
			INNER JOIN field_data_field_availability_person person ON (person.entity_id = count.entity_id)
			INNER JOIN field_data_field_availability_month month ON (month.entity_id = person.entity_id)
		WHERE field_availability_person_uid = :uid AND
			DATE_FORMAT(field_availability_month_value, \'%Y-%m\') =
			DATE_FORMAT(FROM_UNIXTIME(:date), \'%Y-%m\')',
		array(':uid' => $uid, ':date' => $date));
	
	// Update or insert
	if ($res->rowCount() > 0) {
		$nid = $res->fetchColumn();
		$node = node_load($nid);
        if ($node->field_availability_count['und'][0]['value'] != $count) {
          $node->field_availability_count['und'][0]['value'] = $count;
          node_save($node);
          return true;
        }
        return false;
	} else {
		$node = new StdClass();
		$node->type = 'availability';
		$node->status = 1;
		$node->title = 'Availability';
		$node->uid = $uid;
		$node->field_availability_person['und'][0]['uid'] = $uid;
		$node->field_availability_month['und'][0]['value'] = date('Y-m-01 00:00:00', $date);
		$node->field_availability_count['und'][0]['value'] = $count;

		$node = node_submit($node);
		node_save($node);
        return true;
	}
}

?>
