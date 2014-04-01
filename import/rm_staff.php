<?php
require("include.php");
require("open_v3.php");

$tables = array();

foreach ($tables as $t) {
  //echo "$t\n";
  db_query($t) or die(db_error());
}
