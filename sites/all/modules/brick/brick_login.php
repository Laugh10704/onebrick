<?php
// $Id: brick_login.php,v 1.25 2013/12/18 03:09:17 jordan Exp crc $

function brick_check_password($password, $user) {
  // Wrap user_check_password to check for imported md5 hashes.

  $hash_type = substr($user->pass, 0, 3);
  $stored_hash = substr($user->pass, 3);

  if ($hash_type == '$I$') {
    // The users account has a hash imported from the old systems
    $hash = md5($password);
    if ($stored_hash == $hash) {
      // The password matches, update the stored hash and fall through to the normal check/login code
      $user->pass = user_hash_password($password);
      user_save($user);
    }
  }

  return (user_check_password($password, $user));
}

function brick_colorbox_form_access() {
  return TRUE;
}

function brick_create_guest_account($emailAddr, $name) {
  $roles = user_roles();

  $newUser = new StdClass();
  $newUser->is_new = TRUE;
  $newUser->status = TRUE;
  $newUser->field_user_fullname[LANGUAGE_NONE][0]['value'] = $name;
  $newUser->name = $emailAddr;
  $newUser->pass = '';
  $newUser->mail = $emailAddr;
  $newUser->status = 1;
  $newUser->timezone = "America/New_York";
  $newUser->roles = array(array_search('guest_user', $roles) => 'guest_user');
  $newUser->init = $emailAddr;
  user_save($newUser);

  return $newUser;
}

function load_user($mail) {
  $account = user_load_by_mail($mail);
  if (!$account) {
    $account = user_load_by_name($mail);
  }
  return $account;
}

function brick_create_account($form, $form_state) {
  if (form_get_errors()) {
    return $form;
  }

  global $user;
  $account = NULL;

  $uid = $form_state['values']['uid'];
  $pass = $form_state['values']['pw'];
  $fullname = $form_state['values']['fullname'];
  $chapter = intval($form_state['values']['chapters']);
  $newsletter = $form_state['values']['newsletter'];
  $mail = $form_state['values']['username'];
  // an admin is creating this user
  $isAdmin = $form_state['values']['is_admin'] == "YES";

  if (empty($newsletter)) {
    $newsletter = 0;
  }

  $update = array();
  $conn = NULL;
  $transName = "createAccount";
  $baseVal = 100000000;

  // give me a number representation for this email
  $mailId = $baseVal + brick_hashcode($mail) % $baseVal;

  // make sure we have exclusive eaccess to this user
  $semaphore = sem_get($mailId);

  try {
    sem_acquire($semaphore);

    // we don't want anyone writing to the output buffer. Especially lame modules which don't know how to use echo.
    ob_start();
    $conn = Database::getConnection();
    $conn->pushTransaction($transName);

    if (!empty($uid)) {
      // user that already exists, carried over from signin screen
      $account = user_load($uid);
    }
    else {
      // check to see if this is a user with no password (old user)
      $account = load_user($mail);

      if (brick_is_full_user($account)) {
        sem_release($semaphore);
        ob_end_clean();
        drupal_set_message("The email '$mail' is already in use");
        $conn->rollback($transName);
        return $form;
      }
    }

    if (!$account) {
      // brand new user
      $update['name'] = $mail;
      $update['mail'] = $mail;
      $update['init'] = $mail;
    }

    $update['field_user_fullname'][LANGUAGE_NONE][0]['value'] = $fullname;
    $update['pass'] = $pass;
    $update['roles'] = array(DRUPAL_AUTHENTICATED_RID => TRUE);
    $update['status'] = 0;
    $update['timezone'] = "America/Los_Angeles";
    $update['field_user_chapter']['und'][0]['nid'] = $chapter;

    $createdUser = user_save($account, $update);

    if (!empty($createdUser->uid)) {
      db_update('field_data_field_user_chapter')
        ->fields(array('field_user_chapter_nid' => $chapter))
        ->condition('entity_id', $createdUser->uid, '=')
        ->execute();
      db_update('field_data_field_user_subscribed')
        ->fields(array('field_user_subscribed_value' => $newsletter))
        ->condition('entity_id', $createdUser->uid, '=')
        ->execute();
    }

    //watchdog("Info", "Logging in User");

    //user_login_finalize();

    //send approval email
    //_user_mail_notify('register_no_approval_required', $user);

    // requires user_verify module: send a user verification email
    $udata = new stdClass();
    $udata->uid = $createdUser->uid;
    drupal_write_record('user_verify', $udata);

    if (_user_verify_send_code($udata)) {
      $conn->popTransaction($transName);
    }
    else {
      throw new Exception("Failure sending verify email");
    }

    sem_release($semaphore);

    // if the user themselves added an account, we switch their user variable here
    if (!$isAdmin) {
      $user = $createdUser;
    }

  } catch (Exception $ex) {
    sem_release($semaphore);
    ob_end_clean();
    drupal_set_message("There was an error setting up your account. Please contact us at bugs@onebrick.org");
    $conn->rollback($transName);
    return $form;
  }

  ob_end_clean();

  if ($isAdmin) {
    drupal_set_message("Account created. The user will need to verify the account before it can be used.");
    $commands = brick_build_refresh_page_command();
  }
  else {
    $commands = brick_move_to_new_cbox_form("Thanks for signing up!", drupal_render(drupal_get_form('brick_thankyou_form')), "#formWrapper");
  }

  return $commands;
}

