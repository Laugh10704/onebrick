<div class="profile"<?php print $attributes; ?>>

  <?php
  // We hide the name field so it doesn't show up in the source.  As a result, it's not accessible from
  // $user_profile.  As a workaround, we get the name from field_user_chapter.

  //drupal_set_message(print_r($user, true));

  $account = $elements['#account'];
  //$account = user_load($account_id);

  $username = brick_format_name($account->signature);
  echo "<div id='profile_title'>" . $username . "</div>";

  print render($user_profile);
  ?>

  <?php
  print("<dev>");
  //print_r ($user_profile);
  print("</dev>");
  ?>

</div>
