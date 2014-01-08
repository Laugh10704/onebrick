<?php
require("include.php");
require("open_v3.php");

$q = "delete FROM field_revision_field_user_profile where 1";
db_query($q) or die(db_error());

$q = "delete FROM field_data_field_user_profile WHERE 1";
db_query($q) or die(db_error());


$dir="/tmp/staff_profiles"; chdir($dir);

$files = glob("*.profile");
$old_userid = 0;
foreach ($files as $fname) {
	$userid = basename($fname, ".profile");

	$fp = fopen($fname, "r");
	$str = fread($fp, filesize($fname));
	$profile = addslashes($str);
	fclose($fp);

	$q = " INSERT INTO field_revision_field_user_profile
		SET
			ENTITY_TYPE = 'user',
			bundle = 'user',
			deleted = 0,
			entity_id = ". $userid .",
			revision_id = ". $userid .",
			language = 'und',
			delta = 0,
			field_user_profile_value = '". $profile ."',
			field_user_profile_format = 'filtered_html' ";


	db_query($q) or die(db_error());

	$q = " INSERT INTO field_data_field_user_profile
		SET
			ENTITY_TYPE = 'user',
			bundle = 'user',
			deleted = 0,
			entity_id = ". $userid .",
			revision_id = ". $userid .",
			language = 'und',
			delta = 0,
			field_user_profile_value = '". $profile ."',
			field_user_profile_format = 'filtered_html' ";


	db_query($q) or die(db_error());
}
