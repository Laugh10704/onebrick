<?php
require("include.php");
require("open_v3.php");

$fp = fopen("rm_users.sql", "r");

while (($q = fgets($fp, 4096)) !== FALSE) {
  db_query($q) or die(db_error());
}
fclose($fp);

exit(0);
