<?php

include("fpdf.php");

function generate_waiver($pdf) {
  $pdf->Ln(10);
  $pdf->Cell(690, 160, '', 1, 0, 'L', 0);
  $pdf->Ln(1);
  $pdf->Cell(150);
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(370, 20, 'Waiver, Assumption of Risk and Release', 0, 1, 'C', 0);
  $pdf->Ln(1);
  $pdf->SetFont('Arial', '', 6);
  $pdf->Write(8, 'I, the abovesigned, wish to volunteer my services to various community service organizations and projects through you, One Brick, a California nonprofit corporation ("One Brick"). In consideration of your locating, arranging, coordinating and/or making available volunteer opportunities, I hereby agree and release you as follows:');
  $pdf->Ln();
  $pdf->Cell(10);
  $pdf->Write(8, '1.  I acknowledge and agree that the nature of the volunteer services which are typically performed by One Brick volunteers, and which may be performed by me as a One Brick volunteer, can pose serious risks of injury or death and may involve (a) strenuous physical activity (including without limitation work with heavy tools and materials), (b) contact with unidentified and unfamiliar persons, (c) travel to and from various unspecified locations, and (d) other potential risk of injury. Knowing the risks involved, nevertheless, I have voluntarily applied to participate as a volunteer and hereby agree to assume any and all risks of injury or death or damage to personal property and to release and hold harmless One Brick, its directors, officers, partners, agents, employees, successors, assigns, licensees, sponsors, donors, representatives, guests and affiliates who through negligence, carelessness or any other act or omission in connection with my participation as a One Brick volunteer might otherwise be liable to me.');
  $pdf->Ln();
  $pdf->Cell(10);
  $pdf->Write(8, '2.  I hereby release and forever discharge and hold harmless One Brick and its directors, officers, partners, agents, employees, successors, assigns, licensees, sponsors, donors, representatives, guests and affiliates from and covenants not to sue any of them for, any and all liability, claims and causes of action, whether known or unknown, arising out of, based upon or relating to my participation as an One Brick volunteer or in any One Brick related activity or project, even though such liability, claims or causes of action might arise out of negligence or carelessness on the part of One Brick, its directors, officers, partners, agents, employees, successors, assigns, licensees, sponsors, donors, representatives, guests or affiliates.');
  $pdf->Ln();
  $pdf->Cell(10);
  $pdf->Write(8, '3.  I further understand and agree that this waiver, release and assumption of risks is to be binding on my heirs and assigns. I am fully aware that One Brick carries no medical or other insurance for any volunteers. I also understand that One Brick does not assume any responsibility or obligation to provide financial assistance or other assistance, including, but not limited to, medical, health, or disability insurance, in the event of injury, illness, death, or property damage. AS A VOLUNTEER, I AM EXPECTED AND ENCOURAGED BY ONE BRICK TO MAINTAIN MEDICAL, HEALTH, AND ALL OTHER APPLICABLE INSURANCE COVERAGE FOR MY OWN BENEFIT.');
  $pdf->Ln();
  $pdf->Cell(10);
  $pdf->Write(8, '4.  I further irrevocably grant to One Brick, its assigns and successors, my consent and full right to: use my name, photograph, likeness, image, voice and biography in any and all media, publications, advertising, and publicity, in connection with my participation hereunder.');
  $pdf->Ln();
  $pdf->Cell(10);
  $pdf->Write(8, '5.  This release shall inure to the benefit of One Brick, as well as to the benefit of its successors, licensee, agents, employees, affiliates and assigns. ');
  $pdf->Ln(20);
}

