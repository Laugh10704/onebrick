<?php
require("include.php");
require("open_v3.php");
dmsg("userid_offset is $userid_offset");

$two_types = array('data', 'revision');
foreach ($two_types as $t) {

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
}

