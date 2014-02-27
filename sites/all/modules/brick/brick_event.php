<?php

function brick_event_is_cancelled($node) {
  return $node->field_event_status['und'][0]['value'] == 'Cancelled';
}


function brick_event_status($nid) {
  $node = node_load($nid);
  return $node->field_event_status['und'][0]['value'];
}

function brick_event_status_class($nid) {
	$str = '';
	$status = brick_get_event_assigned_status($nid);
	if ($status == 'Cancelled') {
		$str = 'event-cancelled';
	} else if (brick_can_optin()) {
		if ($status == 'Unassigned')
			$str = 'event-opted-in';
		else if ($status == 'Manager' || $status == 'Coordinator') {
			$str = 'event-assigned';
		}
	}

	return $str;
}

function brick_upload_photo_form($form, &$form_state, $nid = null) {
  $form['#parents']=array();

  if (!$nid) {
    $nid = intval($form_state['values']['field_event_photos']);
  }

  $node = node_load($nid);
  $field = field_info_field("field_event_photos");
  $instance = field_info_instance('node', "field_event_photos", "event");
  $items = array_key_exists('und', $node->{"field_event_photos"}) ? $node->{"field_event_photos"}["und"] : array();
  $result = field_default_form('node', $node, $field, $instance, LANGUAGE_NONE, $items, $form, $form_state);

  $form += (array)$result;

  $form['submitarea'] = array(
    '#type' => "container",
  );

  $form['submitarea']['submit'] = array(
    '#type' => "submit",
    '#value' => t('Save Changes to Photos')
  );

  $form['nid'] = array(
    '#type' => "hidden",
    '#value' => $nid
  );

  return $form;
}

function brick_upload_photo_form_submit($form, &$form_state) {
  $nid = intval($form_state['values']['nid']);

  // get node object
  $node = node_load($nid);

  $node->{'field_event_photos'} = $form_state['values']['field_event_photos'];

  // have to convert the width and height to nulls instead of blanks to prevent save error
  foreach ($node->{'field_event_photos'}['und'] as &$photo) {
    if (array_key_exists('width', $photo) && !$photo['width']) {
      $photo['width'] = null;
      $photo['height'] = null;
    }
  }
  node_save($node);

  drupal_set_message('Photos saved!');
}

?>
