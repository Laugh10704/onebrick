<?php
require("include.php");

function find_files($path, $pattern, $callback) {
  $path = rtrim(str_replace("\\", "/", $path), '/') . '/';
  $matches = Array();
  $entries = Array();
  $dir = dir($path);
  while (FALSE !== ($entry = $dir->read())) {
    $entries[] = $entry;
  }
  $dir->close();
  foreach ($entries as $entry) {
    if ($path === './') {
      $fullname = $entry;
    }
    else {
      $fullname = $path . $entry;
    }
    if ($entry != '.' && $entry != '..' && is_dir($fullname)) {
      find_files($fullname, $pattern, $callback);
    }
    else {
      if (is_file($fullname) && preg_match($pattern, $entry)) {
        call_user_func($callback, $fullname);
      }
    }
  }

}


$old_eventid = 0;
$seq = 0;
function my_handler($fname) {
  global $old_eventid;
  global $seq;
  //echo $fname . "\n";

  $b = basename($fname);
  $t = explode('_', $b);
  $eventid = $t[0];
  $fileid_a = explode('.', $t[1]);
  $fileid = $fileid_a[0];
  $fsize = filesize($fname);

  if ($old_eventid != $eventid) {
    $old_eventid = $eventid;
    $seq = 0;
  }

  if ($fsize > 10000) { //skip small thumbnail images.
    //echo "$eventid, $seq, $fname\n";
    $fn = "/tmp/ephotos.csv";
    $fp = fopen($fn, "a");
    fprintf($fp, "%d,%d,%d,event_photos/%s,public://event_photos/%s,%d\n", $fileid, $eventid, $seq, $fname, $fname, $fsize);
    fclose($fp);
    $seq += 1;
  }
}

chdir('/Users/crc/v3/sites/default/files/event_photos');
find_files('.', '/jpg$/', 'my_handler');
