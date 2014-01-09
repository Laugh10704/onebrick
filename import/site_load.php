<?php
require("include.php");
require("open_v3.php");
dmsg("lid_offset is $lid_offset");


$file ="data/locs.csv"; 

// Bugin old data means created date is not set correctly so we just use modified.

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
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
			@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
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
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE node_access
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
		SET 
			nid = @lid+$lid_offset,
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
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
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
			@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
		SET 
			lid = @lid+$lid_offset,
			name=@name,
			street=@address1,
			additional=@address2,
			city=@city,
			province=@state,
			postal_code=@postal,
			latitude=0.0,
			longitude=0.0
";
db_query($q) or die(db_error());

$q = "
	LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE location_instance
	FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
			(@lid,  @name, @address1, @address2, @city, @state, @postal, 
			@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
		SET 
			lid = @lid+$lid_offset,
			vid = @lid+$lid_offset,
			nid = @lid+$lid_offset,
			uid=0
";
db_query($q) or die(db_error());


$two_types = array('data', 'revision');
foreach ($two_types as $t) {
	$q = "
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_site_driving
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@lid,  @name, @address1, @address2, @city, @state, @postal, 
				@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
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
				@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
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
				@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
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
		LOAD DATA LOCAL INFILE '".$file."' REPLACE INTO TABLE field_".$t."_field_site_chapter
		FIELDS TERMINATED BY ',' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
				(@lid,  @name, @address1, @address2, @city, @state, @postal, 
				@directions_drive, @directions_transit, @directions_other, @longitude, @latitude, @created, @modified, @chapter)
			SET 
				entity_type='node',
				bundle='site',
				entity_id=@lid+$lid_offset,
				revision_id=@lid+$lid_offset,
				language='und',
				field_site_chapter_nid=@chapter+$chapid_offset
	";
	db_query($q) or die(db_error());

} // End of foreach $two_types


