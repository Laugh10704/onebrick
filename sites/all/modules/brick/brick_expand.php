<?php
// Move these into a misc file one day
function brick_org_contact_list($orgid) {
  $q = "SELECT DISTINCT
            node.nid AS orgid, title AS organization,  users.signature AS name,
            field_user_phone_value AS phone, users.mail as email
          FROM node
          LEFT JOIN field_data_field_org_contact_organization  ON field_org_contact_organization_nid = node.nid
          LEFT JOIN field_data_field_org_contact_person ON field_data_field_org_contact_person.entity_id  =
            field_data_field_org_contact_organization.entity_id
          LEFT JOIN field_data_field_user_phone ON field_data_field_user_phone.entity_id =
            field_org_contact_person_uid
          LEFT JOIN users  ON users.uid = field_org_contact_person_uid
          WHERE type = 'organization' and title <> '' and users.signature <> '' and node.nid = " . $orgid . ";";
  return (db_query($q));
}

function brick_chapter_details($chapter_id) {
  $q = "SELECT DISTINCT
           node.nid AS chapter_id,   node.title as name,
           field_chapter_recruiting_email_value as email_recruiting,
           field_chapter_events_email_value as email_events, field_chapter_twitter_url_value as twitter,
           field_chapter_facebook_url_value as facebook, field_chapter_craigslist_stub_value as cl_stub,
           field_chapter_craigslist_code_value as cl_code
        FROM node
           LEFT JOIN field_data_field_chapter_recruiting_email on   field_data_field_chapter_recruiting_email.entity_id = node.nid
           LEFT JOIN field_data_field_chapter_events_email on       field_data_field_chapter_events_email.entity_id = node.nid
           LEFT JOIN  field_data_field_chapter_twitter_url on       field_data_field_chapter_twitter_url.entity_id = node.nid
           LEFT JOIN field_data_field_chapter_facebook_url on       field_data_field_chapter_facebook_url.entity_id = node.nid
           LEFT JOIN field_data_field_chapter_craigslist_stub on    field_data_field_chapter_craigslist_stub.entity_id = node.nid
           LEFT JOIN  field_data_field_chapter_craigslist_code on   field_data_field_chapter_craigslist_code.entity_id = node.nid
        WHERE field_chapter_craigslist_code_value  <> and node.nid = " . $chapter_id . ";";
  return (db_query($q));
}

function brick_site_title($eid) {
  $event_node = node_load($eid);
  $title = "";
  if (isset($event_node->field_event_site)) {
    $site = node_load($event_node->field_event_site['und']['0']['nid']);
    $title = $site->title;
  }
  return ($title);
}

function brick_site_address($eid) {
  $event_node = node_load($eid);
  $address = "";
  if (isset($event_node->field_event_site)) {
    $site = node_load($event_node->field_event_site['und']['0']['nid']);
    if (isset($site->location)) {
      $site->location['country'] = ""; //remove ",us" from the address
      $address = location_address2singleline($site->location);
    }
  }
  return ($address);
}

function brick_event_from_to($eid) {
  $event_node = node_load($eid);

  // The string value is represented in one timezone (probably UTC),
  // and we need to convert it to the target timezone instead.
  if (!function_exists('get_date')) {
    function get_date($str, $from_tz, $to_tz) {
      $obj = new DateTime($str, new DateTimeZone($from_tz));
      $obj->setTimezone(new DateTimeZone($to_tz));
      return $obj;
    }
  }
  $from_tz = $event_node->field_event_date['und'][0]['timezone_db'];
  $to_tz = $event_node->field_event_date['und'][0]['timezone'];
  $ts_from = get_date($event_node->field_event_date['und'][0]['value'], $from_tz, $to_tz);
  $ts_to = get_date($event_node->field_event_date['und'][0]['value2'], $from_tz, $to_tz);

  // Put end date if the starting date does not match the end date, otherwise just
  // list start date and times.
  $from_date = $ts_from->format('M jS Y');
  $to_date = $ts_to->format('M jS Y');
  if ($from_date == $to_date) {
    $end = $ts_to->format('g:i a');
  }
  else {
    $end = $ts_to->format('M jS Y, g:i a');
  }

  $start = $ts_from->format('M jS Y, g:i a');
  return ("$start - $end");
}

