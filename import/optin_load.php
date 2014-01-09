<?php
echo 1;
require("include.php");
echo 2;
require("open_v3.php");
echo 3;
//$limit=1;

$now = time();

$file = 'data/optin.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@optinid, @userid, @eventid, @preference, @role)
	SET
			nid = @optinid+$optin_offset,
			vid = @optinid+$optin_offset,
			uid = 1,
			status=1,
			created=@created,
			changed=@modified,
			comment=0,
			title = @name,
			type = 'opt_in',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@optinid, @userid, @eventid, @preference, @role)
		SET 
			nid = @optinid+$optin_offset,
			vid = @optinid+$optin_offset,
			uid = 1,
			title = @name,
			timestamp=@created,
			status=1,
			comment=0
";

db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@optinid, @userid, @eventid, @preference, @role)
		SET 
			nid = @optinid+$optin_offset,
			gid = 0,
			realm = 'all',
			grant_view=1,
			grant_update=0,
			grant_delete=0
";

db_query($q) or die(db_error());
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@optinid, @userid, @eventid, @preference, @role)
		SET 
			nid = @optinid+$optin_offset,
			cid = 0,
			last_comment_timestamp=@created,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());


$two_types = array('data', 'revision');
foreach ($two_types as $t) {
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_optin_event
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
(@optinid, @userid, @eventid, @preference, @role) 
	SET 
				entity_type='node',
				bundle='opt_in',
				entity_id=@optinid+$optin_offset,
				revision_id=@optinid+$optin_offset,
				language='und',
				field_optin_event_nid=@eventid+$eventid_offset
	";
	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_optin_person
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
(@optinid, @userid, @eventid, @preference, @role) 
	SET 
				entity_type='node',
				bundle='opt_in',
				entity_id=@optinid+$optin_offset,
				revision_id=@optinid+$optin_offset,
				language='und',
				field_optin_person_uid=@userid+$userid_offset
	";
	db_query($q) or die(db_error());
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_optin_preference
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
(@optinid, @userid, @eventid, @preference, @role) 
	SET 
				entity_type='node',
				bundle='opt_in',
				entity_id=@optinid+$optin_offset,
				revision_id=@optinid+$optin_offset,
				language='und',
				field_optin_preference_value=@preference
	";
	db_query($q) or die(db_error());
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_optin_role
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
(@optinid, @userid, @eventid, @preference, @role) 
	SET 
				entity_type='node',
				bundle='opt_in',
				entity_id=@optinid+$optin_offset,
				revision_id=@optinid+$optin_offset,
				language='und',
				field_optin_role_value=@role
	";
	db_query($q) or die(db_error());

echo 3;
} // End of foreach $two_types
