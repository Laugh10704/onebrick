<?php
require("include.php");
require("open_v3.php");
$curtime = time();


$file ="/tmp/ephotos.csv"; 

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE file_usage 
	FIELDS TERMINATED BY ',' (@fileid, @eventid, @seq, @filename, @uri, @fsize)
		SET 
			fid = @fileid,
			module = 'file',
			type = 'node',
			id = @eventid+$eventid_offset,
			count = 1
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE file_managed
	FIELDS TERMINATED BY ',' (@fileid, @eventid, @seq, @filename, @uri, @fsize)
		SET 
			fid = @fileid,
			uid = 1,
			filename = @filename,
			uri = @uri,
			filemime = 'image/jpeg',
			filesize = @fsize,
			status = 1,
			timestamp = $curtime;
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
foreach ($two_types as $t) {

	$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_photos
	FIELDS TERMINATED BY ',' (@fileid, @eventid, @seq, @filename, @uri, @fsize)
		SET 
		entity_type = 'node',
		bundle = 'event',
		deleted = 0,
		entity_id = @eventid+$eventid_offset,
		revision_id = @eventid+$eventid_offset,
		language = 'und',
		delta = @seq,
		field_event_photos_fid = @fileid
	";
	db_query($q) or die(db_error());


} // End of foreach $two_types
