<?php
/**
* implements hook_views_data_alter().
*
* @param $data
*   Information about Views' tables and fields.
*/
function brick_views_data_alter(&$data) {
  // Add the reconcile checkbox 
  $data['node']['reconcile'] = array(
    'field' => array(
      'title' => t('Reconcile field'),
      'help' => t('Set whether the rsvp is reconciled or not'),
      'handler' => 'brick_field_handler_reconcile',
    ),
  );
  // RSVP remove
  $data['node']['rsvprem'] = array(
    'field' => array(
      'title' => t('RSVP Remove field'),
      'help' => t('Set whether the rsvp should be removed'),
      'handler' => 'brick_field_handler_rsvp_remove',
    ),
  );
  // opt-in form
  $data['node']['optin'] = array(
    'field' => array(
      'title' => t('Optin fields'),
      'help' => t('Set user optin preferences for an event'),
      'handler' => 'brick_field_handler_optin',
    ),
  );
}
?>
