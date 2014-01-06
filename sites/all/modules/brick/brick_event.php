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

?>
