<?php
require("include.php");
require("open_v3.php");

$two_types = array('data', 'revision');

for ($i = 1; $i <= 10; $i++) {
  $file = "data/profile$i.csv";

  foreach ($two_types as $t) {
    $q = "
			LOAD DATA LOCAL INFILE '" . $file . "' REPLACE INTO TABLE field_" . $t . "_field_user_profile_q${i}
			FIELDS TERMINATED BY '^^^' ESCAPED BY '*' OPTIONALLY ENCLOSED BY '%' LINES TERMINATED BY 'XYXXY'
					(@userid, @question, @answer, @date)
		SET 
			entity_type='user',
			bundle='user',
			deleted = '0',
			entity_id=@userid+$userid_offset,
			revision_id=@userid+$userid_offset,
			language='und',
			field_user_profile_q${i}_value=@answer
			";
    db_query($q) or die(db_error());
    ck($file);
  }
}
