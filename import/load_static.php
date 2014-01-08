<?php
require("include.php");
require("open_v3.php");
dmsg("lid_offset is $lid_offset");

$file ="data/static.csv"; 

// Bugin old data means created date is not set correctly so we just use modified.

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@sid,  @title)
		SET 
			nid = @sid+$static_offset,
			vid = @sid+$static_offset,
			uid = 1,
			status=1,
			created=@modified,
			changed=@modified,
			comment=0,
			title = @title,
			type = 'page',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@sid,  @title)
		SET 
			nid = @sid+$static_offset,
			vid = @sid+$static_offset,
			uid = 1,
			title = @title,
			timestamp=@modified,
			status=1,
			comment=0
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@sid,  @title)
		SET 
			nid = @sid+$static_offset,
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
			(@sid,  @title)
		SET 
			nid = @sid+$static_offset,
			cid = 0,
			last_comment_timestamp=@modified,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());


exit(0); // DON'T overwrite the body nodes unless you add in the html_body content!!
//$two_types = array('data', 'revision');
//foreach ($two_types as $t) {
//	$q = "
//		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_body
//		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
//			(@sid,  @title)
//			SET 
//				entity_type='node',
//				bundle='page',
//				entity_id= @sid+$static_offset,
//				revision_id= @sid+$static_offset,
//				language='und'
//	";
//	db_query($q) or die(db_error());
//
//} // End of foreach $two_types
//
//