function brick_user_insert(&$edit, $account, $category) {
  update_user_name($edit['field_user_fullname'][LANGUAGE_NONE][0]['value'], $account->uid);
}

function update_user_name($name, $uid) {
  watchdog('brick', $name . '_' . $uid);
  db_update('users')
    ->fields(array('name' => $name . '_' . $uid))
    ->condition('uid', $uid, '=')
    ->execute();

}

function brick_thankyou_form($form, $form_state) {
  $form['thanks']['intro'] = array(
    '#markup' => '<div class="thankYouMessage">Please check your email to activate your account.</div>',
  );


  return $form;
}

function brick_forgotpw() {
  return brick_move_to_new_cbox_form("Reset Password", drupal_render(drupal_get_form('brick_forgot_pw_form', $_POST['username'])), "#formWrapper");
}

function brick_signup() {
  return brick_move_to_new_cbox_form("Create Account", drupal_render(drupal_get_form('brick_create_account_form', NULL, $_POST['username'])), "#formWrapper");
}

/** Meant to be called by an ajax request **/
function brick_signup_form_direct() {
  print drupal_render(drupal_get_form('brick_create_account_form'));
}

function brick_create_account_form($form, $form_state, $uid = NULL, $username = NULL) {
  $loadedUser = NULL;
  $name = "";
  $isGuestUser = FALSE;

  if (!empty($username)) {
    $loadedUser = load_user($username);
    if ($loadedUser && !brick_is_full_user($loadedUser)) {
      $name = brick_get_user_name($loadedUser);
      $isGuestUser = TRUE;
    }
  }

  // if this isn't a full user, show them a message
  if ($isGuestUser) {
    //if ($loadedUser->field_copied_over[LANGUAGE_NONE][0]['value'] == TRUE) {
    //$form['create']['message'] = array(
    //'#markup' => "<div class='messages status'><b>We've recently updated our system!</b> Please provide a new password to login. Sorry for the inconvienence!</div>"
    //);
    //}
    //else {
    $form['create']['message'] = array(
        '#markup' => "<div class='messages status'><b>Welcome Back!</b> Please provide a password to login with from now on</div>"
      );
    //}
  }

  $form['create']['username'] = array(
    '#type' => "textfield",
    '#title' => t("Email:"),
    '#size' => 40,
    '#required' => TRUE,
    '#default_value' => $username
  );

  $form['create']['pw'] = array(
    '#type' => "password",
    '#title' => t('Password:'),
    '#size' => 40,
    '#required' => TRUE,
  );

  $form['create']['pwconf'] = array(
    '#type' => "password",
    '#title' => t('Password Again:'),
    '#size' => 40,
    '#required' => TRUE,
  );

  $form['create']['fullname'] = array(
    '#type' => "textfield",
    '#title' => t("Your name:"),
    '#default_value' => $name,
    '#size' => 40,
    '#required' => TRUE
  );

  $chapters = variable_get('brick_chapters');
  $chapterMap = array();

  foreach ($chapters as $chapter) {
    $chapterMap[$chapter['nid']] = $chapter['title'];
  }

  $form['create']['chapters'] = array(
    '#type' => "select",
    '#options' => $chapterMap,
    '#title' => t('Default chapter:'),
    '#default_value' => brick_current_chapter()
  );

  $form['create']['newsletter'] = array(
    '#type' => 'checkbox',
    '#title' => t('Join the One Brick mailing list'),
    '#default_value' => TRUE,
    '#title_display' => 'after'
  );

  $form['create']['uid'] = array(
    '#type' => 'hidden',
    '#value' => $uid,
    '#default_value' => $uid
  );

  $form['create']['is_admin'] = array(
    '#type' => 'hidden',
    '#default_value' => '',
    '#attributes' => array(
      'id' => 'isAdminSubmit'
    )
  );

  $form['create']['createButtonArea'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => 'createButtonArea'
    )
  );

  $form['create']['createButtonArea']['submitCreate'] = array(
    '#type' => "button",
    '#button_type' => 'button',
    '#value' => t('Create Account'),
    '#ajax' => array(
      'wrapper' => 'formWrapper',
      'callback' => 'brick_create_account',
      'method' => 'html'
    )
  );

  $form['create']['createButtonArea']['loginFeedback'] = array(
    '#markup' => "<span id='loginFeedback'></span>"
  );

  return $form;
}