function brick_num_orgs() {
  $q = "SELECT count(*) FROM field_data_field_organization_chapter;";
  $r = db_query($q);
  return $r->fetchField();
}

function brick_num_hours($p = "ALL") {
  $q = "  SELECT truncate(sum(timestampdiff(minute,field_event_date_value, field_event_date_value2)/60.0), 0)
                FROM field_data_field_rsvp_event
                LEFT JOIN (field_data_field_rsvp_attended, field_data_field_event_type, field_data_field_event_date)
                ON (field_data_field_rsvp_event.entity_id = field_data_field_rsvp_attended.entity_id
                    AND field_rsvp_event_nid = field_data_field_event_date.entity_id
                    AND field_rsvp_event_nid = field_data_field_event_type.entity_id)
                WHERE field_rsvp_attended_value = 1 AND field_event_type_value = 'Volunteer' 
  ";

  if ($p === "YTD") {
    $q .= " AND year(field_event_date_value) = year(curdate())";
  }
  $q .= ";";

  $r = db_query($q);
  return $r->fetchField();
}


// count people who have volunteered at least once
function brick_num_vols() {
  $q = "  SELECT COUNT(DISTINCT field_rsvp_person_uid)
    FROM field_data_field_rsvp_attended
    LEFT JOIN (field_data_field_rsvp_event,
               field_data_field_event_type,
               field_data_field_rsvp_person)
    ON (field_data_field_rsvp_person.entity_id = field_data_field_rsvp_attended.entity_id
        AND field_data_field_rsvp_person.entity_id = field_data_field_rsvp_event.entity_id
        AND field_rsvp_event_nid = field_data_field_event_type.entity_id)
    WHERE field_rsvp_attended_value = 1 AND field_event_type_value = 'Volunteer';
  ";
  $r = db_query($q);
  return $r->fetchField();
}

function brick_num_events($p = "Volunteer") {
  $q = "  SELECT COUNT(*)
    FROM field_data_field_event_type
    LEFT JOIN field_data_field_event_date
    ON field_data_field_event_type.entity_id = field_data_field_event_date.entity_id
    WHERE field_event_type_value = '" . $p . "' AND field_event_date_value2 < CURDATE();
  ";

  $r = db_query($q);
  return $r->fetchField();
}


// Returns an array of chapter names
function brick_chapter_list() {
  $q = "
SELECT title FROM  node 
  LEFT JOIN field_revision_field_chapter_hide_from_menu
     ON field_revision_field_chapter_hide_from_menu.entity_id = nid
  WHERE TYPE = 'chapter'
  AND field_revision_field_chapter_hide_from_menu.field_chapter_hide_from_menu_value =  '0';";

  $chapter_list = array();

  $r = db_query($q);
  foreach ($r as $rec) {
    array_push($chapter_list, $rec->title);
  }
  return ($chapter_list);
}

function brick_chapter_info($chapter_id) {
}


//debug function
function crc_log_to_file($text) {
  $f = fopen('/tmp/crc_log.txt', 'a');
  fwrite($f, date('Ymd H:i:s - ') . $text . "\n");
  fclose($f);
}

// helper functions
function brick_round_up10($n, $d) {
  return $n - $n % pow(10, $d);
}

function brick_array2commalist($list) {
  $comma_list = "";
  $list_length = count($list);
  foreach ($list as $elem) {
    if ($list_length-- === 1) {
      $comma_list .= "and " . $elem;
    }
    else {
      $comma_list .= $elem . ", ";
    }
  }
  return ($comma_list);
}

/*
 * brick_merge_variables(string s) scans a string and replaces variables 
 */
