<?php 

function brick_menu_alter(&$items) {
  $items['user/reset/%/%/%']['page arguments'] = array('brick_pass_reset', 2, 3, 4);
}

function brick_menu() {
  $items['a/rsvp'] = array(
    'page callback' => 'brick_dorsvp',
    'page arguments' => array(1),
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['a/unrsvp'] = array(
    'page callback' => 'brick_dounrsvp',
    'page arguments' => array(2),
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['a/%/%/rsvp'] = array(
    'page callback' => 'brick_manager_add_rsvp',
    'page arguments' => array(1,2),
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['forgotpw/ajax'] = array(
    'delivery callback' => 'ajax_deliver',
    'page callback' => 'brick_forgotpw',
    'access callback' => 'brick_access',
    'access arguments' => array(1)
  );

  $items['gosignup/ajax'] = array(
    'delivery callback' => 'ajax_deliver',
    'page callback' => 'brick_signup',
    'access callback' => 'brick_access',
    'access arguments' => array(1)
  );

  $items['rpx/brick_token_handler'] = array(
    'delivery callback' => 'ajax_deliver',
    'page callback' => 'brick_handle_rpx',
    'access callback' => 'brick_access',
    'access arguments' => array(1)
  );

  $items['signup/ajax'] = array(
    'page callback' => 'brick_signup_form_direct',
    'access callback' => 'brick_access',
    'access arguments' => array(1)
  );

  $items['a/login'] = array(
    'page callback' => 'brick_login_ajax',
    'page arguments' => array(1),
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['a/passwordupdate'] = array(
    'page callback' => 'brick_update_password',
    'page arguments' => array(1),
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['node/%/copy'] = array(
      'title' => 'Copy',
      'page callback' => 'brick_copy_node',
      'page arguments' => array(1),
      'access callback' => 'user_has_role',
      'access arguments' => array('Event Creator'),
      'weight' => 20,
      'type' => MENU_LOCAL_TASK
  );

  // FYI The "Edit Roster" button is dynamically created in an alter hook in brick.module

  $items['node/%/promote'] = array(
      'title' => 'Promote',
      'page callback' => 'brick_promote',
      'page arguments' => array(1),
      'access callback' => 'brick_access_type_role',
      'access arguments' => array(1, 'event', array('Manager', 'Coordinator')),
      'weight' => 20,
      'type' => MENU_LOCAL_TASK
  );
 
  $items['node/%/roster'] = array(
      'title' => 'Roster',
      'page callback' => 'brick_gen_roster',
      'page arguments' => array(1),
      'access callback' => 'brick_access_type_role',
      'access arguments' => array(1, 'event', array('Manager', 'Coordinator')),
      'weight' => 20,
      'type' => MENU_LOCAL_TASK
  );

  brick_set_globals();

  $items['chapters'] = array(
     'title' => 'Chapters',
     'page callback' => 'brick_switch_chapter',
     'access callback' => 'brick_access',
     'weight' => -50,
     'menu_name' => 'main-menu',
     'expanded' => TRUE,
     'type' => MENU_NORMAL_ITEM
  );

  $items['webmgr/newsletter-tool'] = array(
     'title' => 'Newsletter Tool',
     'page callback' => 'brick_do_nothing',
     'access callback' => 'user_has_role',
     'access arguments' => array('Newswriter'),
     'weight' => -50,
     'file' => 'newsletter_tool/builder.php',
  );

  $items['webmgr/newsletter-tool-preview'] = array(
     'title' => 'Newsletter Tool Preview',
     'page callback' => 'brick_do_nothing',
     'access callback' => 'user_has_role',
     'access arguments' => array('Newswriter'),
     'weight' => -50,
     'file' => 'newsletter_tool/preview.php',
  );

  $items['webmgr/newsletter-tool-ajax'] = array(
     'title' => 'Newsletter Tool Ajax',
     'page callback' => 'brick_do_nothing',
     'access callback' => 'user_has_role',
     'access arguments' => array('Newswriter'),
     'weight' => -50,
     'file' => 'newsletter_tool/ajax-gateway.php',
  );

  $items['webmgr/newsletter-tool-manage-templates'] = array(
     'title' => 'Newsletter Tool Manage Templates',
     'page callback' => 'brick_do_nothing',
     'access callback' => 'user_has_role',
     'access arguments' => array('Newswriter'),
     'weight' => -50,
     'file' => 'newsletter_tool/manageTemplates.php',
  );

  
  $items['webmgr/assign-events/%/stage'] = array(
    'page callback' => 'brick_stage',
    'page arguments' => array(1),
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['webmgr/assign-events/%/stage/ajax'] = array(
      'page callback' => 'brick_ajax_stage',
      'page arguments' => array(2),
      'access callback' => 'user_has_role',
      'access arguments' => array('Event Assignment'),
  );

  $items['webmgr/assign-events/save'] = array(
    'page callback' => 'brick_assign_staged',
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['webmgr/assign-events/clear'] = array(
    'page callback' => 'brick_clear_staged',
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $items['ajax/check-staged'] = array(
    'page callback' => 'brick_ajax_check_staged',
    'access callback' => 'brick_access',
    'access arguments' => array(1),
  );

  $chapters = variable_get('brick_chapters');

  $order = 0;
  foreach($chapters as $chapter) {
	$order += 1;

    $items['chapters/' . $chapter['nid']] = array(
          'title' => $chapter['title'],
          'page callback' => 'brick_switch_chapter',
          'page arguments' => array(1, $chapter['nid']),
          'access callback' => 'brick_access',
          'weight' => $order,
          'tab_parent' => 'chapters',
          'menu_name' => 'main-menu',
          'type' => MENU_NORMAL_ITEM
     );
  }

  return $items;
}
