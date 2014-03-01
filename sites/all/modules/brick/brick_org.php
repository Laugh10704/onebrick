<?php

function brick_add_organization_contact_form($form, &$form_state, $nid) {
  $form['#parents']=array();

  $form['group0'] = array(
    '#type' => "container"
  );

  $field = field_info_field("field_org_contact_person");
  $instance = field_info_instance('node', "field_org_contact_person", "organization_contact");
  $result = field_default_form('node', null, $field, $instance, LANGUAGE_NONE, array(), $form, $form_state);
  $result['field_org_contact_person']['und'][0]['uid']['#title'] = "Search for existing contact:";

  $form['group0'] += (array)$result;

  $form['group0']['txt'] = array(
    '#type' => "item",
    '#markup' => '<b>OR</b>',
  );

  $form['group0']['name'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s Name:'),
    '#required' => FALSE
  );

  $form['group0']['email'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s Email:'),
    '#required' => FALSE
  );

  $form['group1'] = array(
     '#type' => "container"
   );

  $form['group1']['phone'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s Phone (Not Required):'),
    '#required' => FALSE
  );

  $form['group1']['title'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s organization title:'),
    '#required' => TRUE
  );

  $form['group1']['submit'] = array(
    '#type' => "submit",
    '#value' => t('Add New Contact'),
  );

  $form['nid'] = array(
    '#type' => "hidden",
    '#value' => $nid
  );

  return $form;
}

function brick_add_organization_contact_form_validate($form, $form_state) {
  if (!form_get_errors()) {
    $existing = $form_state['values']['field_org_contact_person']['und'][0]['uid'];
    $name = $form_state['values']['name'];
    $email = $form_state['values']['email'];
    if (!$existing) {
      if (!$name) {
        form_set_error('name', "Missing contact name");
      }
      if (!$email) {
        form_set_error('email', "Missing contact email");
      }
    }
  }
}

function brick_add_organization_contact_form_submit($form, &$form_state) {
  if (form_get_errors()) {
    return $form;
  }

  $uid = $form_state['values']['field_org_contact_person']['und'][0]['uid'];

  if (!$uid) {
    $mail = $form_state['values']['email'];
    $user = load_user($mail);
    $roles = user_roles();

    if ($user) {
      // make sure the user has the organizational contact role
      user_multiple_role_edit(array($user->uid), 'add_role', array_search('Organization Contact', $roles));
    }
    else {
      $role_array = array(array_search('Organization Contact', $roles) => 'Organization Contact');

      $user = brick_create_account_impl($mail, $form_state['values']['name'], $role_array);
    }
    $uid = $user->uid;
  }
  else {
    $user = user_load($uid);
  }

  if ($form_state['values']['phone']) {
    $user->field_user_phone[LANGUAGE_NONE][0]['value'] = $form_state['values']['phone'];
    user_save($user);
  }

  // make sure the contact doesn't already exist

  $node = new StdClass();
  $node->type = 'organization_contact';
  $node->status = 1;
  $node->title = $form_state['values']['title'];
  $node->field_org_contact_person['und'][0]['uid'] = $uid;
  $node->field_org_contact_organization['und'][0]['nid'] = intval($form_state['values']['nid']);

  $node = node_submit($node);
  node_save($node);

  drupal_set_message("Contact Added");

  return $form;
}

?>