function brick_create_account_form_validate($form, $form_state) {
  if (!form_get_errors()) {
    $mail = $form_state['values']['username'];
    $loadedUser = load_user($mail);
    if ($loadedUser && $loadedUser->status && brick_is_full_user($loadedUser)) {
      form_set_error('username', "Email is already in use");
    }
    if ($form_state['values']['pw'] != $form_state['values']['pwconf']) {
      form_set_error('pwconf', "Password fields don't match");
    }
  }
}

function brick_forgot_pw_form($form, $form_state, $username) {
  $form['pwWrapper'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => "pwWrap"
    )
  );
  $form['pwWrapper']['emailHeader'] = array(
    '#markup' => "<h3 class='loginHeader'>Email Address</h3>"
  );
  $form['pwWrapper']['emailSection'] = array(
    '#type' => "container"
  );
  $form['pwWrapper']['emailSection']['username'] = array(
    '#type' => "textfield",
    '#title' => t('Email:'),
    '#size' => 40,
    '#required' => TRUE,
    '#default_value' => "$username"
  );
  $form['pwWrapper']['emailSection']['submitButton'] = array(
    '#type' => "button",
    '#value' => t('Send Password Reset Email'),
    '#ajax' => array(
      'wrapper' => 'pwWrap',
      'callback' => 'brick_forgot_pw_submit',
      'method' => 'html',
      'event' => 'click'
    )
  );

  return $form;
}

function brick_forgot_pw_form_validate($form, $form_state) {
  if (!form_get_errors()) {
    $mail = $form_state['values']['username'];
    $loadedUser = load_user($mail);
    if (!$loadedUser) {
      form_set_error('username', "Could not find a user with that email address");
    }
  }
}


