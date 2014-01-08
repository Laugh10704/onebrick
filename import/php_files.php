all_dump.php                                                                                        0000644 0000000 0000000 00000000463 11600442652 012065  0                                                                                                    ustar   root                            root                                                                                                                                                                                                                   <?php

$dump_files = array (
				'chapter_dump.php',
				'user_dump.php',
				'site_dump.php',
				'org_dump.php',
				'event_dump.php',
				'org_users_dump.php',
				'rsvp_dump.php'
// 'roles_dump.php'
);

echo "Dumping data...\n";
foreach ($dump_files as $f) {
	echo "$f\n";
	exec ("/usr/bin/php ".$f);
}
                                                                                                                                                                                                             all.php                                                                                             0000664 0000765 0000765 00000001123 11600441127 012002  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php

$dump_files = array (
				'chapter_dump.php',
				'user_dump.php',
				'site_dump.php',
				'org_dump.php',
				'event_dump.php',
				'org_users_dump.php',
				'rsvp_dump.php'
// 'roles_dump.php'
);

$load_files = array (
				'chapter_load.php', 
				'user_load.php', 
				'site_load.php', 
				'org_load.php', 
				'event_load.php', 
				'org_users_load.php', 
				'rsvp_load.php' 
// 'roles_dump.php'
);

echo "DUMP\n";
foreach ($dump_files as $f) {
	echo "$f\n";
	exec ("/usr/bin/php ".$f);
}

echo "LOAD\n";
foreach ($load_files as $f) {
	echo "$f\n";
//	exec ("/usr/bin/php ".$f);
}
                                                                                                                                                                                                                                                                                                                                                                                                                                             chapter_dump.php                                                                                    0000664 0000765 0000765 00000000772 11600441463 013721  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
$record="chapter";
require("include.php");
require("open_v2.php");

$file="data/chapters.csv";
rm($file);

/* Dump location data */
$q="
				select regionid, name, facebook_url, twitter_url, craigslist_name, UNIX_TIMESTAMP(datecreated), UNIX_TIMESTAMP(datemodified)
								INTO OUTFILE '".$file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				FROM 	region
				WHERE region.active
";

db_query($q) or die(db_error()); ck($file);
      chapter_load.php                                                                                    0000664 0000765 0000765 00000007470 11574260560 013704  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");
echo "he\n";
dmsg("chapid_offset is $chap_id_offset");

/* Load the User */
$curtime = time();
$file = '/tmp/data/chapters.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)
		SET 
			nid = @chapid+$chapid_offset,
			vid = @chapid+$chapid_offset,
			uid = 1,
			status=1,
			created=@created,
			changed=@modified,
			comment=0,
			title = @name,
			type = 'chapter',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)
		SET 
			nid = @chapid+$chapid_offset,
			vid = @chapid+$chapid_offset,
			uid = 1,
			title = @name,
			timestamp=$curtime,
			status=1,
			comment=0
";

db_query($q) or die(db_error());
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)

		SET 
			nid = @chapid+$chapid_offset,
			cid = 0,
			last_comment_timestamp=$curtime,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_data_body
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)

		SET 
			entity_type='node',
			bundle='chapter',
			entity_id=@chapid+$chapid_offset,
			revision_id=@chapid+$chapid_offset,
			language='und',
			body_format='filtered_html',
			body_value='',
			body_summary=''
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
foreach ($two_types as $t) {
				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_chapter_craigslist_stub
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub)
						SET 
							entity_type='node',
							bundle='chapter',
							entity_id=@chapid+$chapid_offset,
							revision_id=@chapid+$chapid_offset,
							language='und',
							field_chapter_craigslist_stub_value=@craigslist_stub;
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_chapter_twitter_url
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)

						SET 
							entity_type='node',
							bundle='chapter',
							entity_id=@chapid+$chapid_offset,
							revision_id=@chapid+$chapid_offset,
							language='und',
							field_chapter_twitter_url_value=@twitter_url;
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_chapter_facebook_url
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)

						SET 
							entity_type='node',
							bundle='chapter',
							entity_id=@chapid+$chapid_offset,
							revision_id=@chapid+$chapid_offset,
							language='und',
							field_chapter_facebook_url_value=@facebook_url;
				";
				db_query($q) or die(db_error());


} // End of foreach $two_types
                                                                                                                                                                                                        event_dump.php                                                                                      0000664 0000765 0000765 00000002013 11600441500 013372  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v2.php");

