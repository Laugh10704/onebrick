<?php
require("include.php");
require("open_v3.php");

/* Load the User */
$file = 'data/org_users.csv';
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE users
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET 
			uid = @uid+$orgcontact_offset,
			name = @name,
			mail = @mail,
			init = @mail,
			timezone = @timezone,
			status = 1,
			signature_format = 'filtered_html',
			created = @modified,
			language = 'und'
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
	foreach ($two_types as $t) {

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_chapter
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_user_chapter_nid=@chapter + $chapid_offset 
	";
	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_phone
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_user_phone_value=@phone
	";
	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_phone
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_user_phone_value=@phone
	";
	db_query($q) or die(db_error());

	// create people as unsubscribed
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_subscribed
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_user_subscribed_value=0
	";
	//db_query($q) or die(db_error()); 
}

// Create the Org Contact Records

$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			nid = @uid+$orgcontact_offset,
			vid = @uid+$orgcontact_offset,
			uid = 1,
			status=1,
			created=@created,
			changed=@modified,
			comment=0,
			title = @jobtitle,
			type = 'organization_contact',
			language = 'und'
";
db_query($q) or die(db_error());


$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			nid = @uid+$orgcontact_offset,
			gid = 0,
			realm = 'all',
			grant_view = 1,
			grant_update = 0,
			grant_delete = 0,
";
db_query($q) or die(db_error());

$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET
			nid = @uid+$orgcontact_offset,
			vid = @uid+$orgcontact_offset,
			uid = 1,
			title = @jobtitle,
			timestamp=@created,
			status=1,
			comment=0
";
db_query($q) or die(db_error());


$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET 
			nid = @uid+$orgcontact_offset,
			cid = 0,
			last_comment_timestamp=@created,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
	foreach ($two_types as $t) {
				$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_org_contact_organization
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET 
			entity_type = 'node',
			bundle = 'organization_contact',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_org_contact_organization_nid=@orgid+$orgid_offset
	";

	db_query($q) or die(db_error());

				$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_org_contact_person
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET 
			entity_type = 'node',
			bundle = 'organization_contact',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_org_contact_person_uid=@uid+$orgcontact_offset
	";

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_fullname
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @jobtitle, @mail, @phone, @orgid, @timezone, @chapter, @created, @modified)
		SET 
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid+$orgcontact_offset,
			revision_id = @uid+$orgcontact_offset,
			language = 'und',
			field_user_fullname_value=@name
	";
	db_query($q) or die(db_error());

	db_query($q) or die(db_error());
}

