<?php
require("include.php");
require("open_v3.php");

/* Load the User */
$curtime = time();
$file = 'data/chapters.csv';

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
			comment=2,
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
			comment=2
";

db_query($q) or die(db_error());


$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
	(@chapid, @name, @facebook_url, @twitter_url, @craigslist_stub, @created, @modified)
		SET 
			nid = @chapid+$chapid_offset,
			gid = 0,
			realm = 'all',
			grant_view=1,
			grant_update=0,
			grant_delete=0
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
