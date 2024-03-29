<?php

/**
 * @file
 *
 * A Views' field handler for editing a node title.
 *
 */
class brick_field_handler_optin extends views_handler_field {
  function construct() {
    parent::construct();
    $this->additional_fields = array(
      'nid' => 'nid'
    );
  }

  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  function render($values) {

    // Render a Views form item placeholder.
    // This causes Views to wrap the View in a form.
    // Render a Views form item placeholder.
    return '<!--form-item-' . $this->options['id'] . '--' . $this->view->row_index . '-->';
  }

  function access() {
    // dont show these fields if the user is not in their home chapter
    return brick_staff_access() && brick_is_user_in_home_chapter();
  }

  function get_current_date() {
    // grab the current month from the arguments to this view
    $view = views_get_current_view();
    $dt = $view->args[0];
    $parsedDate = strtotime($dt);

    return $parsedDate;
  }


  /*
   * Add to and alter the form created by Views.
   */
  function views_form(&$form, &$form_state) {
    define("MGR_IDX", 2);
    define("MGR_ORIG_IDX", 4);
    define("COORD_IDX", 3);
    define("COORD_ORIG_IDX", 5);

    // Create a container for our replacements
    $form[$this->options['id']] = array(
      '#type' => 'container',
      '#tree' => TRUE,
    );

    $hdr = "<script language ='javascript'>
	      var optinMap = new Array();
              var changed = false;

	      optinMap[0] = '<span class=\'optinNo\'>Optin</span>';
              optinMap[1] = '<span class=\'optinAvail\'>Available</span>';
              optinMap[2] = '<span class=\'optinPreferred\'>Preferred</span>';

              function setVal(obj, inc) {
                 var ndx = obj.attr('rel');
                 var cur = jQuery('#' + ndx).val();

                 var nwVal = inc ? (cur + 1) % 3 : cur;

                 obj.html(optinMap[nwVal]);
                 jQuery('#' + ndx).val(nwVal);
              }

              jQuery(document).ready(function(){
   		 jQuery('.optinSwitch').click(function() {
		    setVal(jQuery(this), true);
                    // mark the user has done an action
                    changed = true;
		 });

                 jQuery('.optinSwitch').each(function() {
                    setVal(jQuery(this), false); 
                 });

	         // remove the tooltip optin fields don't know why this is there
		 jQuery('.calendar_tooltips-optin-value').remove();
	      
                 jQuery(window).bind('beforeunload', function() {
		 	if (changed) {
				return 'You\'ve made some optin changes, but havent saved them';
			}
			return NULL;
		 });

		 // dont show the changes window if the user is submitting the form!
		 jQuery('#views-form-calendar-calendar').submit(function() {
   			jQuery(window).unbind('beforeunload');
		 });
              }) 
 	   </script>";


    $form[$this->options['id']]['js'] = array(
      '#type' => 'item',
      '#markup' => $hdr
    );

    $options = array();

    $options[-1] = "Not Set";

    for ($i = 0; $i < 10; $i++) {
      $options[$i] = $i . " events";
    }

    global $user;

    $uid = $user->uid;

    // grab the current month from the arguments to this view
    $parsedDate = $this->get_current_date();

    // grab the users current availability setings
    $avail = brick_optin_get_availability($parsedDate, $uid);

    // availability dropdown
    $form[$this->options['id']]['avail'] = array(
      '#type' => 'select',
      '#title' => t('Availability'),
      '#options' => $options,
      '#default_value' => $avail,
      '#description' => t('Set your availability for this month'),
    );

    // Iterate over the result and add our replacement fields to the form.
    foreach ($this->view->result as $row_index => $row) {
      // check to see if this event already has an Manager or Coordinator opted-in
      $assignmentMap = brick_get_assignment_map($row->{$this->aliases['nid']});

      $isManager = array_key_exists('Manager', $assignmentMap);
      $isManagerMe = $isManager && $assignmentMap['Manager'] === $uid;
      $isCoord = array_key_exists('Coordinator', $assignmentMap);
      $isCoordMe = $isCoord && $assignmentMap['Coordinator'] === $uid;


      $res = db_query('SELECT field_optin_role_value, field_optin_preference_value
                FROM field_data_field_optin_event event
                        INNER JOIN field_data_field_optin_person person ON (event.entity_id = person.entity_id)
                        INNER JOIN field_data_field_optin_preference preference ON (preference.entity_id = person.entity_id)
                        INNER JOIN field_data_field_optin_role role ON (role.entity_id = preference.entity_id)
                WHERE field_optin_person_uid = :uid AND field_optin_event_nid = :eid',
        array(':uid' => $uid, ':eid' => $row->{$this->aliases['nid']}));

      $managerPref = 0;
      $coordPref = 0;

      foreach ($res as $rec) {
        $prefVal = brick_optin_pref_string_to_val($rec->field_optin_preference_value);
        if ($rec->field_optin_role_value == 'Manager') {
          $managerPref = $prefVal;
        }
        if ($rec->field_optin_role_value == 'Coordinator') {
          $coordPref = $prefVal;
        }
      }

      // if there is another manager assigned and its not us - we show nothing
      if (user_has_role('Manager', $user) && !($isManager && !$isManagerMe)) {
        // are we assigned as the manager? if so show the user were assigned and ready to go
        if ($isManagerMe) {
          $form[$this->options['id']][$row_index][0] = array(
            '#type' => 'item',
            '#markup' => "M: <span class='optinAssigned'>Assigned</span>"
          );
        }
        else {
          $form[$this->options['id']][$row_index][0] = array(
            '#type' => 'item',
            '#markup' => "M: <span class='optinSwitch optin-man' rel='manager-optin-" . $row_index . "'>Optin</span>"
          );
        }
      }

      // if there is another coordinator assigned and its not us - we show nothing
      if (!($isCoord && !$isCoordMe)) {
        // are we assigned as the coordinator? if so show the user were assigned and ready to go
        if ($isCoordMe) {
          $form[$this->options['id']][$row_index][0] = array(
            '#type' => 'item',
            '#markup' => "C: <span class='optinAssigned'>Assigned</span>"
          );
        }
        else {
          $form[$this->options['id']][$row_index][1] = array(
            '#type' => 'item',
            '#markup' => "C: <span class='optinSwitch optin-coord' rel='coord-optin-" . $row_index . "'>Optin</span>",
          );
        }
      }

      // changeable values
      $form[$this->options['id']][$row_index][MGR_IDX] = array(
        '#type' => 'hidden',
        '#default_value' => $managerPref,
        '#attributes' => array('id' => array('manager-optin-' . $row_index))
      );

      $form[$this->options['id']][$row_index][COORD_IDX] = array(
        '#type' => 'hidden',
        '#default_value' => $coordPref,
        '#attributes' => array('id' => array('coord-optin-' . $row_index))
      );


      // original values
      $form[$this->options['id']][$row_index][MGR_ORIG_IDX] = array(
        '#type' => 'hidden',
        '#default_value' => $managerPref,
        '#attributes' => array('id' => array('orig-manager-optin-' . $row_index))
      );
      $form[$this->options['id']][$row_index][COORD_ORIG_IDX] = array(
        '#type' => 'hidden',
        '#default_value' => $coordPref,
        '#attributes' => array('id' => array('orig-coord-optin-' . $row_index))
      );
    }

    if ($this->view->display_handler->plugin_name != 'page') {
      $form['#action'] = '/' . current_path();
    }
  }

