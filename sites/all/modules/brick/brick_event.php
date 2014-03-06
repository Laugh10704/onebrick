<?php

function brick_event_status($eid) {
  $event_node = node_load($eid);
  return $event_node->field_event_status['und'][0]['value'];
}

function brick_event_is_cancelled($eid) {
  return brick_event_status($eid) == 'Cancelled';
}

function brick_event_status_class($eid) {
  $str = '';
  $status = brick_get_event_assigned_status($eid);
  if ($status == 'Cancelled') {
    $str = 'event-cancelled';
  }
  else {
    if (brick_can_optin()) {
      if ($status == 'Unassigned') {
        $str = 'event-opted-in';
      }
      else {
        if ($status == 'Manager' || $status == 'Coordinator') {
          $str = 'event-assigned';
        }
      }
    }
  }

  return $str;
}

function brick_upload_photo_form($form, &$form_state, $eid = NULL) {
  $form['#parents'] = array();

  if (!$eid) {
    $eid = intval($form_state['values']['field_event_photos']);
  }

  $event_node = node_load($eid);
  $field = field_info_field("field_event_photos");
  $instance = field_info_instance('node', "field_event_photos", "event");
  $items = array_key_exists('und', $event_node->{"field_event_photos"}) ? $event_node->{"field_event_photos"}["und"] : array();
  $result = field_default_form('node', $event_node, $field, $instance, LANGUAGE_NONE, $items, $form, $form_state);

  $form += (array) $result;

  $form['submitarea'] = array(
    '#type' => "container",
  );

  $form['submitarea']['submit'] = array(
    '#type' => "submit",
    '#value' => t('Save Changes to Photos')
  );

  $form['nid'] = array(
    '#type' => "hidden",
    '#value' => $eid
  );

  return $form;
}

function brick_upload_photo_form_submit($form, &$form_state) {
  $eid = intval($form_state['values']['nid']);
  $event_node = node_load($eid);

  $event_node->{'field_event_photos'} = $form_state['values']['field_event_photos'];

  // have to convert the width and height to nulls instead of blanks to prevent save error
  foreach ($event_node->{'field_event_photos'}['und'] as &$photo) {
    if (array_key_exists('width', $photo) && !$photo['width']) {
      $photo['width'] = NULL;
      $photo['height'] = NULL;
    }
  }
  node_save($event_node);

  drupal_set_message('Photos saved!');
}

?>
