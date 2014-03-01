<?php

function brick_add_organization_contact_form($form, &$form_state, $nid) {
  $form['#parents']=array();

  $field = field_info_field("field_org_contact_person");
  $instance = field_info_instance('node', "field_org_contact_person", "organization_contact");
  $result = field_default_form('node', null, $field, $instance, LANGUAGE_NONE, array(), $form, $form_state);
  $result['field_org_contact_person']['und'][0]['uid']['#title'] = "Search for existing contact:";

  $form += (array)$result;

  $form['txt'] = array(
    '#type' => "item",
    '#markup' => '<b>OR</b>',
  );

  $form['name'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s Name:'),
    '#required' => FALSE
  );

  $form['email'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s Email:'),
    '#required' => FALSE
  );

  $form['sep'] = array(
    '#type' => "item",
    '#markup' => '<hr/>',
  );

  $form['phone'] = array(
    '#type' => "textfield",
    '#title' => t('Contact\'s Phone (Not Required):'),
    '#required' => FALSE
  );

  $form['title'] = array(
    '#type' => "textfield",
    '#title' => t('User\'s organization title:'),
    '#required' => TRUE
  );

  $form['submit'] = array(
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
    $email = $form_state['values']['phone'];
    $phone = $form_state['values']['phone'];
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
    $roles = user_roles();
    $user = brick_create_account_impl($form_state['values']['email'], $form_state['values']['name'],
      array(array_search('organization_contact', $roles) => 'organization_contact'));
    $uid = $user->uid;
  }
  else {
    $user = user_load($uid);
  }

  if ($form_state['values']['phone']) {
    $user->field_user_phone[LANGUAGE_NONE][0]['value'] = $form_state['values']['phone'];
    user_save($user);
  }

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
