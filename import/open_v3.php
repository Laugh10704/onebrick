<?php
//$db=mysql_connect('v3.onebrick.org', 'drupal7', '1_brick!') or die(mysql_error());
echo "inside open_v3.php\n";
//$db=mysql_connect('dbserver.dev.165bd75f-04b4-2bf8-e387-64aa2c58b0d1.drush.in:11642', 'pantheon', '7e661344b9cd4e1f8c6a0f394a30b374') or die(mysql_error());
//mysql_select_db('pantheon', $db) or die(mysql_error());
$db=mysql_connect('localhost', 'www', '') or die(mysql_error());
mysql_select_db('onebrick', $db) or die(mysql_error());