// $s - String we are expanding variables into (the template)
// $event - node for the event we are looking at
// $to - user node for the person we intend to email
// $rsvp -  node for the person who just rsvp'ed
//
function brick_expand($s, $eid = NULL, $to = NULL, $rsvp = NULL) {
  $c = "#brick_expand"; //This is the stub we will look for in the file

  // Performance optimization ... if no variables simply return
  if (strpos($s, $c) === FALSE) {
    return ($s);
  }

  if ($rsvp && strpos($s, $c . "(rsvp:")) {
    $s = str_replace($c . "(rsvp:name)", "rsvp:name NOT IMPLEMENTED", $s);
    $s = str_replace($c . "(rsvp:note)", "rsvp:name NOT IMPLEMENTED", $s);
  }

  if (strpos($s, $c . "(stats:")) {
    $s = str_replace($c . "(stats:num_orgs)",
      number_format(brick_round_up10(brick_num_orgs(), 2)), $s);
    $s = str_replace($c . "(stats:num_volunteers)",
      number_format(brick_round_up10(brick_num_vols(), 1)), $s);
    $s = str_replace($c . "(stats:hours)",
      number_format(brick_round_up10(brick_num_hours("ALL"), 3)), $s);
    $s = str_replace($c . "(stats:ytd_hours)",
      number_format(brick_round_up10(brick_num_hours("YTD"), 2)), $s);
    $s = str_replace($c . "(stats:num_events)",
      number_format(brick_round_up10(brick_num_events(), 1)), $s);
    $s = str_replace($c . "(stats:num_chapters)", count(brick_chapter_list()), $s);
    $s = str_replace($c . "(stats:chapter_list)",
      brick_array2commalist(brick_chapter_list()), $s);
  }

  // expand information about the event passed as param event
  if ($eid && strpos($s, $c . "(event:")) {
    global $base_url;
    $event = node_load($eid);
    $url = $base_url . "/node/" . $eid;
    $s = str_replace($c . "(event:rsvp_count)", brick_rsvp_count($eid), $s);
    $s = str_replace($c . "(event:rsvp_capacity)",
      $event->field_event_max_rsvp_capacity['und'][0]['value'], $s);
    $s = str_replace($c . "(event:date)", brick_event_from_to($eid), $s);
    $s = str_replace($c . "(event:name)", strip_tags($event->title), $s);
    $s = str_replace($c . "(event:location)", brick_site_address($eid), $s);

    $s = str_replace($c . "(event:staff)", brick_format_managment_list($eid, FALSE), $s);
    $s = str_replace($c . "(event:page)", '<a href="' . $url . '">' . $url . "</a>", $s);

    $contacts = brick_org_contact_list($event->field_event_organization['und']['0']['nid']);

    $org_contacts = "";
    while ($row = $contacts->fetchAssoc()) {
      $org_name = $row['organization'];
      if ($org_contacts != "") {
        $org_contacts .= ", ";
      }
      $org_contacts .= $row['name'];
      if ($row['email']) {
        $org_contacts .= " - " . $row['email'];
      }
      if ($row['phone']) {
        if ($row['email']) {
          $org_contacts .= "/" . $row['phone'];
        }
        else {
          $org_contacts .= " - " . $row['phone'];
        }
      }
    }
    if (isset($org_name)) {
      $s = str_replace($c . "(event:org_name)", $org_name, $s);
      $s = str_replace($c . "(event:org_contacts)", $org_contacts, $s);
    }
  }

  // expand information about the current user
  if (strpos($s, $c . "(user:")) {
    global $user;
    $account = user_load($user->uid);
    $name = brick_get_user_name($account);
    $email = $user && property_exists($user, "mail") ? $user->mail : "";

    $s = str_replace($c . "(user:name)", $name, $s);
    $s = str_replace($c . "(user:email)", $email, $s);
  }

  // expand information about the user we are emailing
  if ($to && strpos($s, $c . "(email:")) {
    $s = str_replace($c . "(email:name)", $to->signature, $s);
    $names = explode(" ", $to->signature);
    $s = str_replace($c . "(email:fname)", $names[0], $s);
    if (count($names) > 1) {
      $s = str_replace($c . "(email:sname)", $names[1], $s);
    }
  }

  // expand information about the current chapter
  if (strpos($s, $c . "(chapter:")) {

    $chapter_info = brick_chapter_details(brick_current_chapter())->fetchAssoc();
//crc_log_to_file($chapter_node->title);

    $s = str_replace($c . "(chapter:name)", $chapter_info['name'], $s);
    $s = str_replace($c . "(chapter:email_recruiting)", $chapter_info['email_recruiting'] . "@onebrick.org", $s);
    $s = str_replace($c . "(chapter:email_events)", $chapter_info['email_events'] . "@onebrick.org", $s);
  }


  return ($s);
}