$file="/tmp/data/events.csv"; rm($file);

// Content Type Fields
//Title
//Description
//Requested
//RSVP Capacity
//Date and Time
//RSVP Date
//Site
//Organization
//Photos ---- NOT DONE YET
//Chapter
//Status

$q="
				SELECT DISTINCT event.eventid, event.name, event.description, event.shortdescription, event.capacity,
												event.rsvpcapacity, event.time_start, event.time_end, UNIX_TIMESTAMP(event.rsvpdate), event.locationid,
												event.organizationid, event.regionid, event_status.name, event_type.name, 
												UNIX_TIMESTAMP(event.datecreated), UNIX_TIMESTAMP(event.datemodified)

								INTO OUTFILE '".$file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'

				FROM event
								LEFT JOIN event_status on event.statusid = event_status.statusid
								LEFT JOIN event_type on event.typeid = event_type.typeid


				WHERE event.active =1

";

db_query($q) or die(db_error()); ck($file);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     event_load.php                                                                                      0000664 0000765 0000765 00000020767 11574260745 013410  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");
//$limit=1;

/* Load the User */
$file = '/tmp/data/events.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_max_rsvp_capacity
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_rsvp_date
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
						SET 
							entity_type='node',
							bundle='event',
							entity_id=@eventid+$eventid_offset,
							revision_id=@eventid+$eventid_offset,
							language='und',
							field_event_rsvp_date_value = @rsvpdate
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_event_site
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
				(@eventid, @name, @description, @shortdescription, @capacity, @rsvpcapacity, @time_start, @time_end, @rsvpdate, @locationid, @organizationid, @regionid, @status, @etype, @created, @modified)
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
         include.php                                                                                         0000664 0000765 0000765 00000002706 11563005360 012670  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php

// We try to keep everything liked together by using the table ids from the old system.
// Unfortunately most objects are stored as nodes. 
//We can't just put them into the node table with their old id because they will overlap. 
//So we add an offset so that each node has it's own space.

$chapid_offset = 100;	// old was 1i to 12, new is from 101-113
$lid_offset = 1000;	// old was 53 to about 2,200, new is from 1,053-3,200
$orgid_offset = 4000;	// old was 1 to about 1,400, new is from 4,001-5,400
$org_lid_offset = 6000; // unfortunately orgs have addresses in them and we need to merge  with site locations
$eventid_offset = 20000;	// old was 53 to about 9,000, new is from 10,053-19,000
$rsvp_offset = 100000; // Need to make sure that when we ass user id and event id we get a unique value

$userid_offset = 0;	// old was 62 to about 80,000, don't need to increment as users 
$orgcontact_offset = 80000; // there are less than 1500 



function dmsg($s) {
					echo $s."\n";
}

function rm($f) {
				if(file_exists($f))
					return (unlink($f));
				return(true);
}

function ck($f) {
				if(!file_exists($f)) {
					echo "$f: does not exist\n";
					return (false);
				}
				$fp = fopen($f, "r" );
				$l=fgets($fp);
				$c=1;

				while(fgets($fp))
					$c++;

				fclose($fp);

				echo "$f($c lines):\t$l";
				return(true);
}


function db_query($query) {
		dmsg($query);
		return(mysql_query($query));
}

function db_error() {
		return(mysql_error());
}
                                                          open_v2.php                                                                                         0000664 0000765 0000765 00000000707 11600427413 012613  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php

