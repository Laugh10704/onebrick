<div class="profile"<?php print $attributes; ?>>

<?php 
	$username = $user_profile['field_user_profile_q1']['#object']->field_user_fullname['und'][0]['safe_value'];
	echo "<div id='profile_title'>".brick_format_name($username)."</div>";

  print render($user_profile);
?>

<?php
	print("<dev>");
//	print_r ($user_profile);
	print("</dev>");
?>

</div>
