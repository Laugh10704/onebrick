<?php 

class pdf_output {

	public $pretty_name;
	public $anchor_name;

	private $my_parent;
	private $form;

	// class constructor
	function pdf_output(&$in_my_parent) {
		_error_debug(array('this' => $this, 'args' => func_get_args()), "SUBMODULE:".get_class()."()");

		$this->submodule_name = "";
		$this->submodule_description = "";
		$this->anchor_name = strtolower(str_replace(' ', '_', @$this->submodule_name));
		$this->my_parent = $in_my_parent;
	}

	################################################
	#
	#	LOAD
	#
	##################################################

	function load() {
		_error_debug(array('this' => $this, 'args' => func_get_args()), "_".get_class().":".__FUNCTION__."()");

		if(isset($_GET['eventid']) && is_numeric($_GET['eventid']) && isset($_GET['shiftid']) && is_numeric($_GET['shiftid'])) { 
			$this->eventid=$_GET['eventid'];
			$this->shiftid=$_GET['shiftid'];
			$this->_get_roster();
		} else { 
		
		}
	}

	################################################
	#
	#	LOAD SUBMISSION
	#
	##################################################

	function load_submission() {
		_error_debug(array('this' => $this, 'args' => func_get_args()), "_".get_class().":".__FUNCTION__."()");

		foreach($_POST as $key => $val) {
			$this->form[$key] = $val;
		}
	}

	################################################
	#
	#	VALIDATE
	#
	##################################################

	function validate(&$error_messages) {
		_error_debug(array('this' => $this, 'args' => func_get_args()), "_".get_class().":".__FUNCTION__."()");

	}

	################################################
	#
	#	SAVE
	#
	##################################################

	function save(&$log_messages) {
		_error_debug(array('this' => $this, 'args' => func_get_args()), "_".get_class().":".__FUNCTION__."()");

	}

	################################################
	#
	#	FORM
	#
	##################################################

	function form($submodnum='') {
		_error_debug(array('this' => $this, 'args' => func_get_args()), "_".get_class().":".__FUNCTION__."()");

		$merge_array=array();
		$merge_array['company']='Company Name';
		$merge_array['contact']='My Name';
		$merge_array['orderid']='1000';
		$merge_array['phone']='Phone';

		$pdf_attachment=generate_pdf($merge_array, $this->roster);

		return($pdf_attachment);

	}

################################################
#
#
#
##################################################

function _get_roster() { 
	_error_debug(get_defined_vars(), "_".get_class().":".__FUNCTION__."");

	$q = "
	select distinct 
		`event`.name as 'eventname', 
		DATE_FORMAT(`event`.date,'%m/%d/%Y') as 'eventdate', 
		DATE_FORMAT(`event`.time_start,'%l:%i %p') as 'eventstart', 
		DATE_FORMAT(`event`.time_end,'%l:%i %p') as 'eventend', 
		`shift_user`.userid,
		`shift_user`.shiftid,
		`shift_user`.roleid,
		`user`.firstname,
		`user`.lastname,
		`user`.email,
		DATE_FORMAT(`shift_user`.datecreated,'%m/%d/%Y') as date, 
		sum(`su2`.attended) as 'previous',
                `location`.address1,
                `location`.address2,
                `location`.city,
                `organization_contact`.firstname as `contact_firstname`,
                `organization_contact`.lastname as `contact_lastname`,
                `organization_contact`.email1 as `contact_email`,
                `organization_contact`.workphone as `contact_phone`

	from 
		`shift` 
		join `shift_user` on `shift_user`.shiftid=`shift`.shiftid and `shift_user`.active=1 
		join `user` on `user`.userid=`shift_user`.userid and `user`.active=1 
		join `shift_user` su2 on `su2`.userid=`user`.userid 
		join `event` on `event`.eventid=`shift`.eventid 
                left join `location` on `location`.locationid=`event`.locationid
                left join `organization_contact` on `organization_contact`.organizationid=`event`.organizationid and `organization_contact`.active=1
	where 
		shift.shiftid=".($this->shiftid)." 
	group by
		shift_user.userid, 
		`shift_user`.roleid
	order by
		firstname,
		roleid desc,
		shiftid,
		lastname
	";
	$res = database_query($q,"Getting users","system");
	$userids = "";
	$this->roster = array();
	while($row = database_fetch_row($res)) {
		$this->roster[]=$row;
	}
	return(true);
}


################################################
#
#	EOF
#
##################################################
}