//$db=mysql_connect('db101.onsitetechnical.com', 'root', 'mandy24') or die(mysql_error());
$db=mysql_connect('db.linux1.onebrick.org', 'root', 'mandy24') or die(mysql_error());
mysql_select_db('onebrick', $db) or die(mysql_error());

$record='event';
if($record) {
				/*check the connection */
				$q="select * FROM ".$record;
				$r=mysql_query($q) or die(mysql_error());
				$nr=mysql_num_rows($r);
				echo "Found $nr ${record} records\n";
}


                                                         open_v3.php                                                                                         0000664 0000765 0000765 00000000226 11565317445 012625  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php

$db=mysql_connect('alpha2db.1btest.org', 'root1b', 'OneBrick') or die(mysql_error());
mysql_select_db('alpha2db', $db) or die(mysql_error());

                                                                                                                                                                                                                                                                                                                                                                          org_dump.php                                                                                        0000664 0000765 0000765 00000001315 11600441510 013045  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php $record="user";
require("include.php");
require("open_v2.php");

$file="/tmp/data/orgs.csv";
rm($file);

$q="
				SELECT DISTINCT organization.organizationid, organization.name, organization.description, 
												organization.url, organization_region.regionid, 
												UNIX_TIMESTAMP(organization.datecreated), UNIX_TIMESTAMP(organization.datemodified)

								INTO OUTFILE '".$file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'

				FROM organization
					LEFT JOIN organization_region ON organization.organizationid = organization_region.organizationid
				WHERE organization.active =1

";

db_query($q) or die(db_error()); ck($file);
                                                                                                                                                                                                                                                                                                                   org_load.php                                                                                        0000664 0000765 0000765 00000006274 11574476057 013060  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");
dmsg("orgid_offset is $org_offset");

/* Load the User */
$file = '/tmp/data/orgs.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@orgid,	@name, @description, @url, @regionid, @created, @modified)
	SET
			nid = @orgid+$orgid_offset,
			vid = @orgid+$orgid_offset,
			uid = 1,
			status=1,
			created=@modified,
			changed=@modified,
			comment=0,
			title = @name,
			type = 'organization',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@orgid,	@name, @description, @url, @regionid, @created, @modified)
	SET
			nid = @orgid+$orgid_offset,
			gid = 0,
			realm = 'all',
			grant_view = 1,
			grant_update = 0,
			grant_delete = 0,
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@orgid,	@name, @description, @url, @regionid, @created, @modified)
		SET 
			nid = @orgid+$orgid_offset,
			vid = @orgid+$orgid_offset,
			uid = 1,
			title = @name,
			timestamp=@modified,
			status=1,
			comment=0
";

db_query($q) or die(db_error());
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@orgid,	@name, @description, @url, @regionid, @created, @modified)
		SET 
			nid = @orgid+$orgid_offset,
			cid = 0,
			last_comment_timestamp=@modified,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
foreach ($two_types as $t) {
				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_body
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@orgid,	@name, @description, @url, @regionid, @created, @modified)
						SET 
							entity_type='node',
							bundle='organization',
							entity_id=@orgid+$orgid_offset,
							revision_id=@orgid+$orgid_offset,
							language='und',
							body_format='filtered_html',
							body_value=@description,
							body_summary=''
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_organization_chapter
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@orgid,	@name, @description, @url, @regionid, @created, @modified)
						SET 
							entity_type='node',
							bundle='organization',
							entity_id=@orgid+$orgid_offset,
							revision_id=@orgid+$orgid_offset,
							language='und',
							field_organization_chapter_nid=@regionid+$chapid_offset
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_organization_website
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@orgid,	@name, @description, @url, @regionid, @created, @modified)
						SET 
							entity_type='node',
							bundle='organization',
							entity_id=@orgid+$orgid_offset,
							revision_id=@orgid+$orgid_offset,
							language='und',
							field_organization_website_value=@url
				";
				db_query($q) or die(db_error());

} // End of foreach $two_types
                                                                                                                                                                                                                                                                                                                                    org_users_dump.php                                                                                  0000664 0000765 0000765 00000005352 11600441521 014275  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v2.php");

