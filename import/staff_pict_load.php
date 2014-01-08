<?php
require("include.php");
require("open_v3.php");

$file ="/tmp/staff.csv"; 

$now = time();

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE users_roles
	FIELDS TERMINATED BY ',' ESCAPED BY '*' 
			(@uid, @rid)
		SET 
			uid = @uid,
			rid = @rid
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
foreach ($two_types as $t) {

	db_query($q) or die(db_error());
	//All users are private volunteers until THEY set they visibility as public
	$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_public_rsvp
	FIELDS TERMINATED BY ',' ESCAPED BY '*' 
			(@uid, @rid)
		SET 
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_public_rsvp_value=1
	";
	db_query($q) or die(db_error());
}

