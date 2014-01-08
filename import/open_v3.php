<?php
//$db=mysql_connect('v3.onebrick.org', 'drupal7', '1_brick!') or die(mysql_error());
$db=mysql_connect('localhost:11642', 'pantheon', '7e661344b9cd4e1f8c6a0f394a30b374') or die(mysql_error());
mysql_select_db('pantheon', $db) or die(mysql_error());