// create a user accoutn for each org contact
$file="/tmp/data/org_users.csv"; rm($file);
$pst_file="/tmp/data/pst_org_users.csv"; rm($pst_file);
$cst_file="/tmp/data/cst_org_users.csv"; rm($cst_file);
$est_file="/tmp/data/est_org_users.csv"; rm($est_file);
$q=" 
				SELECT contactid, concat(firstname, ' ', lastname), title, email1, workphone, organization_contact.organizationid, 'America/Los_Angeles', organization_region.regionid, UNIX_TIMESTAMP(organization_contact.datecreated), UNIX_TIMESTAMP(organization_contact.datemodified)
								INTO OUTFILE '".$pst_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM organization_contact
				LEFT JOIN organization_region ON organization_region.organizationid=organization_contact.organizationid
				WHERE organization_contact.active =1 and organization_region.regionid in (1, 7, 11)
				order by contactid
";

db_query($q) or die(db_error()); ck($cst_file);
$q=" 
				SELECT contactid,  concat(firstname, ' ', lastname),  title, email1, workphone, organization_contact.organizationid, 'America/Chicago', organization_region.regionid, UNIX_TIMESTAMP(organization_contact.datecreated), UNIX_TIMESTAMP(organization_contact.datemodified) 
								INTO OUTFILE '".$cst_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM organization_contact
				LEFT JOIN organization_region ON organization_region.organizationid=organization_contact.organizationid
				WHERE organization_contact.active =1 and organization_region.regionid in (3, 5, 10)
				order by contactid
";

db_query($q) or die(db_error()); ck($est_file);
$q=" 
				SELECT contactid,  concat(firstname, ' ', lastname),  title, email1, workphone, organization_contact.organizationid, 'America/New_York', organization_region.regionid, UNIX_TIMESTAMP(organization_contact.datecreated), UNIX_TIMESTAMP(organization_contact.datemodified) 
								INTO OUTFILE '".$est_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM organization_contact
				LEFT JOIN organization_region ON organization_region.organizationid=organization_contact.organizationid
				WHERE organization_contact.active =1 and organization_region.regionid in (2, 4, 9)
				order by contactid
";

db_query($q) or die(db_error()); ck($pst_file);

// Contact the 3 chapters files together
$fp_out = fopen($file, "w" );
$fp = fopen($pst_file, "r" ); while($l=fgets($fp)) { fputs($fp_out, $l); } fclose($fp); rm($pst_file);
$fp = fopen($est_file, "r" ); while($l=fgets($fp)) { fputs($fp_out, $l); } fclose($fp); rm($est_file);
$fp = fopen($cst_file, "r" ); while($l=fgets($fp)) { fputs($fp_out, $l); } fclose($fp); rm($cst_file);
fclose($fp_out);
                                                                                                                                                                                                                                                                                      org_users_load.php                                                                                  0000664 0000765 0000765 00000012122 11574460536 014261  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");

/* Load the User */
$file = '/tmp/data/org_users.csv';
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

	db_query($q) or die(db_error());
}

                                                                                                                                                                                                                                                                                                                                                                                                                                              roles_dump.php                                                                                      0000664 0000765 0000765 00000004722 11556653627 013437  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v2.php");


$gid_map = array (
  200 => 4, // Coordinator
	300	=> 5, // Manager
	400	=> 6, // Event Creation
	500	=> 7, // Staff Assignment
	350	=> 8, // Recruiting
	600	=> 9, // Content Managment
);

// Dump all the users as ordinaty (volunteer) members
$file = "/tmp/data/roles_2.csv"; rm($file);

$q=" 
				SELECT DISTINCT user.userid, user_region.regionid, 2
						INTO OUTFILE '".$file."'
				FIELDS
						TERMINATED BY ','
				FROM  user 
				JOIN  user_region ON  user_region.userid =  user.userid
				WHERE user.active
							AND user.statusid =1
