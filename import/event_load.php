<?php
require("include.php");
require("open_v3.php");
$limit=1;

$now = time();

/* Load the User */
$file = 'data/events.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
	SET
			nid = @eventid+$eventid_offset,
			vid = @eventid+$eventid_offset,
			uid = 1,
			status=1,
			created=@created,
			changed=@modified,
			comment=0,
			title = @name,
			type = 'event',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
		SET 
			nid = @eventid+$eventid_offset,
			gid = 0,
			realm = 'all',
			grant_view = 1,
			grant_update = 0,
			grant_delete = 0
";

db_query($q) or die(db_error());
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
		SET 
			nid = @eventid+$eventid_offset,
			vid = @eventid+$eventid_offset,
			uid = 1,
			title = @name,
			timestamp=@created,
			status=1,
			comment=0
";

db_query($q) or die(db_error());
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
		SET 
			nid = @eventid+$eventid_offset,
			cid = 0,
			last_comment_timestamp=@created,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());


$two_types = array('data', 'revision');
foreach ($two_types as $t) {
				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_body
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							body_format='filtered_html',
							body_value=@description,
							body_summary=@shortdescription
				";
				db_query($q) or die(db_error());


				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_chapter
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_chapter_nid=@regionid + $chapid_offset 
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_date
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_date_value = @time_start,
							field_event_date_value2 =  @time_end
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_organization
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_organization_nid=@organizationid+$orgid_offset
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_requested
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_requested_value = @capacity
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_otherinfo
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							deleted=0,
							delta=0,
							field_event_otherinfo_value = @otherinfo,
							field_event_otherinfo_format = 'filtered_html'
	
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_max_rsvp_capacity
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_max_rsvp_capacity_value =  @rsvpcapacity
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_rsvp_date
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_rsvp_date_value = @rsvpdate
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_site
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_site_nid = @locationid + $lid_offset
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_status
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_status_value = @status
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_type
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @otherinfo, @capacity,  @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified, @reconciled)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_type_value = @etype
				";
				db_query($q) or die(db_error());
} // End of foreach $two_types


$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/Los_Angeles', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/Los_Angeles', 'UTC')
 WHERE field_event_chapter_nid = 101; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 102; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/Chicago', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/Chicago', 'UTC')
 WHERE field_event_chapter_nid = 103; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 104; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/Chicago', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/Chicago', 'UTC')
 WHERE field_event_chapter_nid = 105; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/Los_Angeles', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/Los_Angeles', 'UTC')
 WHERE field_event_chapter_nid = 107; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 109; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 110; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/Los_Angeles', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/Los_Angeles', 'UTC')
 WHERE field_event_chapter_nid = 111; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/Los_Angeles', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/Los_Angeles', 'UTC')
 WHERE field_event_chapter_nid = 112; ";

db_query($q) or die(db_error());
$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 114; ";
db_query($q) or die(db_error());

$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 115; ";

db_query($q) or die(db_error());
$q =" 
UPDATE field_data_field_event_date INNER JOIN
       field_data_field_event_chapter ON
          (field_data_field_event_date.entity_id = field_data_field_event_chapter.entity_id)
   SET field_event_date_value = CONVERT_TZ(field_event_date_value, 'America/New_York', 'UTC'),
       field_event_date_value2 = CONVERT_TZ(field_event_date_value2, 'America/New_York', 'UTC')
 WHERE field_event_chapter_nid = 116; ";
