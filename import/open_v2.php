<?php

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