function brick_forgot_pw_submit($form, $form_state) {
  if (form_get_errors()) {
    return $form['pwWrapper'];
  }

  $mail = $form_state['values']['username'];
  $account = load_user($mail);
  $params['account'] = $account;
  $language = user_preferred_language($account);
  $mail = drupal_mail('user', 'password_reset', $account->mail, $language, $params);

  return brick_move_to_new_cbox_form("Reset Password", drupal_render(drupal_get_form('brick_pw_reset_sent_form', $form_state['values']['username'])));
}

function brick_pass_reset($form, &$form_state, $uid, $timestamp, $hashed_pass, $action = NULL) {
  $user = user_load($uid);
  $error = TRUE;

  if ($user) {
    $rehash = user_pass_rehash($user->pass, $timestamp, $user->login);
    if ($hashed_pass == $rehash) {
      $error = FALSE;
    }
  }

  if ($error) {
    drupal_set_message("Reset password link is invalid or has expired.", 'error');
    return;
  }

  $userName = explode(' ', brick_get_user_name($user));

  $form['reset']['message'] = array(
    '#markup' => "<div class='resetPasswordHeader'>Hi " . $userName[0] . ". Please enter a new password.</div>"
  );

  $form['reset']['pw'] = array(
    '#type' => "password",
    '#title' => t('Password:'),
    '#size' => 40,
    '#required' => TRUE,
  );

  $form['reset']['pwconf'] = array(
    '#type' => "password",
    '#title' => t('Password Again:'),
    '#size' => 40,
    '#required' => TRUE,
  );

  $form['reset']['uid'] = array(
    '#type' => "hidden",
    '#value' => $uid
  );

  $form['reset']['submit'] = array(
    '#type' => "submit",
    '#value' => t('Change Password')
  );

  return $form;
}

function brick_pass_reset_validate($form, $form_state) {
  if ($form_state['values']['pw'] != $form_state['values']['pwconf']) {
    form_set_error('pw', 'Passwords did not match');
  }
}

function brick_pass_reset_submit($form, $form_state) {
  if (form_get_errors()) {
    return $form;
  }

  $user = user_load($form_state['values']['uid']);

  $update = array(
    'pass' => $form_state['values']['pw']
  );

  user_save($user, $update);

  drupal_set_message("Your password has been changed. Please login to continue");

  drupal_goto();
}

function brick_pw_reset_sent_form($form, $form_state, $user) {
  $form['thanks']['intro'] = array(
    '#markup' => '<div class="forgotPWMessage">An email has been sent to <b>' . $user . '</b> with a link that will allow you to reset your password</div>',
  );

  $form['thanks']['buttonArea'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => 'buttonArea'
    )
  );

  $form['thanks']['buttonArea']['submit'] = array(
    '#markup' => "<INPUT TYPE=\"button\" VALUE=\"Close\" class=\"form-button\" onClick=\"parent.location='/'\">"
  );


  return $form;
}

function brick_login_form($form, $form_state) {
  $form['loginWrapper'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => "loginWrap"
    )
  );
  $form['loginWrapper']['loginSection'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => "loginSection"
    )
  );
  $form['loginWrapper']['loginSection']['username'] = array(
    '#type' => "textfield",
    '#title' => t('Email:'),
    '#size' => 40,
    '#required' => TRUE
  );
  $form['loginWrapper']['loginSection']['password'] = array(
    '#type' => "password",
    '#attributes' => array(
      'id' => "password"
    ),
    '#title' => t('Password:'),
    '#size' => 40,
    '#required' => TRUE
  );

  $form['loginWrapper']['loginSection']['buttonArea'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => 'buttonArea'
    )
  );
  $form['loginWrapper']['loginSection']['buttonArea']['submitLogin'] = array(
    '#type' => "button",
    '#value' => t('Sign In'),
    '#ajax' => array(
      'wrapper' => 'loginWrap',
      'callback' => 'brick_login_ajax',
      'method' => 'html',
      'event' => 'click'
    )
  );
  $form['loginWrapper']['loginSection']['buttonArea']['loginFeedback'] = array(
    '#markup' => "<span id='loginFeedback'></span>"
  );
  $form['forgotPwArea']['forgotPwLinkSection'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => "forgotPwLinkSection"
    )
  );
  $form['forgotPwArea']['forgotPwLinkSection']['forgotPwLink'] = array(
    '#markup' => "<a id='forgotPwLink' href='/forgotpw/ajax/username' class='use-ajax'>reset password</a>"
  );
  // FYI, this clears the float that will be applied to the "throbber" when the user clicks Submit
  $form['signupArea']['floatClear'] = array(
    '#markup' => "<div style='clear:both'></div>"
  );


  $form['signupArea']['signupHeader'] = array(
    '#markup' => "<h3 class='loginHeader' id='signup'>Create Account</h3>"
  );
  $form['signupArea']['signupLinkContainer'] = array(
    '#type' => "container",
    '#attributes' => array(
      'id' => "signupSection"
    )
  );

  $form['signupArea']['signupLinkContainer']['signupLink'] = array(
    '#markup' => "<a id='newUserLink' onclick='javascript:toSignupForm()'>Don't have an account? Click here to sign up!</a>"

  );
  return $form;

}