function generate_waiver($pdf) {
  $pdf->Ln(10);
  $pdf->Cell(690,160,'',1,0,'L',0);
  $pdf->Ln(1);
  $pdf->Cell(150);
  $pdf->SetFont('Arial', 'B',10);
  $pdf->Cell(370,20,'Waiver, Assumption of Risk and Release',0,1,'C',0);
  $pdf->Ln(1);
  $pdf->SetFont('Arial', '',6);
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

function generate_page_top($pdf, $roster, $page) {
  $num_blanks = 0;

  $pdf->Cell(485);
  $pdf->SetFont('Arial', 'B',8);
  $pdf->Cell(200,12,''.strip_tags($roster[0]['eventname']),0,1,'R',0);
  
  $pdf->Cell(485);
  $pdf->SetFont('Arial', 'B',8);
  $pdf->Cell(200,12,''.trim($roster[0]['eventdate']).', '.trim($roster[0]['eventstart']).' - '.trim($roster[0]['eventend']).'',0,1,'R',0);
  
  $contact_lastname = trim($roster[0]['contact_lastname']);
  if ($contact_lastname != '') {
    $contact_lastname = ' '.$contact_lastname;
  }
  $contact_phone = trim($roster[0]['contact_phone']);
  if ($contact_phone != '') {
    $contact_phone = ', '.$contact_phone;
  }
  $contact_email = trim($roster[0]['contact_email']);
  if ($contact_email != '') {
    $contact_email = ', '.$contact_email;
  }
  $contact_info = trim(trim($roster[0]['contact_firstname']).($contact_lastname).($contact_phone).($contact_email));
  if ($contact_info != '') {
    $pdf->Cell(485);
    $pdf->SetFont('Arial', 'B',8);
    $pdf->Cell(200,12,$contact_info,0,1,'R',0);
  } else {
    $num_blanks++;
  }

  $address2 = trim($roster[0]['address2']);
  if ($address2 != '') {
    $address2 = ', '.$address2;
  }
  $city = trim($roster[0]['city']);
  if ($city != '') {
    $city = ', '.$city;
  }
  $address_info = trim(trim($roster[0]['address1']).($address2).($city));
  if ($address_info != '') {
    $pdf->Cell(485);
    $pdf->SetFont('Arial', 'B',8);
    $pdf->Cell(200,12,$address_info,0,1,'R',0);
  } else {
    $num_blanks++;
  }

  $pdf->Cell(485);
  $pdf->SetFont('Arial', 'B',8);
  $pdf->Cell(200,12,'Page #'.$page,0,1,'R',0);
  
  for ($i = 0; $i < $num_blanks; $i++) {
    $pdf->Cell(485);
    $pdf->SetFont('Arial', 'B',8);
    $pdf->Cell(200,12,'',0,1,'R',0);
  }

  $pdf->Image('images/onebrick_72.png',45,20,99,62);
  
  $pdf->Ln(7);
}

function generate_pdf($merge_array, $roster) { 
	$pdf = new fpdf('L', 'pt', 'Letter');

	$row_height='20';
	$printed_width = 125;
	$signed_width=200;
	$cell_border = 'B';

	$pdf->SetMargins(50, 20, 50,10); // left, top, right
	$pdf->SetAutoPageBreak(true, 50); // this is the bottom margin
	$pdf->SetDisplayMode('fullwidth', 'continuous');

	$max_columns = 2;
	$max_rows = 12;

	$num_spaces = sizeof($roster) * 1.2; // allocate space for 20% walk-ins
	$num_volunteers_per_page = $max_columns * $max_rows;
	$num_pages = intval(ceil($num_spaces / $num_volunteers_per_page));

	$i = 0;
	foreach($roster as $volunteer) {
	  $all_volunteers[$i] = $volunteer;
	  $i++;
	}

	$num_newbies = 0;
	for ($page = 0; $page < $num_pages; $page++) {
	  $pdf->AddPage();

	  generate_page_top($pdf, $roster, $page+1);

	  $pdf->SetFont('Arial', 'B',10);
	  $pdf->Write(11, 'BY SIGNING BELOW, YOU AGREE TO THE WAIVER, ASSUMPTION OF RISK AND RELEASE AT THE BOTTOM OF THE PAGE');
	  $pdf->Ln(15);

	  $pdf->SetFillColor(224);
	  $pdf->SetFont('Arial', 'B',8);

	  // header
	  $pdf->Cell($printed_width,$row_height,'Name',$cell_border,0,'L',1);
		$pdf->Cell(8,$row_height,'',0,0,'L',0);
	  $pdf->Cell($signed_width,$row_height,'Signature',$cell_border,0,'L',1);
		$pdf->Cell(20,$row_height,'',0,0,'L',0);
	  $pdf->Cell($printed_width,$row_height,'Name',$cell_border,0,'L',1);
		$pdf->Cell(8,$row_height,'',0,0,'L',0);
	  $pdf->Cell($signed_width,$row_height,'Signature',$cell_border,1,'L',1);

	  /////////////////////////////////////////////

	  $pdf->SetFillColor(224);
	  $pdf->SetFont('Arial', 'B',12);

	  // print names and blanks
	  for ($row = 0; $row < $max_rows; $row++) {
	    for ($col = 0; $col < $max_columns; $col++) {
	      $volunteer_index = ($page * 24) + ($col * 12) + ($row);
	      
	      // space should contain volunteer info
	      if ($volunteer_index < sizeof($roster)) {
		$volunteer = $all_volunteers[$volunteer_index];
		
		if($volunteer['previous']==0) { 
		  $newbie="(*)";
		  $num_newbies++;
		} else { 
		  $newbie="";
		}
	    
		$pdf->Cell($printed_width,$row_height,''.($volunteer['firstname'].' '.$volunteer['lastname']).' '.($newbie).'',$cell_border,0,'L',0);
		$pdf->Cell(8,$row_height,'',0,0,'L',0);
	      } else {
		$pdf->Cell($printed_width,$row_height,'',$cell_border,0,'L',0);
		$pdf->Cell(8,$row_height,'',0,0,'L',0);
	      }
	      
	      if($col == 0) { 
		$pdf->Cell($signed_width,$row_height,'',$cell_border,0,'L',0);
		$pdf->Cell(20,$row_height,'',0,0,'L',0);
	      } else { 
		$pdf->Cell($signed_width,$row_height,'',$cell_border,1,'L',0);
	      }
	    }
	  }

	  generate_waiver($pdf);

	  $pdf->SetFont('Arial', 'B',10);
	  $pdf->Cell(300,$row_height,'(*) This is your first time with One Brick.  Please complete the Volunteer Information page.',0,1,'L',0);
	}
	  
	// generate pages for newbies
	$num_newbies = $num_newbies * 1.05 + 1; // compensate for possible walk-ins

	$num_lines_per_page = 10;
	$num_newbie_pages = intval(ceil($num_newbies / $num_lines_per_page));

	$newbie_row_height = 40;
	$newbie_name_width = 90;
	$newbie_address_width = 140;
	$newbie_email_width = 100;
	$newbie_phone_width = 57;
	$newbie_employer_width = 60;
	$newbie_gender_width = 35;
	$newbie_dob_width = 43;
	$newbie_how_hear_width = 140;
	$newbie_spacer = 3;

	for ($newbie_page = 0; $newbie_page < $num_newbie_pages; $newbie_page++, $page++) {
	  $pdf->AddPage();
                          
	  generate_page_top($pdf, $roster, $page+1);

	  $pdf->SetFont('Arial', 'B',10);
	  $pdf->Write(11, 'THIS PAGE MUST BE COMPLETED BY ALL FIRST-TIME VOLUNTEERS');
	  $pdf->Ln(15);

	  $pdf->SetFillColor(224);
	  $pdf->SetFont('Arial', 'B',8);

	  $pdf->Cell($newbie_name_width,$row_height,'Name',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_address_width,$row_height,'Address',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);	    
	  $pdf->Cell($newbie_email_width,$row_height,'Email',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_phone_width,$row_height,'Phone',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_employer_width,$row_height,'Employer',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_gender_width,$row_height,'Gender',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_dob_width,$row_height,'Birthdate',$cell_border,0,'L',1);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_how_hear_width,$row_height,'How did you hear about us?',$cell_border,1,'L',1);

	  // example line
	  $pdf->SetFillColor(224);
	  $pdf->SetFont('Arial', 'BI',10);

	  $pdf->Cell($newbie_name_width,$row_height,'Jane',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_address_width,$row_height,'1234 Main Street',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);	    
	  $pdf->Cell($newbie_email_width,$row_height,'janedoe@',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_phone_width,$row_height,'(123)',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_employer_width,$row_height,'One',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_gender_width,$row_height,'F',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_dob_width,$row_height,'01/01',0,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_how_hear_width,$row_height,'Friend',0,1,'L',0);

	  $pdf->Cell($newbie_name_width,$row_height,'Doe',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_address_width,$row_height,'My Town, 12345',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);	    
	  $pdf->Cell($newbie_email_width,$row_height,'gmail.com',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_phone_width,$row_height,'456-7890',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_employer_width,$row_height,'Brick',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_gender_width,$row_height,'',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_dob_width,$row_height,'1985',$cell_border,0,'L',0);
	    $pdf->Cell($newbie_spacer,$row_height,'',0,0,'L',0);
	  $pdf->Cell($newbie_how_hear_width,$row_height,'',$cell_border,1,'L',0);

	  for($i = 2; $i<=$num_lines_per_page; $i++) {
	    $pdf->Cell($newbie_name_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);
	    $pdf->Cell($newbie_address_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);	    
	    $pdf->Cell($newbie_email_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);
	    $pdf->Cell($newbie_phone_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);
	    $pdf->Cell($newbie_employer_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);
	    $pdf->Cell($newbie_gender_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);
	    $pdf->Cell($newbie_dob_width,$newbie_row_height,'',$cell_border,0,'L',0);
	      $pdf->Cell($newbie_spacer,$newbie_row_height,'',0,0,'L',0);
	    $pdf->Cell($newbie_how_hear_width,$newbie_row_height,'',$cell_border,1,'L',0);
	  }
	}

	return $pdf->Output('PDF_Roster.pdf', 'S');
}


?>
