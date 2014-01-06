<div style="display:none">
<div id="promoteArea">

<?php
  $chapter = $_SESSION['CHAPTER'];

  // search for craigslist code
  $q = "SELECT field_chapter_craigslist_stub_value FROM field_data_field_chapter_craigslist_stub WHERE entity_id=$chapter";
  $region = db_query($q)->fetchField();

  $url = "http://v3.onebrick.org";
  $node_url = "$url/node/$node->nid";

  $q = "SELECT field_chapter_craigslist_code_value FROM field_data_field_chapter_craigslist_code WHERE entity_id=$chapter";
  $shortregion = db_query($q)->fetchField();
?>

<h1>Craigslist Posting</h1>

For each of the following sections: (If you use a Mac, replace 'Ctrl' with the 'Command' key)<br/>
<ol>
<li>Click in the text box</li>
<li>Select all the text (press Ctrl-A)</li>
<li>Copy the text (press Ctrl-C)</li>
<li>Click the heading link to go to Craigslist</li>
<li>Navigate to the ad form</li>
<li>Click in the "Posting Description" text box and paste the text (press Ctrl-V)</li>
</ol>
<hr/>

<?php
  // get node numbers (queries are separate, because "order" matters)
  $q = "SELECT source FROM url_alias WHERE alias='promotion-header1'";
  $source = db_query($q)->fetchField();
  $promotion_header1 = strtok($source, "node/");
  $q = "SELECT source FROM url_alias WHERE alias='promotion-header2'";
  $source = db_query($q)->fetchField();
  $promotion_header2 = strtok($source, "node/");
  $q = "SELECT source FROM url_alias WHERE alias='promotion-header3'";
  $source = db_query($q)->fetchField();
  $promotion_header3 = strtok($source, "node/");
  $q = "SELECT source FROM url_alias WHERE alias='promotion-footer'";
  $source = db_query($q)->fetchField();
  $promotion_footer = strtok($source, "node/");

  // drupal node ids for headers and footers
  $headers = array($promotion_header1, $promotion_header2, $promotion_header3);
  $footers = array($promotion_footer, $promotion_footer, $promotion_footer);
  $sections = array("Volunteers", "Activity Partners", "Events");
  $top_section_code = array("C", "C", "E");
  $section_code = array("vol", "act", "eve");
  $count = count($headers);

  for ($i = 0; $i < $count; $i++) {
    $url_suffix = $top_section_code[$i] . "/" . $section_code[$i];
    print("<a href='https://post.craigslist.org/c/$shortregion/$url_suffix' target='_blank'>Click to place ad in $sections[$i] section</a>");
?>

<textarea readonly style="height:100px">
<?php
  // get promotion header
  $q = "SELECT body_value FROM field_data_body WHERE entity_id=$headers[$i]";
  $code = db_query($q)->fetchField();
  $code = str_replace(array('<strong>', '</strong>', '<em>', '</em>', 'www.onebrick.org'), array('<b>', '</b>', '<i>', '</i>', "v3.onebrick.org"), $code);
  print(htmlentities($code));

  // date
  $ts_from = strtotime($node->field_event_date['und'][0]['value']);
  $when = date('l, F d, Y, g:i A', $ts_from);
  // location
  if (isset($node->field_event_site)) {
    $site = node_load($node->field_event_site['und']['0']['nid']);
    if (isset($site->location)) {
      $location = location_address2singleline($site->location);
    }
  }

  $text = "<p><b>What:</b> <a href='$node_url'>$node->title</a><br> <b>When:</b> $when<br> <b>Where:</b> $location<br> <b>RSVP:</b> <a href='$node_url'>Click Here</a><br><br></p>";
  print(htmlentities($text));

  // event info
  $event_info = $node->body['und'][0]['value'];
  if (!empty($node->field_event_otherinfo)) {
    $event_other_info = $node->field_event_otherinfo['und'][0]['value'];
    $text = "<p><p>$event_info</p><p><b>Other Event Info:</b></p><p>$event_other_info</p>";
    print(htmlentities($text));
  }

  $text = "<p>View <a href='$node_url'>more information</a> about this event at:<br><a href='$node_url'>$node_url</a></p>";
  print(htmlentities($text));

  $text = "<p>Please <a href='$node_url'><b>RSVP here</b></a></p><br/><p>Check out our <a href='$url/calendar/'>event calendar</b></a> for other upcoming volunteer opportunities.</p>";
  print(htmlentities($text));

  // get promotion footer
  $q = "SELECT body_value FROM field_data_body WHERE entity_id=$footers[$i]";
  $code = db_query($q)->fetchField();
  $code = str_replace(array('<strong>', '</strong>', '<em>', '</em>', 'www.onebrick.org'), array('<b>', '</b>', '<i>', '</i>', "v3.onebrick.org"), $code);
  print(htmlentities($code));
?>

</textarea>
<p></p>

<?php
  } // end for loop of text areas
?>

</div>
</div>