function brick_login_form_validate($form, $form_state) {
}

function build_create_account_form($existing_uid, $username) {
  $form = drupal_get_form('brick_create_account_form', $existing_uid, $username);

  return $form;
}

function brick_move_to_login_form() {
  return brick_move_to_new_cbox_form("Login", drupal_render(drupal_get_form('brick_login_form')));
}

function brick_login_ajax($form, $form_state) {
  if (form_get_errors()) {
    return $form['loginWrapper']['loginSection'];
  }

  global $user;

  $username = $form_state['values']['username'];
  $password = $form_state['values']['password'];

  // try to load by email first, and then by username
  $loadedUser = user_load_by_mail($username);
  if (!$loadedUser) {
    $loadedUser = user_load_by_name($username);
  }

  if ($loadedUser) {
    // see if this user needs to verify
    if (!check_user_verified($loadedUser)) {
      form_set_error('username', t('Please check your email to verify your account'));
    }
    else {
      if (!brick_is_full_user($loadedUser)) {
        // I make sure to pass "formWrapper" here so that it replaces the entire form, not just the internal area
        return brick_move_to_new_cbox_form("Create Account", drupal_render(build_create_account_form($loadedUser->uid, $username)), "#formWrapper");
      }
      else {
        require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
        if (brick_check_password($password, $loadedUser)) {
          $user = $loadedUser;

          user_login_finalize();

          // refresh the user's page
          $commands = brick_build_refresh_page_command();

          return $commands;
        }
        else {
          form_set_error('password', t('Invalid Password'));
        }
      }
    }
  }
  else {
    form_set_error('username', t('Invalid Email Address'));
  }

  return $form['loginWrapper']['loginSection'];
}

function brick_handle_rpx() {
  $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';

  if ($token) {
    $rpx_data = RPX::auth_info($token, variable_get('rpx_apikey', ''), variable_get('rpx_extended_authinfo', FALSE));

    $rpx_id = $rpx_data['profile']['identifier'];
    $provider_title = $rpx_data['profile']['providerName'];

    // Save provider info (for token replacement and account linking).
    $_SESSION['rpx_last_provider_info'] = array(
      'name' => _rpx_get_provider_machine_name($provider_title),
      'title' => $provider_title,
    );

    $account = user_external_load($rpx_id);

    if (isset($account->uid)) {
      rpx_core_delete_rpx_session();
    }
    else {
      watchdog("Info", "Creating new account");
      $account = array();
      $account['id'] = $rpx_id;
      $account['password'] = user_password();
      $account['data']['rpx_data']['profile'] = $rpx_data['profile'];
      $account = _rpx_save_profile_picture($account);

      _rpx_import_user_data($account);

      unset($account['data']['rpx_data']);

      user_save($account);

      rpx_core_delete_rpx_session();
    }
  }
}

?>
