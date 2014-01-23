<?php
require("include.php");
require("open_v3.php");

$dir="../../files"; chdir($dir);

$q = "delete FROM file_managed WHERE filename like '%staff%'";
db_query($q) or die(db_error());

$q = "delete FROM file_usage WHERE type = 'user'";
db_query($q) or die(db_error());
//die("images removed");

$files = glob("staff/*.jpg");
$old_userid = 0;
foreach ($files as $fname) {
//echo "$fname\n";

	$userid = basename($fname, ".jpg");
	$fsize = filesize($fname);


	$q = " INSERT INTO file_managed
		SET
			uid = " . $userid . ",
			filename = '" .$fname. "',
			uri = 'public://" .$fname."',
			filemime = 'image/jpeg',
			filesize = " .$fsize.",
			status = 1,
			timestamp = now();";

	echo $q;
	echo "\n";
	db_query($q) or die(db_error());
	$fileid = mysql_insert_id();

	$q = "INSERT INTO file_usage 
		SET
		fid = " . $fileid . ",
		module = 'user',
		type = 'user',
		id = " . $userid . ",
		count = 1;";
echo $q;

	db_query($q) or die(db_error());

	$q = "UPDATE users 
		SET
		 picture = " . $fileid . "
		 where uid = ". $userid;

	db_query($q) or die(db_error());

echo "$userid, $fname\n";
}