";
if ($limit)
	$q .= "LIMIT $limit";

db_query($q) or die(db_error()); ck($file);

// Dump Managers etc
foreach ($gid_map as $old => $new) {
				$file = "/tmp/data/roles_".$new.".csv"; echo "$file\n";
				rm($file);

				$q=" 
								SELECT DISTINCT user.userid, user_region.regionid, $new
										INTO OUTFILE '".$file."'
								FIELDS
										TERMINATED BY ','
								FROM  user 
								JOIN  user_group ON user_group.userid =  user.userid
								JOIN  `group` ON group.groupid =  user_group.groupid
								JOIN  group_category ON group_category.categoryid = group.categoryid AND group_category.categoryid =100
								JOIN  user_region ON  user_region.userid =  user.userid
								WHERE user.active
											AND user.statusid =1
											AND user_group.groupid =$old
				";
				if ($limit)
					$q .= "LIMIT $limit";

				db_query($q) or die(db_error()); ck($file);
}

// Mark all people form the org contact table as Requestors (11)
$file = "/tmp/data/roles_11.csv"; rm($file);

$q=" 
				SELECT DISTINCT contactid+$orgcontact_offset, organization_region.regionid, 11
						INTO OUTFILE '".$file."'
				FIELDS
						TERMINATED BY ','
				FROM organization_contact
						LEFT JOIN organization_region ON 
							organization_region.organizationid=organization_contact.organizationid
				WHERE organization_contact.active =1
";
if ($limit)
	$q .= "LIMIT $limit";

db_query($q) or die(db_error()); ck($file);
// Contat all the files together.

$fp_out = fopen("/tmp/data/roles.csv", "w" );

$fp = fopen("/tmp/data/roles_2.csv", "r" );
				while($l=fgets($fp)) { fputs($fp_out, $l); }
fclose($fp); 

foreach ($gid_map as $old => $i) {
				$fp = fopen("/tmp/data/roles_".$i.".csv", "r" ); 
								while($l=fgets($fp)) { fputs($fp_out, $l); }
				fclose($fp);
}

$fp = fopen("/tmp/data/roles_11.csv", "r" );
				while($l=fgets($fp)) { fputs($fp_out, $l); }
fclose($fp); 

fclose($fp_out);

exit(0);

                                              rsvp_dump.php                                                                                       0000664 0000765 0000765 00000004427 11600441566 013272  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php require("include.php");
require("open_v2.php");

$file="/tmp/data/rsvp.csv"; 		rm($file);
$file1="/tmp/data/rsvp_vol.csv";rm($file1);
$file2="/tmp/data/rsvp_ec.csv"; rm($file2);
$file3="/tmp/data/rsvp_em.csv"; rm($file3);


// Dump Vols, ECs and EMs seperately because it makes it easy to translate '1' into Vol, '2' into EC, etc

/* Dump Volunteer data */
$q="
				SELECT shiftid, 'Volunteer', userid, attended, UNIX_TIMESTAMP(rsvpdate), rsvpnote
								INTO OUTFILE '".$file1."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
				FROM shift_user
				WHERE shift_user.active and shift_user.roleid=1
";

db_query($q) or die(db_error()); ck($file1);

/* Dump Coordinators data */
/* Bug fix, hard wire EM and EC to always mark as attended */
$q="
				SELECT shiftid, 'Coordinator', userid, attended, UNIX_TIMESTAMP(rsvpdate), rsvpnote
								INTO OUTFILE '".$file2."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
				FROM shift_user
				WHERE shift_user.active and shift_user.roleid=2
";

db_query($q) or die(db_error()); ck($file2);
/* Dump Managers data */
/* Bug fix, hard wire EM and EC to always mark as attended */
$q="
				SELECT shiftid, 'Manager', userid, '1', UNIX_TIMESTAMP(rsvpdate), rsvpnote
								INTO OUTFILE '".$file3."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
				FROM shift_user
				WHERE shift_user.active and shift_user.roleid=3
