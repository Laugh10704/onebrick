<?php
// $Id: brick_data.php,v 1.4 2012/02/04 01:40:39 crc Exp $

function brick_1b_email($uid) {
  $q = "
  SELECT field_staff_email_value FROM field_revision_field_staff_person
  JOIN field_revision_field_staff_email
  on field_revision_field_staff_person.entity_id=field_revision_field_staff_email.entity_id
  WHERE field_staff_person_uid=$uid;";

  $c = db_query($q)->fetchField();
  return($c);
}

function brick_data_chapter_list() {
	return("here, there and everywhere");
}

function brick_data_hours() {
	return("300,000");
}

function brick_data_chapter_count_num() {
	return(13);
}

function brick_data_chapter_count_text() {
	return("13");
}

function brick_data_org_count() {
	//round up
	return("1,200");
}

function brick_data_chapter_list() {
	return("here, there and everywhere");
}

function brick_data_chapter_list() {
	return("here, there and everywhere");
}

function brick_data_chapter_list() {
	return("here, there and everywhere");
}

?>
