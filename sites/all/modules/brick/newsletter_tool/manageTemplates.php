<?php 
$newsletter_tool_dir = "../sites/all/modules/brick/newsletter_tool/";

require('manageTemplates_c.inc'); 
?>

<html>
<head>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php print($newsletter_tool_dir); ?>js/validate.js"></script>

<link href="<?php print($newsletter_tool_dir); ?>css/styles.css" type="text/css" rel="stylesheet">
<link href="<?php print($newsletter_tool_dir); ?>css/custom-theme/jquery-ui-1.8.10.custom.css" type="text/css" rel="stylesheet">

</head>

<body>

<script type="text/javascript">
//initialize jQuery UI

$(function() {
		$("button, input:submit, a", ".buttons").button();
		
		$("a", ".buttons").click(function() { return false; });
	});
</script>


<div id="appContainer">

<table id="appOuterTable">
<tr>
<td style="height:50px;">
<?php include("header.inc"); ?>
</td>
</tr>
<tr>
<td>

<div class="buttons">
  <button type="button" name="return" onClick="location.href='/webmgr/newsletter-tool?stage=1'"/>Return</button>
</div>
<br><br>

<table>
<tr>
<td>
  <script type = "text/javascript">
    function doConfirm() {
      if( $('input[name|=deleteTemplateIDs[]]:checked').length > 0)
        return confirm("Are you sure you want to delete those templates?");

      return true;
    }
  </script>

  <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return doConfirm();">
  <table id="templateTable">
  <tr>
  <td>
  <b>Delete</b>
  </td>
  <td>
  <b>Default</b>
  </td>
  <td>
  <b>Template</b>
  </td>
  </tr>

  <?php foreach($templateRecs as $row): ?>
  <tr> 
     <?php if($row['default_template'] != 1): ?> 
        <td><input type="checkbox" name="deleteTemplateIDs[]" id="deleteTemplateIDs" value="<?php echo $row['ID']?>"</td>
     <?php else: ?>
        <td>&nbsp;</td>
     <?php endif; ?>
     <td><input type="radio" name="templateDefault" value="<?php echo $row['ID']?>" <?php echo $row['default_template'] == '1' ? "checked" : "" ?>></td>
     <td><?php echo $row['name']?><td>
  </tr>
  <?php endforeach; ?>

  </table>
  <br><br>
  <input type="submit" class="button" name="save" value="Save">
  </form>
</td>
<td width="40px">&nbsp;</td>
<td>
  <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">

  Upload new template:<br>
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE ?>" />
  <input type="file" name="templateFile" id="templateFile" size="30">
  <br><br>
  Name for template:<br>
  <input type="text" name="templateName" id="templateName" size="30" maxlength="30">
  <br>
  <input type="checkbox" name="setDefault">&nbsp;Set as default?
  <br><br>
  <input type="submit" class="button" name="upload" value="Upload">
  </form>
</td>
</tr>
</table>

</td>
</tr>

</table>

</div>

<br><br>


</body>
</html>
