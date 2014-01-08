<?php
require("include.php");
echo 1;
require("open_v3.php");
echo 2;
dmsg("orgid_offset is $orgid_offset");

/* Load the User */
$file = 'data/orgs.csv';

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
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' 
	(@orgid,@name, @description, @url, @regionid, @created, @modified)
		SET 
			nid = @orgid+$orgid_offset,
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
