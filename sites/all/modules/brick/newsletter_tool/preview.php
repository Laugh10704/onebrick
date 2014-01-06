<?php

require('preview_c.inc');

$newsletter_tool_dir = "../sites/all/modules/brick/newsletter_tool/";

?>

<html>

<head>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php print($newsletter_tool_dir); ?>js/ajax-functions.js"></script>

<link href="<?php print($newsletter_tool_dir); ?>css/styles.css" type="text/css" rel="stylesheet">
<link href="<?php print($newsletter_tool_dir); ?>css/custom-theme/jquery-ui-1.8.10.custom.css" type="text/css" rel="stylesheet">

</head>

<body>

<script type="text/javascript">
$(function() {
		$("button, input:submit, a", ".buttons").button();
		
		$("a", ".buttons").click(function() { return false; });
	});
</script>

<div id="appContainer">
<table id="appOuterTable" border=0>
<tr>
<td style="height:50px;">
<?php include("header.inc"); ?>
</td>
</tr>
<tr>
<td>
<div class="buttons" style="float:right;"><button type="button" onClick="location.href='/webmgr/newsletter-tool?stage=2'">Make changes</button>
<?php if($stage == "3") : ?>
&nbsp;&nbsp;&nbsp;<button type="button" onClick="location.href='/webmgr/newsletter-tool-preview?code=y'">Get HTML</button>
<?php endif; ?>
</div>
</td>
</tr>
<tr>
<td>
<?php if($stage == "3"): ?>
  <?php echo $templateStr; ?> 
<?php else: ?>
  Click in code area than press CTRL-a then CTRL-c to copy the HTML (or CMD-a then CMD-c on Mac).<br><br>
  <div align="center">
  <textarea id="newsletterHTML" cols="90" rows="30" readonly><?php echo htmlentities($templateStr) ?></textarea>
  </div>
  <br/><br/>
  <hr/>
  <br/><br/>
  <div class="buttons">
  Test send this newsletter to email address: &nbsp; <input id="toEmail" type="text" size="40" value="<?php global $user; echo($user->name) ?>">&nbsp;
  <button id="sendNewsletter">Send</button>

  <br/>
  [CURRENTLY DOES NOT WORK--should send only to authorized chapter?] Send newsletter to <?php echo(brick_get_chapter_name()); ?> chapter: &nbsp; <button id="sendNewsletter">Send</button>

    <div id="emailMsg" style="font-style:italic;"></div>
  </div>
<?php endif; ?>
</td>
</tr>
</table>
</div>

</body>
</html>
