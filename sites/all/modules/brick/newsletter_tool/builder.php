<?php

$newsletter_tool_dir = "../sites/all/modules/brick/newsletter_tool/";

//$_SESSION['tool'] = 'newsletterBuilder';

require('builder_c.inc'); 

function __autoload($class_name) {
    include $class_name . '.php';
}

?>

<html>

<head>
<title></title>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php print($newsletter_tool_dir); ?>ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php print($newsletter_tool_dir); ?>ckfinder/ckfinder.js"></script>
<script type="text/javascript" src="<?php print($newsletter_tool_dir); ?>js/ajax-functions.js"></script>
<script type="text/javascript" src="<?php print($newsletter_tool_dir); ?>js/validate.js"></script>

<link href="<?php print($newsletter_tool_dir); ?>css/styles.css" type="text/css" rel="stylesheet">
<link href="<?php print($newsletter_tool_dir); ?>css/custom-theme/jquery-ui-1.8.10.custom.css" type="text/css" rel="stylesheet">
</head>

<?php
require('articleJSFunctions.inc');
?>

<script type="text/javascript">
//initialize jQuery UI
$(function() {
        //datepickers
		$('#newsletterDate').datepicker();
		$('#eventsEndDate').datepicker();

        // Tabs
		$('#tabs').tabs();
		//hover states on the static widgets
		$('#dialog_link, ul#icons li').hover(
			function() { $(this).addClass('ui-state-hover'); }, 
			function() { $(this).removeClass('ui-state-hover'); }
		);

        //buttons
		$("button, input:submit, a", ".buttons").button();
		$("a", ".buttons").click(function() { return false; });
});
</script>

<script type="text/javascript">
function doSubmit(stage){
  if(stage == 2){
    //must first save the last article that was entered
    storeArticle(articleNum);
    addArticlesToForm();
  }

  return validate(stage);
}
</script>

<body>

<form method="post" action="newsletter-tool?stage=<?php echo $stage + 1?>" onSubmit="return doSubmit(<?php echo $stage ?>)">

