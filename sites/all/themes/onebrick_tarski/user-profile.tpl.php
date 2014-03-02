<div class="profile"<?php print $attributes; ?>>

<?php 
  // We hide the name field so it doesn't show up in the source.  As a result, it's not accessible from
  // $user_profile.  As a workaround, we get the name from field_user_chapter.
  $username = $user_profile['field_user_chapter']['#object']->field_user_fullname['und'][0]['safe_value'];
  $username = brick_format_name($username);
  echo "<div id='profile_title'>".$username."</div>";

  print render($user_profile);
?>

<?php
  print("<dev>");
  //print_r ($user_profile);
  print("</dev>");
?>

</div>