  /**
   * Form submit method.
   */
  function views_form_submit($form, &$form_state) {
    define("MGR_IDX", 2);
    define("MGR_ORIG_IDX", 4);
    define("COORD_IDX", 3);
    define("COORD_ORIG_IDX", 5);

    global $user;

    $uid = $user->uid;

    $updates = 0;


    // Determine which nodes we need to update.
    // Iterate over the view result.

    foreach ($this->view->result as $row_index => $row) {
      $eid = $row->{$this->aliases['nid']};

      // Grab the correspondingly submitted form value.
      $value = $form_state['values'][$this->options['id']][$row_index];


      // new manager value

      if ($value[MGR_IDX] != $value[MGR_ORIG_IDX]) {
        $updates++;

        if ($value[MGR_IDX] == 0) {
          brick_optin_remove($uid, $eid, 'Manager');
        }
        else {
          brick_optin_set_preference($uid, $eid, 'Manager', brick_optin_pref_val_to_string($value[MGR_IDX]));
        }
      }

      // new coordinator value
      if ($value[COORD_IDX] != $value[COORD_ORIG_IDX]) {
        $updates++;

        if ($value[COORD_IDX] == 0) {
          brick_optin_remove($uid, $eid, 'Coordinator');
        }
        else {
          brick_optin_set_preference($uid, $eid, 'Coordinator', brick_optin_pref_val_to_string($value[COORD_IDX]));
        }
      }
    }

    $parsedDate = $this->get_current_date();

    $avail = $form_state['values'][$this->options['id']]['avail'];

    if (brick_optin_set_availability($parsedDate, $avail, $uid)) {
      drupal_set_message(t('Availability updated to @num event(s)', array('@num' => $avail)));
    }

    if ($updates > 0) {
      drupal_set_message(t('Updated @num optin setting(s).', array('@num' => $updates)));
    }
  }
}

?>
