<?php
require("include.php");
require("open_v3.php");
dmsg("userid_offset is $userid_offset");

/* Load the User */
$file = 'data/users.csv';
$team = 'data/team.csv';
$subs = 'data/users_subscription.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE users
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@uid, @fname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET 
			uid = @uid,
			name = @mail,
			mail = @mail,
			init = @mail,
			timezone = @timezone,
			status = 1,
			signature_format = 'filtered_html',
			created = @created,
			language = 'und'
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
	foreach ($two_types as $t) {

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_chapter
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @fname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_chapter_nid=@chapter+$chapid_offset 
	";
	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_phone
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @fullname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_phone_value=@phone
	";
	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_fullname
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @fullname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_fullname_value=@fullname
	";
	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_copied_over
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @fullname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_copied_over_value=1
	";
	db_query($q) or die(db_error());

	//All users are private volunteers until THEY set they visibility as public
	//$q = "
		//LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_public_rsvp
		//FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			//(@uid, @fullname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		//SET
			//entity_type = 'user',
			//bundle = 'user',
			//entity_id = @uid,
			//revision_id = @uid,
			//language = 'und',
			//field_user_public_rsvp_value=0
	//";
	//db_query($q) or die(db_error());

	// By default we create people as unsubscribed, then update later from the subscribed file
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_subscribed
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @fullname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_subscribed_value=0
	";
	db_query($q) or die(db_error());
}

// Add subscription info 
$two_types = array('data', 'revision');
foreach ($two_types as $t) {

	$q = "
		LOAD DATA LOCAL INFILE '".$subs."' REPLACE INTO TABLE field_".$t."_field_user_subscribed
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_subscribed_value=1
	";
	db_query($q) or die(db_error());
}

/* change the login and email for all staff to their one brick email address */

exec("grep 'onebrick.org%\$' < $file > $team");

$q = "
	LOAD DATA LOCAL INFILE '".$team."' REPLACE INTO TABLE users
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@uid, @fname, @sname, @fullname, @mail, @phone, @timezone, @chapter, @created, @1bmail)
		SET 
			uid = @uid,
			status = 1,
			name = @1bmail,
			mail = @1bmail,
			init = @1bmail
			timezone = @timezone,
			signature_format = 'filtered_html',
			created = @created,
			language = 'und'
";
db_query($q) or die(db_error());