function generate_page_top($pdf, $node, $event_contact, $page) {
  $num_blanks = 0;

  $pdf->Cell(485);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(200, 12, '' . strip_tags($node->title), 0, 1, 'R', 0);

  $pdf->Cell(485);
  $pdf->SetFont('Arial', 'B', 8);

  $from_tz = $node->field_event_date['und'][0]['timezone_db'];
  $to_tz = $node->field_event_date['und'][0]['timezone'];
  date_default_timezone_set($to_tz);
  $ts_from = brick_optin_fix_date($node->field_event_date['und'][0]['value'], $from_tz, $to_tz);
  $ts_to = brick_optin_fix_date($node->field_event_date['und'][0]['value2'], $from_tz, $to_tz);
  $pdf->Cell(200, 12, date('M j, Y, g:i A', $ts_from) . ' - ' . date('g:i A', $ts_to), 0, 1, 'R', 0);

  $contact_name = trim($event_contact['signature']);
  if ($contact_name != '') {
    $contact_name = ' ' . $contact_name;
  }
  $contact_phone = trim($event_contact['field_user_phone_value']);
  if ($contact_phone != '') {
    $contact_phone = ', ' . $contact_phone;
  }
  $contact_email = trim($event_contact['mail']);
  if ($contact_email != '') {
    $contact_email = ', ' . $contact_email;
  }
  $contact_info = trim(trim(($contact_name) . ($contact_phone) . ($contact_email)));
  if ($contact_info != '') {
    $pdf->Cell(485);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(200, 12, $contact_info, 0, 1, 'R', 0);
  }
  else {
    $num_blanks++;
  }

  if (isset($node->field_event_site)) {
    $site = node_load($node->field_event_site['und']['0']['nid']);
    if (isset($site->location)) {
      $address_info = location_address2singleline($site->location);
    }
  }

  if ($address_info != '') {
    $pdf->Cell(485);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(200, 12, $address_info, 0, 1, 'R', 0);
  }
  else {
    $num_blanks++;
  }

  $pdf->Cell(485);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(200, 12, 'Page #' . $page, 0, 1, 'R', 0);

  for ($i = 0; $i < $num_blanks; $i++) {
    $pdf->Cell(485);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(200, 12, '', 0, 1, 'R', 0);
  }

  $pdf->Image('sites/default/files/logo.gif', 45, 20, 99, 62);

  $pdf->Ln(7);
}


function get_roster($node) {
  // IDs of volunteers
  $q = "SELECT field_rsvp_person_uid
		FROM field_data_field_rsvp_person
		LEFT JOIN field_data_field_rsvp_event
		ON field_data_field_rsvp_event.entity_id = field_data_field_rsvp_person.entity_id
		WHERE field_rsvp_event_nid = $node->nid
	";

  $roster = db_query_temporary($q);

  // IDs of non-newbies
  $q = "SELECT distinct field_data_field_rsvp_person.field_rsvp_person_uid
		FROM $roster
		LEFT JOIN (field_data_field_rsvp_person, field_data_field_rsvp_attended)
		ON field_data_field_rsvp_person.field_rsvp_person_uid = $roster.field_rsvp_person_uid
		AND field_data_field_rsvp_attended.entity_id = field_data_field_rsvp_person.entity_id
		WHERE field_rsvp_attended_value = 1;
	";

  $r = db_query($q);

  // array of non-newbie IDs
  $non_newbies = array();
  while ($record = $r->fetchAssoc()) {
    $non_newbies[] = $record['field_rsvp_person_uid'];
  }


  $q = "SELECT $roster.field_rsvp_person_uid as id, users.signature
		FROM $roster
		LEFT JOIN users
		ON users.uid = $roster.field_rsvp_person_uid
		ORDER BY users.signature;
	";

  $r = db_query($q);

  $all_volunteers = array();
  while ($record = $r->fetchAssoc()) {
    if (in_array($record['id'], $non_newbies)) {
      $record['previous'] = 1;
    }
    else {
      $record['previous'] = 0;
    }
    $all_volunteers[] = $record;
  }

  $org = $node->field_event_organization['und'][0]['nid'];
  $q = "SELECT mail, signature, field_user_phone_value
		FROM field_data_field_org_contact_organization, field_data_field_user_phone, users
		WHERE field_org_contact_organization_nid = $org
		AND users.uid = field_data_field_org_contact_organization.entity_id
		AND users.uid = field_data_field_user_phone.entity_id
		order by users.uid DESC";

  $r = db_query($q);

  $event_contact = array();
  while ($record = $r->fetchAssoc()) {
    $event_contact[] = $record;
  }

  return array($all_volunteers, $event_contact[0]);
}

