<?php

$load_files = array (
				'chapter_load.php', 
				'user_load.php', 
				'site_load.php', 
				'org_load.php', 
				'event_load.php', 
				'org_users_load.php', 
				'staff_load.php' 
				'rsvp_load.php' 
// 'roles_dump.php'
);

echo "LOAD\n";
foreach ($load_files as $f) {
	echo "$f\n";
	exec ("/usr/bin/php ".$f);
}
