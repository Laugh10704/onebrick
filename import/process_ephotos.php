<?php
require("include.php");
/**
 * find files matching a pattern
 * using PHP "glob" function and recursion
 *
 * @return array containing all pattern-matched files
 *
 * @param string $dir - directory to start with
 * @param string $pattern - pattern to glob for
 */
function find($dir, $pattern) {
  // escape any character in a string that might be used to trick
  // a shell command into executing arbitrary commands
  $dir = escapeshellcmd("cd $dir");
  // get a list of all matching files in the current directory
  $files = glob("$dir/$pattern");

  // find a list of all directories in the current directory
  // directories beginning with a dot are also included
  if ($dir == '.') { // Don't include leading ./
    $glob_string = "{.[^.]*,*}";
  }
  else {
    $glob_string = "$dir/{.[^.]*,*}";
  }

  foreach (glob($glob_string, GLOB_BRACE | GLOB_ONLYDIR) as $sub_dir) {
    $arr = find($sub_dir, $pattern); // resursive call
    $files = array_merge($files, $arr); // merge array with files from subdirectory
  }
  // return all found files
  return $files;
}

$dir = "/Users/crc/v3/sites/default/files/event_photos";
chdir($dir);

$files = find(".", "*.jpg");

$curtime = time();

$fn = "/tmp/ephotos.csv";
$fp = fopen($fn, "w");

$old_eventid = 0;
foreach ($files as $fname) {
  $b = basename($fname);
  $t = explode('_', $b);
  $eventid = $t[0];
  $fileid_a = explode('.', $t[1]);
  $fileid = $fileid_a[0];
  $fsize = filesize($dir . "/" . $fname);

  if ($old_eventid != $eventid) {
    $old_eventid = $eventid;
    $seq = 0;
  }

  if ($fsize > 10000) { //skip small thumbnail images.
    //echo "$eventid, $seq, $fname\n";
    fprintf($fp, "%d,%d,%d,event_photos/%s,public://event_photos/%s,%d\n", $fileid, $eventid, $seq, $fname, $fname, $fsize);
    $seq += 1;
  }
  else {
    unlink($dir . "/" . $fname);
  }
}

print ("SEQ: $seq");

fclose($fp);
