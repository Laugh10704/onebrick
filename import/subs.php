<?php
require("include.php");
require("open_v2.php");

$sub_file="/tmp/rsvps.csv"; rm($sub_file);
$q=" 
				SELECT distinct userid, datemodified 
								INTO OUTFILE '".$sub_file."'
				from shift_user order by datemodified DESC
";

db_query($q) or die(db_error()); ck($sub_file);