function get_org_info($id) {
  return $org;
}

function generate_pdf($nid) {
  $node = node_load($nid);
  $pdf = new fpdf('L', 'pt', 'Letter');

  list($all_volunteers, $event_contact) = get_roster($node);

  $row_height = '20';
  $printed_width = 125;
  $signed_width = 200;
  $cell_border = 'B';

  $pdf->SetMargins(50, 20, 50, 10); // left, top, right
  $pdf->SetAutoPageBreak(TRUE, 50); // this is the bottom margin
  $pdf->SetDisplayMode('fullwidth', 'continuous');

  $max_columns = 2;
  $max_rows = 12;

  $num_spaces = sizeof($all_volunteers) * 1.2; // allocate space for 20% walk-ins
  $num_volunteers_per_page = $max_columns * $max_rows;
  $num_pages = intval(ceil($num_spaces / $num_volunteers_per_page));
  // Want at least 1 page (even if no one signed up)
  $num_pages = max(1, $num_pages);

  $num_newbies = 0;
  for ($page = 0; $page < $num_pages; $page++) {
    $pdf->AddPage();

    generate_page_top($pdf, $node, $event_contact, $page + 1);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Write(11, 'BY SIGNING BELOW, YOU AGREE TO THE WAIVER, ASSUMPTION OF RISK AND RELEASE AT THE BOTTOM OF THE PAGE');
    $pdf->Ln(15);

    $pdf->SetFillColor(224);
    $pdf->SetFont('Arial', 'B', 8);

    // header
    $pdf->Cell($printed_width, $row_height, 'Name', $cell_border, 0, 'L', 1);
    $pdf->Cell(8, $row_height, '', 0, 0, 'L', 0);
    $pdf->Cell($signed_width, $row_height, 'Signature', $cell_border, 0, 'L', 1);
    $pdf->Cell(20, $row_height, '', 0, 0, 'L', 0);
    $pdf->Cell($printed_width, $row_height, 'Name', $cell_border, 0, 'L', 1);
    $pdf->Cell(8, $row_height, '', 0, 0, 'L', 0);
    $pdf->Cell($signed_width, $row_height, 'Signature', $cell_border, 1, 'L', 1);

    /////////////////////////////////////////////

    $pdf->SetFillColor(224);
    $pdf->SetFont('Arial', 'B', 12);

    // print names and blanks
    for ($row = 0; $row < $max_rows; $row++) {
      for ($col = 0; $col < $max_columns; $col++) {
        $volunteer_index = ($page * 24) + ($col * 12) + ($row);

        // space should contain volunteer info
        if ($volunteer_index < sizeof($all_volunteers)) {
          $volunteer = $all_volunteers[$volunteer_index];

          if ($volunteer['previous'] == 0) {
            $newbie = "(*)";
            $num_newbies++;
          }
          else {
            $newbie = "";
          }

          $pdf->Cell($printed_width, $row_height, '' . $volunteer['signature'] . ' ' . ($newbie) . '', $cell_border, 0, 'L', 0);
          $pdf->Cell(8, $row_height, '', 0, 0, 'L', 0);
        }
        else {
          $pdf->Cell($printed_width, $row_height, '', $cell_border, 0, 'L', 0);
          $pdf->Cell(8, $row_height, '', 0, 0, 'L', 0);
        }

        if ($col == 0) {
          $pdf->Cell($signed_width, $row_height, '', $cell_border, 0, 'L', 0);
          $pdf->Cell(20, $row_height, '', 0, 0, 'L', 0);
        }
        else {
          $pdf->Cell($signed_width, $row_height, '', $cell_border, 1, 'L', 0);
        }
      }
    }

    generate_waiver($pdf);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(300, $row_height, '(*) This is your first time with One Brick.  Welcome!', 0, 1, 'L', 0);
  }

  return $pdf->Output('PDF_Roster.pdf', 'D');
}

?>
