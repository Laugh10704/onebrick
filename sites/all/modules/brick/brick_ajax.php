<?php

function brick_ajax_command_raw_html($selector, $html) {
	return array(
		'command' => 'brick_raw_html',
		'selector' => $selector,
		'html' => $html,
	);
}

function brick_ajax_command_close_colorbox() {
	return array(
		'command' => 'brick_close_colorbox',
	);
}

?>
