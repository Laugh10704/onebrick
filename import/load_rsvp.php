<?php
require("include.php");
require("open_v3.php");

$file = 'data/rsvp.csv';

$q = "
	LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
		SET 
			nid =@rsvpid,
			vid =@rsvpid,
			uid = 1,
			status=1,
			created=@rsvpdate,
			changed=@rsvpdate,
			comment=0,
			title = 'imported rsvp',
			type = 'rsvp',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
		SET 
			nid =@rsvpid,
			vid =@rsvpid,
			uid = 1,
			title = @name,
			timestamp=@created,
			status=1,
			comment=0,
			promote=0
";

db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
		SET 
			nid = @rsvpid,
			gid = 0,
			realm = 'all',
			grant_view=1,
			grant_update=0,
			grant_delete=0
";

db_query($q) or die(db_error());
$q = "
	LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
		SET 
			nid =@rsvpid,
			cid = 0,
			last_comment_timestamp=@created,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());


$two_types = array('data', 'revision');
foreach ($two_types as $t) {
  $q = "
					LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_rsvp_attended
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id=@rsvpid,
							revision_id=@rsvpid,
							language='und',
							field_rsvp_attended_value=@attended;
				";
  db_query($q) or die(db_error());

  $q = "
					LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_rsvp_event
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id=@rsvpid,
							revision_id=@rsvpid,
							language='und',
							field_rsvp_event_nid=@eventid+$eventid_offset
				";
  db_query($q) or die(db_error());

  $q = "
					LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_rsvp_person
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id=@rsvpid,
							revision_id=@rsvpid,
							language='und',
							field_rsvp_person_uid=@userid+$userid_offset
				";
  db_query($q) or die(db_error());

  $q = "
					LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_rsvp_role
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id=@rsvpid,
							revision_id=@rsvpid,
							language='und',
							field_rsvp_role_value=@role
				";
  db_query($q) or die(db_error());

  $q = "
					LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_rsvp_note
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id= @rsvpid,
							revision_id= @rsvpid,
							language='und',
							field_rsvp_note_value=@rsvpnote
				";
  db_query($q) or die(db_error());

  $q = "
					LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_public
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id= @rsvpid,
							revision_id= @rsvpid,
							language='und',
							field_public_value=0
				";
  db_query($q) or die(db_error());

} // End of foreach $two_types


// Add the EMs and ECs to the Event Structure (not just the rsvp list)

echo shell_exec("grep ,%Manager% < $file >$file.em");

$two_types = array('data', 'revision');
foreach ($two_types as $t) {
  $q = "
	LOAD DATA LOCAL INFILE '" . $file . ".em' REPLACE INTO TABLE field_" . $t . "_field_manager
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
		( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
		SET 
			entity_type='node',
			bundle='event',
			entity_id=@eventid+$eventid_offset,
			revision_id=@eventid+$eventid_offset,
			language='und',
			field_manager_uid=@userid+$userid_offset;
	";
  db_query($q) or die(db_error());
} // End of foreach $two_types managers

echo shell_exec("grep ,%Coordinator% < $file >$file.ec");

$two_types = array('data', 'revision');
foreach ($two_types as $t) {
  $q = "
		LOAD DATA LOCAL INFILE '" . $file . ".ec' REPLACE INTO TABLE field_" . $t . "_field_coordinator
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
			SET 
				entity_type='node',
				bundle='event',
				entity_id=@eventid+$eventid_offset,
				revision_id=@eventid+$eventid_offset,
				language='und',
				field_coordinator_uid=@userid+$userid_offset;
	";
  db_query($q) or die(db_error());
} // End of foreach $two_types coordiantors
