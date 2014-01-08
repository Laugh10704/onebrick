<?php

// We try to keep everything liked together by using the table ids from the old system.
// Unfortunately most objects are stored as nodes. 
//We can't just put them into the node table with their old id because they will overlap. 
//So we add an offset so that each node has it's own space.

$userid_offset = 0;	// old was 62 to about 90,000, don't need to increment as users , doesn't create a node so can overlap with oter types

//*************
$chapid_offset = 100;	// old was 1i to 12, new is from 101-113
$lid_offset = 1000;	// old was 53 to about 2,200, new is from 1,053-3,200
$orgid_offset = 4000;	// old was 1 to about 1,400, new is from 4,001-5,400
$org_lid_offset = 6000; // unfortunately orgs have addresses in them and we need to merge  with site locations
$eventid_offset = 20000;	// old was 53 to about 9,000, new is from 20,053-33,000
// gap from  35,000 - 80,000 - can use for other things

$staff_offset = 85000; // there are less than 500 
$rsvp_offset = 100000; // Need to make sure that when we add user id and event id we get a unique value

$orgcontact_offset = 1000000; // there are less than 1500 // 1 Million
$static_offset = 1999000; 
$optin_offset = 2000000; // there are less than 1500 // 1 Million


function dmsg($s) {
				echo $s."\n";
}

function rm($f) {
				if(file_exists($f))
					return (unlink($f));
				return(true);
}

function ck($f) {
				if(!file_exists($f)) {
					echo "$f: does not exist\n";
					return (false);
				}
				$fp = fopen($f, "r" );
				$l=fgets($fp);
				$c=1;

				while(fgets($fp))
					$c++;

				fclose($fp);

				echo "$f($c lines):\t$l";
				return(true);
}


function db_query($query) {
		dmsg($query);
		return(mysql_query($query));
}

function db_error() {
		return(mysql_error());
}
