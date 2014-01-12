<?php
if (preg_match('/^\/ical\//', $_SERVER['REQUEST_URI'])) {
    include 'node--event--ical.tpl.php';
    return;
}
?>
<script language="javascript">
  jQuery(document).ready(function(){
      var promoteButton = jQuery("ul.tabs").find("a:contains('Promote')");
      if (promoteButton) {
         promoteButton.colorbox({inline:true, href:"#promoteArea", title:"Promote", transition:"none", width:"600"});
      }

      /*
      var rosterButton = jQuery("ul.tabs").find("a:contains('Roster')");
      
      if (rosterButton) {
	rosterButton.attr("target", "_blank");
      }
      */
  });

</script>


<style>
table {
  border: 1px solid lightgray; 
}
td {
  padding: .4em; 
}
td.title {
  vertical-align: top; 
  font-weight: bold; 
  background-color:#EEE; 
  width: 10em;
}
</style>

<table valign="middle" id="Event Details"; width="97%" border="0" cellpadding="0" cellspacing="0"> 
  <tr>
    <td class='title'>Event:</td>
    <td><B>
		<?php
			print strip_tags($node->title);
			if (brick_event_is_cancelled($node)) {
				print " (CANCELLED)";
			}
		?>
	</B>
	</div>
    </td>

  </tr>
  <tr>
    <td class='title'>Location:</td>
    <td>
 	<?php
	if ($address = brick_site_address($node)) {
		print("$address <a title=\"Google Map\" ");
	  print ("href=\"http://maps.google.com/maps?q=");
	  print("$address\" target=\"_blank\">");
	  print("<img width=\"20\" src=\"/sites/default/files/images/google_maps.png\" /></a>");
	}
      ?>
    </td>
    </tr>
  <tr>
    <td class='title'>Date:</td>
    <td>
        <?php echo brick_event_from_to($node); ?>
	<a title="Add to Calendar" href="/ical/<?php echo $node->nid; ?>/event.ics"><img width="20" src="/sites/default/files/images/Download_Event.png" /></a>

    </td>
  <tr>
    <td class='title'>Staff:</td> 
    <td> <?php print(brick_format_managment_list($node, TRUE)); ?></td>
  </tr>
  </tr>
    <td class='title'>RSVP:</td>
    <td>
 <! @@@ How do we keep the button on the same line?  >
       <?php
        global $user;

	if (brick_event_is_cancelled($node)) {
	  print "This event has been cancelled";
	}
	else if(strtotime($node->field_event_date['und'][0]['value']) < time()) {
	  print("This event has already occurred");
        }
        else if (user_is_logged_in() && brick_get_rsvp_status($node, $user)) {
	  print("<form method='post' action='/a/unrsvp'>");
	  print("<input type='submit' class='form-submit' value='Un-RSVP'>");
	  printf("<input type='hidden' name='nid' value='%s'>", $node->nid);
	  printf("<input type='hidden' name='uid' value='%s'>", $user->uid);
	  print("</form>");
	}
        else if (($open_date = brick_event_open_date($node)) > time() ) {
          print("This event will open on ");
          print(date("M jS", $open_date));
        }
        else if (brick_event_full($node)) {
                print("This event is FULL. Space often opens up a few days before the event, please check back.");
        }
        else {
             ctools_include('ajax');
             ctools_include('modal');
             ctools_add_js('ajax-responder');
             ctools_modal_add_js();
        
        ?>
        <script language='javascript'>
            jQuery(document).ready(function(){
		setupPopupForm("#initialRSVPForm");

                jQuery('#rsvpButton').colorbox({inline:true, href:'#currentPopupForm', transition:'none', width:'400', title: 'RSVP for this event', opacity: '0.50'});
            })
        </script>
        
         <input type='button' class='form-submit' id='rsvpButton' value='RSVP Now!' onclick="javascript: resetPopupForm('#initialRSVPForm', 'RSVPFormWrapper')"/>
            <div id="initialRSVPForm" style='display:none'>
               <?php
                   $form = drupal_get_form('brick_rsvp_form', $node->nid);
                   print drupal_render($form);
               ?>
            </div>         

	<?php
	   }
        ?>
</td>

  <tr> 
  </tr>
  <tr>
    <td class='title'>Description:</td>
    <td><?php print($node->body['und'][0]['value']) ?>

    <?php
	if (!empty($node->field_event_otherinfo)) {
	  $other = $node->field_event_otherinfo['und'][0]['value'];
	  if(!empty($other)) {
	    print("<br><br>$other");
	  }
	}
	?>
    </td>
  </tr>

<?php // "What we'll be doing"
  if (!empty($node->field_tasks)) {
    $tasks = $node->field_tasks['und'][0]['value'];
    if (!empty($tasks)) {
?>
  <tr>
    <td class='title'>What we'll be doing:</td>
    <td><?php print($tasks) ?>
    </td>
  </tr>
<?php
    }
  }
?>

<?php // "What you should know"
  if (!empty($node->should_know)) {
    $should_know = $node->field_should_know['und'][0]['value'];
    if (!empty($should_know)) {
?>
  <tr>
    <td class='title'>What you should know:</td>
    <td><?php print($should_know) ?>
    </td>
  </tr>
<?php
    }
  }
?>

<?php // "Where to meet"
  if (!empty($node->field_where_meet)) {
    $where_meet = $node->field_where_meet['und'][0]['value'];
    if (!empty($where_meet)) {
?>
  <tr>
    <td class='title'>Where to meet:</td>
    <td><?php print($where_meet) ?>
    </td>
  </tr>
<?php
    }
  }
?>

  <tr>
    <td class='title'>Organization: </td>
    <td> <?php
	 if(isset($node->field_event_organization)) {
	   $organization = node_load($node->field_event_organization['und']['0']['nid']);

	   if(isset($organization->body)) {
             print($organization->body['und'][0]['value']);
	   }
	}
	?>
    </td>
  </tr>

<?php // "Location Note"
  if(isset($node->field_event_site)) {
    $site = node_load($node->field_event_site['und']['0']['nid']);
    if(isset($site->field_site_please_note)) {
      $please_note = $site->field_site_please_note['und'][0]['value'];
      if (!empty($please_note)) {
?>
  <tr>
  <td class='title'>Location Note:</td>
  <td>  <?php print($please_note); ?>
  </td>
  </tr>
<?php
      }
    }
  }
?>

</table> 

<?php
include("promote.php");
?>
