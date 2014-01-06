<div class="profile"<?php print $attributes; ?>>

<?php 
	echo "<div id='profile_title'>".$field_user_fullname[0]['safe_value']."</div>";

  print render($user_profile);
?>

<?php
	print("<dev>");
	//print_r ($user_profile);
	print("</dev>");
?>

</div>
