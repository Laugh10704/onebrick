<?php
/**
   * @file
   *
   * A Views' field handler for editing a node title.
   *
   */
class brick_field_handler_rsvp_remove extends views_handler_field {
  
  function construct() {
    parent::construct();
    $this->additional_fields = array(
      'nid' => 'nid',
      'field_rsvp_role' => array(
        'table' => 'field_data_field_rsvp_role',
        'field' => 'field_rsvp_role_value',
      )
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
     return brick_staff_access();
  }

   
  /*
   * Add to and alter the form created by Views.
   */
  function views_form(&$form, &$form_state) {
    // Create a container for our replacements
    $form[$this->options['id']] = array(
      '#type' => 'container',
      '#tree' => TRUE,
    );
    // Iterate over the result and add our replacement fields to the form.
    foreach($this->view->result as $row_index => $row) {
      if ($row->{$this->aliases['field_rsvp_role']} == 'Volunteer') {
      	// Add a text field to the form.  This array convention
      	// corresponds to the placeholder HTML comment syntax.
      	$form[$this->options['id']][$row_index] = array(
        	'#type' => 'checkbox',
        	'#required' => FALSE,
      	);
      }
    }

    if ($this->view->display_handler->plugin_name != 'page') {
        $form['#action'] = '/' . current_path();
    }
  }

  /**
   * Form submit method.
   */
  function views_form_submit($form, &$form_state) {
    // Determine which nodes we need to update.
    $updates = array();
    $num = 0;

    // Iterate over the view result.
    foreach($this->view->result as $row_index => $row) {
	if (array_key_exists($row_index, $form_state['values'][$this->options['id']])) {
		// Grab the correspondingly submitted form value.
      		$value = $form_state['values'][$this->options['id']][$row_index];
      		// If the submitted value is different from the original value add it to the
      		// array of nodes to update.
      		if ($value) {
        		brick_delete_rsvp($row->{$this->aliases['nid']});
        		$num++;
      		}
	}
    }
    
    drupal_set_message(t('Removed @num rsvps.', array('@num' => $num)));
  }
}
?>