<div id="appContainer">
    
    <table id="appOuterTable" border="0">
    <tr>
    <td style="height:50px;">
      <?php include("header.inc"); ?>

      <?php if ($stage == 1): ?>
        <!-- initial settings stage -->

        <table class="appBodyTable" > 
        <tr>
        <td>
          <br>
          <i>Download instructions <a href="<?php print($newsletter_tool_dir); ?>docs/Newsletter-builder-instructions.pdf" class="uiLink">here</a> </i>
          <br><br><br>
          <b>Select your region:</b><br>
          <select name="regionID" id="regionID">
          <option value="">&nbsp;</option>
          <?php foreach($regionRecs as $region): ?>
              <option value="<?php echo $region['nid']?>"><?php echo $region['title']?></option>
          <?php endforeach; ?>
          </select>
          <tr>
          <td>
            <b>Template:</b><br>
            <div id="templatesDiv">
              <select></select>
            </div>
          </td>
          </tr>
          <tr>
          <td>
            Load articles from existing newsletter:
            <div id="existingNewslettersDiv">
              <select></select>
            </div>
          </td>
          </tr>
          <tr>
          <td>
            <b>Newsletter date:</b><br>
            <input type="text" name="newsletterDate" id="newsletterDate" class="text" value="<?php echo $newsletterDefaultDate; ?>" readonly>
          </td>
          </tr>
          <tr>
          <td>
            <b>List events until:</b><br>
            <input class="text" name="eventsEndDate" id="eventsEndDate" value="<?php echo $eventsEndDefaultDate; ?>" readonly>
          </td>
          </tr>
          <tr>
          <td>
            <br><br>
            <div class="buttons">
              <input type="submit" name="Next" value="Next">
            </div>
            <br><br>
          </td>
          </tr>
          </table><!-- ends appBodyTable -->

      <?php else: ?>
      <!-- newsletter stage -->

          <table class="appBodyTable">
          <tr>
          <td>
            <div class="buttons" style="float:right;">
              <button type="submit" name="preview">Preview</button>&nbsp;&nbsp;
              <button type="button" onClick="location.href='/webmgr/newsletter-tool?stage=1'">Start over</button>
            </div>
          </td>
          </tr>
          <tr>
          <td valign="top">
             <div id="tabs">
                <ul>
                  <li><a href="#tabs-1">Events</a></li>
                  <li><a href="#tabs-2">Articles</a></li>
                </ul>
                <div id="tabs-1">
                  <div align="center">
                        <table class="appEventTable" width="575" cellpadding="0" cellspacing="0">
                        <?php foreach(unserialize($_SESSION['eventsArray']) as $event): ?>
                          <tr>
                          <td><?php echo $event->date;?>&nbsp;&nbsp;</td>
                          <td><b><?php echo $event->name;?></b></td>
                          <td width="100">RSVPs: <?php echo $event->rsvpCount;?>/<?php echo $event->eventCapacity;?><br>(max: <?php echo $event->maxRsvps;?>)</td>
                          <td width="150"><input type="checkbox" name="includeEventIDs[]" value="<?php echo $event->id;?>" <?php echo $event->checked ? "checked" : "";?> >&nbsp;Include event</td>
                          </tr>
                          <tr>
                          <td>&nbsp;</td>
                          <td colspan="2">
                            Description:<br>
                            <textarea name="descriptionEvent<?php echo $event->id;?>" rows="3" cols="40"/><?php echo $event->description;?></textarea>
                          </td>
                          <td valign="top"><input type="checkbox" name="includeEventDescriptionIDs[]" value="<?php echo $event->id;?>" <?php echo $event->descriptionChecked ? "checked" : "";?> >&nbsp;Include description
                          </td>
                          </tr>
                          <tr>
                          <td colspan="4" style="height:30px;"></td>
                          </tr>
                         <?php endforeach; ?>
                         </table>
                   </div>
                </div> <!-- ends tab-1 -->
                <div id="tabs-2">
                  <div id="appArticleDiv" class="appArticleDiv">
                    <table class="appArticleTable">
                    <tr>
                    <td colspan="2"><div id="appArticleHeading" class="appArticleHeading"></div></td>
                    </tr>
                    <tr>
                    <td width="150px"><b>Section</b>:</td>
                    <td>
                      <select name="articleSectionNum" id="articleSectionNum" class="largeSelect">
                      <?php
                        $count = 1;
			$newsletterGenerator = new NewsletterGenerator($_SESSION['templateID'],$_SESSION['newsletterID']);
			$articleHeadings = $newsletterGenerator->getArticleHeadings();
                        foreach($articleHeadings as $heading){
                          echo "<option value='" . $count . "'>" . $heading . "</option>";
                          $count++;
                        }
                      ?>
                      </select>
                    </td>
                    </tr>
                    <tr>
                    <td>Use existing article:</td>
                    <td>
                      <div id="existingArticlesDiv" style="float:left">
                        <select name="existingArticleID" id="existingArticleID" class="largeSelect"></select>
                      </div>
                      <div id="existingArticleStatus" class="ajaxStatus" style="float:left">&nbsp;&nbsp;&nbsp;Loading...</div>
                    </td>
                    </tr>
                    <tr>
                    <td>Title:</td>
                    <td><input name="articleTitle" id="articleTitle" type="text" size="40" maxlength="50" ></td>
                    </tr>
                    <tr>
                    <td><b>Body</b>:</td>
                    <td>&nbsp;</td>
                    </tr>
                    </table>

                    <div align="center">
                      <table>
                      <tr>
                      <td align="center">
                            <table>
                            <tr>
                            <td width="175" align="left"><div id="prevArticleButtonDiv"><input id="prevArticleButton" class="button" type="button" value="&lt;&nbsp;Previous article" onClick="previousArticle()" ></div></td>
                            <td width="50" align="center"><div id="articleCounter" style="font-weight:bold;"></div></td>
                            <td width="175" align="right"><div id="nextArticleButtonDiv"><input id="nextArticleButton" class="button" type="button" value="Add another article" onClick="nextArticle()" ></div></td>
                            </tr>
                            </table>
                      </td>
                      </tr>
                      <tr>
                      <td>
                          <textarea cols="80" rows="10" name="articleBody" id="articleBody"></textarea>
                      <br>
                      </td>
                      </tr>
                      <tr>
                      <td align="right">
                          <input id="deleteArticleButton" class="button" type="button" value="Delete article" onClick="deleteArticle(articleNum)" >&nbsp;&nbsp;
                      </td>
                      </tr>
                      </table>
                    </div>
                   <br><br> 

                    <script type="text/javascript">
                      var editor = CKEDITOR.replace('articleBody', {customConfig : '/<?php print($newsletter_tool_dir); ?>js/ckeditorConfig.js'});
                      CKFinder.setupCKEditor( editor, { basePath : '/<?php print($newsletter_tool_dir); ?>ckfinder/', startupPath : 'Images:/<?php echo $_SESSION['regionName'] ?>/'} );
                      viewArticle(articleNum);
                    </script>

                </div> <!-- ends appArticleDiv -->
              </div> <!-- ends tab-2 -->
          </div> <!-- ends tabs -->
        </td>
        </tr>
        </table> <!-- ends appBodyTable -->

    <?php endif; ?>


    </td>
    </tr>
   </table> <!-- ends appOuterTable-->

</div> <!-- ends appContainer div -->

</form>

</body>
</html>