";

db_query($q) or die(db_error()); ck($file3);

// Merge the 3 files into one file and add an index to the start of the line

$index = $rsvp_offset;

$fp_out = fopen($file, "w" );

$fp = fopen($file1, "r" );
while($l=fgets($fp)) {
	$l = rtrim($l);
	fprintf($fp_out, "%s", $l);
	if ($l[strlen($l)-1]=='%') {
					fprintf($fp_out, ",%d", $index++);
	}
	fputs($fp_out, "\n");
}
fclose($fp); rm($file1);

$fp = fopen($file2, "r" );
while($l=fgets($fp)) {
	$l = rtrim($l);
	fprintf($fp_out, "%s", $l);
	if ($l[strlen($l)-1]=='%') {
					fprintf($fp_out, ",%d", $index++);
	}
	fputs($fp_out, "\n");
}
fclose($fp); rm($file2);

$fp = fopen($file3, "r" );
while($l=fgets($fp)) {
	$l = rtrim($l);
	fprintf($fp_out, "%s", $l);
	if ($l[strlen($l)-1]=='%') {
					fprintf($fp_out, ",%d", $index++);
	}
	fputs($fp_out, "\n");
}
fclose($fp); rm($file3);

fclose($fp_out);

                                                                                                                                                                                                                                         rsvp_load.php                                                                                       0000664 0000765 0000765 00000007302 11574473132 013243  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");

$file = '/tmp/data/rsvp.csv';

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
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
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
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
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
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
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_rsvp_attended
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
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_rsvp_event
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
						( @eventid, @role, @userid, @attended, @rsvpdate, @rsvpnote, @rsvpid)
						SET 
							entity_type='node',
							bundle='rsvp',
							entity_id=@rsvpid,
							revision_id=@rsvpid,
							language='und',
							field_rsvp_event_nid= @eventid+$eventid_offset
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_rsvp_person
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
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_rsvp_role
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
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_rsvp_note
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

} // End of foreach $two_types
                                                                                                                                                                                                                                                                                                                              site_dump.php                                                                                       0000664 0000765 0000765 00000001577 11600442075 013243  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v2.php");

$file="/tmp/data/locs.csv"; rm($file);
$file2="/tmp/data/lids_tmp.csv";  rm($file2);

/* Dump location data */
$q="
				select locationid,	name, address1, address2, city, state, postal, directions_drive, directions_transit, directions_other, longitude, latitude, UNIX_TIMESTAMP(datecreated),  UNIX_TIMESTAMP(datemodified)
								INTO OUTFILE '".$file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				FROM 	location
				WHERE location.active and
							location.statusid = 1
";

db_query($q) or die(db_error()); ck($file);

/* Dump location data */
$q="
				select locationid
								INTO OUTFILE '".$file2."'
				FIELDS
								TERMINATED BY ',' 
				FROM 	location
				WHERE location.active and
							location.statusid = 1
";

db_query($q) or die(db_error()); ck($file2);
                                                                                                                                 site_load.php                                                                                       0000664 0000765 0000765 00000013070 11600442120 013173  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");
dmsg("lid_offset is $lid_offset");


$file ="/tmp/data/locs.csv"; 
$file2="/tmp/data/lids_tmp.csv"; 
$file3="/tmp/data/lids.csv"; 

// Bugin old data means created date is not set correctly so we just use modified.

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
		SET 
			nid = @lid+$lid_offset,
			vid = @lid+$lid_offset,
			uid = 1,
			status=1,
			created=@modified,
			changed=@modified,
			comment=0,
			title = @name,
			type = 'site',
			language = 'und'
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_revision
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
		SET 
			nid = @lid+$lid_offset,
			vid = @lid+$lid_offset,
			uid = 1,
			title = @name,
			timestamp=@modified,
			status=1,
			comment=0
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_comment_statistics
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
		SET 
			nid = @lid+$lid_offset,
			cid = 0,
			last_comment_timestamp=@modified,
			last_comment_uid = 1,
			comment_count = 0
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE location
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
		SET 
			lid = @lid+$lid_offset,
			name=@name,
			street=@address1,
			additional=@address2,
			city=@city,
			province=@state,
			postal_code=@postal,
			latitude=@latitude,
			longitude=@longitude
