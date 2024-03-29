<?php
/*
 *
 */

function brick_page_alter(&$page) {
	error_log("brick_page_alter() called");
  //brick_merge_recursive($page);
}

// Search through the entire page structure and do the merge on every string.
function brick_merge_recursive(&$val) {
  if (is_array($val)) {
    foreach ($val as &$elem) {
      brick_merge_recursive($elem);
    }
  }
  elseif (is_string($val)) {
    $val = brick_merge_variables($val);
  }
}

/*
 *
 * brick_merge_variables(string s) scans a string and replaces variables 
 *
 * The supported variables are:
 *
 * stats:
 * email:
 * 	from_fname	
 * 	from_sname
 * 	to_fname
 * 	to_sname
 *
 * event:
 * 	name	- name of the event 
 * 	date - date of the event
 * 	start- event start time
 * 	end	- event end time
 * 	loc	-	name of the event location
 * 	org	- the name of the organization associated with the event
 * 	contacts	-	a list of the organizational contacts for the event
 * 	url	-	URL of the event page
 *
 * user:
 * 	fname	-	first name of the current user
 * 	sname	-	first name of the current user
 *
 * rsvp_note	- note left by the user when they rsvp'd to the event
 * password		- new password, set by the system
 *
 */

$brick_mm_fields = array(); 

function brick_merge_variables_xx($s) {
}

function brick_merge_variables($s) {
	$c = "#brick_expand"; //This is the stub we will look for in the file

	$ob_mm_fields = array( // This is debug code, it should be removed once things are being set correctly
 	'email' => array (
 			'from_fname'=> "From FNAME", 'from_sname'=> "TO SNAME",
			'to_fname' 	=> "From FNAME", 'to_sname' 	=> "TO SNAME"),
	
 	'event' => array (
 			'name' 			=> "Putting stuff in boxes",
 			'date' 			=> "April 1st, 2012",
 			'start' 		=> "8am",
 			'end' 			=> "10am",
 			'loc'				=> "San Jose",
 			'org'				=> "Boxs of stuff Charities",
 			'contacts' 	=> "Mr and Mrs Stuff",
 			'url'				=> "http://url.com"),
	
 	'user' => array (
 			'fname' => "Clive", 'sname' 	=> "Charlwood"),
	
 	'rsvp_note' 	=> "Dear EM, this is my RSVP note.",
 	'password' 		=> "XYXYXY_new_password_ABABAB"
	
	);

$tr_email = array (
	$c."(email:from_fname) => $brick_mm_fields['email']['from_fname'],
	$c."(email:from_sname) => $brick_mm_fields['email']['from_sname'],
	$c."(email:to_fname) => $brick_mm_fields['email']['to_fname'],
	$c."(email:to_sname) => $brick_mm_fields['email']['to_sname']);

$tr_event = array (
	$c."(event:name) =>  		$brick_mm_fields['event']['name'],
	$c."(event:date) => 			$brick_mm_fields['event']['date'],
	$c."(event:start) => 		$brick_mm_fields['event']['start'],
	$c."(event:end) =>  	  	$brick_mm_fields['event']['end'],
	$c."(event:loc) =>  	  	$brick_mm_fields['event']['loc'],
	$c."(event:org) =>  	  	$brick_mm_fields['event']['org'],
	$c."(event:contacts) => 	$brick_mm_fields['event']['contacts'],
	$c."(event:url) =>     	$brick_mm_fields['event']['url']);

$tr_name = array (
	$c."(user:fname) =>  	$brick_mm_fields['user']['fname'],
	$c."(user:sname) => 	$brick_mm_fields['user']['sname']);

$s = strtr($s, $tr_email);
$s = strtr($s, $tr_event);
$s = strtr($s, $tr_name);
$s = strtr($s, array ($c."(rsvp_note) => $brick_mm_fields['rsvp_note']));
$s = strtr($s, array ($c."(password) =>  $brick_mm_fields['password']));

return($s);
}
