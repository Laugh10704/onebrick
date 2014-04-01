<?php
require("open_v3.php");

echo "Cleaning out the database...\n";
exec("/usr/bin/php " . "rm_all.php");
exec("/usr/bin/php " . "rm_users.php");
exec("/usr/bin/php " . "rm_ephotos.php");

echo "Creating a list of the event photos...\n";
exec("/usr/bin/php " . "process_ephotos.php");

//load the data
$load_files = array(
  'load_chapter.php',
  'load_ephotos.php',
  'load_event.php',
  'load_optin.php',
  'load_org.php',
  'load_org_users.php',
  'load_profile.php',
  'load_rsvp.php',
  'load_site.php',
  'load_staff.php',
  'load_staff_photos.php',
  'load_staff_pict.php',
  'load_staff_profiles.php',
  'load_user.php',
);

echo "Loading data...\n";
foreach ($load_files as $f) {
  echo "$f\n";
  exec("/usr/bin/php " . $f);
}