";
db_query($q) or die(db_error());

$two_types = array('data', 'revision');
foreach ($two_types as $t) {
				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_site_driving
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@lid,  @name, @address1, @address2, @city, @state, @postal, 
							@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
						SET 
							entity_type='node',
							bundle='site',
							entity_id=@lid+$lid_offset,
							revision_id=@lid+$lid_offset,
							language='und',
							field_site_driving_value=@directions_drive
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_site_transit
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@lid,  @name, @address1, @address2, @city, @state, @postal, 
							@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
						SET 
							entity_type='node',
							bundle='site',
							entity_id=@lid+$lid_offset,
							revision_id=@lid+$lid_offset,
							language='und',
							field_site_transit_value=@directions_transit
				";
				db_query($q) or die(db_error());

				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_site_please_note
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@lid,  @name, @address1, @address2, @city, @state, @postal, 
							@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
						SET 
							entity_type='node',
							bundle='site',
							entity_id=@lid+$lid_offset,
							revision_id=@lid+$lid_offset,
							language='und',
							field_site_please_note_value=@directions_other
				";
				db_query($q) or die(db_error());


				$q = "
					LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_site_location
					FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
							(@lid,  @name, @address1, @address2, @city, @state, @postal, 
							@directions_drive, @directions_transit, @directions_other, @longistude, @latitude, @created, @modified)
						SET 
							entity_type='node',
							bundle='site',
							entity_id=@lid+$lid_offset,
							revision_id=@lid+$lid_offset,
							language='und',
							field_site_location_lid=@lid+$lid_offset
				";
				db_query($q) or die(db_error());
} // End of foreach $two_types



// We need to MUNGE the file to add 'cck_field_organization_address:@lid+$lid_offset, at the start

$fp2 = fopen($file2, "r" );
$fp3 = fopen($file3, "w" );

while($l=fgets($fp2)) {
	$new_lid =  $l + $lid_offset;
	fprintf($fp3, "%d,cck:field_site_location:%d\n", $new_lid, $new_lid);
}
fclose($fp2); rm($file2); fclose($fp3); 

$q = "
	LOAD DATA LOCAL INFILE '".$file3."' REPLACE INTO TABLE location_instance
	FIELDS TERMINATED BY ',' 
			(@new_lid,  @genid)
		SET 
			nid = @new_lid,
			vid = @new_lid,
			lid = @new_lid,
			uid=0,
			genid = @genid
";
db_query($q) or die(db_error());

                                                                                                                                                                                                                                                                                                                                                                                                                                                                        subs.php                                                                                            0000664 0000765 0000765 00000000433 11571223372 012220  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v2.php");

$sub_file="/tmp/rsvps.csv"; rm($sub_file);
$q=" 
				SELECT distinct userid, datemodified 
								INTO OUTFILE '".$sub_file."'
				from shift_user order by datemodified DESC
";

db_query($q) or die(db_error()); ck($sub_file);

                                                                                                                                                                                                                                     user_dump.php                                                                                       0000664 0000765 0000765 00000005545 11600441675 013261  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v2.php");
$file="/tmp/data/users.csv"; rm($file);

// Dump everyone as a VOLUNTEER into 3 timezone files. 
$pst_file="/tmp/data/pst_users.csv"; rm($pst_file);
$q=" 
				SELECT user.userid, fullname, email, phone, 'America/Los_Angeles', user_region.regionid, UNIX_TIMESTAMP(user.datecreated)
								INTO OUTFILE '".$pst_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM user
				LEFT JOIN user_region ON user_region.userid=user.userid
				LEFT JOIN region on user_region.regionid=region.regionid
				LEFT JOIN user_attribute on user.userid=user_attribute.userid
				WHERE user.active =1 and user_region.regionid in (1, 7, 11)
";

db_query($q) or die(db_error()); ck($pst_file);

$est_file="/tmp/data/est_users.csv"; rm($est_file);
$q=" 
				SELECT DISTINCT user.userid, fullname, email, phone, 'America/New_York', user_region.regionid, UNIX_TIMESTAMP(user.datecreated)
								INTO OUTFILE '".$est_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM user
				LEFT JOIN user_region ON user_region.userid=user.userid
				LEFT JOIN region on user_region.regionid=region.regionid
				LEFT JOIN user_attribute on user.userid=user_attribute.userid
				WHERE user.active =1 and user_region.regionid in (2, 4, 9)
";

db_query($q) or die(db_error()); ck($est_file);

$cst_file="/tmp/data/cst_users.csv"; rm($cst_file);
$q=" 
				SELECT DISTINCT user.userid, fullname, email, phone, 'America/Chicago', user_region.regionid, UNIX_TIMESTAMP(user.datecreated)
								INTO OUTFILE '".$cst_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM user
				LEFT JOIN user_region ON user_region.userid=user.userid
				LEFT JOIN region on user_region.regionid=region.regionid
				LEFT JOIN user_attribute on user.userid=user_attribute.userid
				WHERE user.active =1 and user_region.regionid in (3, 5, 10)
";


db_query($q) or die(db_error()); ck($cst_file);

// Contact the 3 chapters files together
$fp_out = fopen($file, "w" );
$fp = fopen($pst_file, "r" ); while($l=fgets($fp)) { fputs($fp_out, $l); } fclose($fp); rm($pst_file);
$fp = fopen($est_file, "r" ); while($l=fgets($fp)) { fputs($fp_out, $l); } fclose($fp); rm($est_file);
$fp = fopen($cst_file, "r" ); while($l=fgets($fp)) { fputs($fp_out, $l); } fclose($fp); rm($cst_file);
fclose($fp_out);


// Dump out a record ONLY for those that are subscribed

$sub_file="/tmp/data/users_subscription.csv"; rm($sub_file);
$q=" 
				SELECT DISTINCT user.userid
								INTO OUTFILE '".$sub_file."'
				FIELDS
								TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
				FROM user
				LEFT JOIN user_attribute on user.userid=user_attribute.userid
				WHERE user.active =1 and user_attribute.attributeid = 101 and
				user_attribute.active = 1
";


db_query($q) or die(db_error()); ck($sub_file);

                                                                                                                                                           user_load.php                                                                                       0000664 0000765 0000765 00000004775 11574261331 013236  0                                                                                                    ustar   hmssys                          hmssys                                                                                                                                                                                                                 <?php
require("include.php");
require("open_v3.php");
dmsg("userid_offset is $userid_offset");

/* Load the User */
$file = '/tmp/data/users.csv';
$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE users
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
			(@uid, @name, @mail, @phone, @timezone, @chapter, @created)
		SET 
			uid = @uid,
			name = @name,
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

	db_query($q) or die(db_error());

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_chapter
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @mail, @phone, @timezone, @chapter, @created)
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
			(@uid, @name, @mail, @phone, @timezone, @chapter, @created)
		SET
			entity_type = 'user',
			bundle = 'user',
			entity_id = @uid,
			revision_id = @uid,
			language = 'und',
			field_user_phone_value=@phone
	";
	db_query($q) or die(db_error());

	// By default we create people as unsubscribed, then update later from the subscribed file
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_subscribed
		FIELDS  TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%'
			(@uid, @name, @mail, @phone, @timezone, @chapter, @created)
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
$file = '/tmp/data/users_subscription.csv';
$two_types = array('data', 'revision');
foreach ($two_types as $t) {

	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_user_subscribed
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
