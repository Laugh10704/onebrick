<?php

require('NewsletterData.php');
require('emailTest.inc');
require("DateHelper.php");

$newsletterData = new NewsletterData();


if($_GET['f'] == "getTemplates"){
  if($_GET['regionID'] != ""){
    $templateRecs = $newsletterData->getRegionTemplates($_GET['regionID']);
    $selectStr = "<select name='templateID' id='templateID'>";

    foreach($templateRecs as $row){
      if($row['default_template'] == '1')
         $selectStr = $selectStr . "<option value='" . $row['ID'] . "' selected>" . $row['name'] . "</option>";
      else
         $selectStr = $selectStr . "<option value='" . $row['ID'] . "'>" . $row['name'] . "</option>";
    }

    $selectStr .= "</select>";
    $selectStr  = $selectStr . "&nbsp;&nbsp;&nbsp;<a href='/webmgr/newsletter-tool-manage-templates?regionID=" . $_GET['regionID'] . "' class='uiLink'>Manage your templates</a>"; 
    echo $selectStr;
  } else {
    echo "<select></select>";
  }

} else if($_GET['f'] == "getExistingNewsletters"){
  if($_GET['regionID'] != ""){
    $newsletterRecs = $newsletterData->getRegionNewsletters($_GET['regionID']);
    $selectStr = "<select name='existingNewsletterIDs' id='existingNewsletterIDs'>";
    $selectStr .= "<option></option>";

    foreach($newsletterRecs as $row){
        $dateTime = new DateTime($row['date']);
        $dateStr = $dateTime->format(DateHelper::FORM_DATE_FORMAT);
        $selectStr = $selectStr . "<option value='" . $row['ID'] . "'>" . $dateStr . "</option>";
    }

    $selectStr .= "</select>";
    echo $selectStr;
  } else {
    echo "<select></select>";
  }

} else if($_GET['f'] == "getExistingArticles"){
  if(array_key_exists('regionID', $_SESSION)){

    $articleRecs = $newsletterData->getRegionArticles($_SESSION['regionID']);
    $selectStr = "<select name='existingArticleID' id='existingArticleID' class='largeSelect'>";
    $selectStr .= "<option value=''>None selected</option>";

    foreach($articleRecs as $row){
        $dateTime = new DateTime($row['articleDate']);
        $dateStr = $dateTime->format(DateHelper::DISPLAY_DATE_FORMAT);
        $selectStr = $selectStr . "<option value='" . $row['articleID'] . "'>" . $dateStr . "&nbsp;&nbsp;&nbsp;&nbsp;" . $row['articleTitle'] . "</option>";
    }

    $selectStr .= "</select>";
    echo $selectStr;

  } else {
    echo "<select class='largeSelect'></select>";
  }

} else if($_GET['f'] == "getArticleContent" && $_GET['articleID'] != ""){
  $articleRec = $newsletterData->getArticle($_GET['articleID']);
  $articleArr = array("title" => $articleRec['title'], "body" => $articleRec['body']);
  echo json_encode($articleArr);

} else if($_POST['f'] == "sendNewsletter"){
  echo sendEmail($_POST['toEmail'], $_POST['subject'], $_POST['newsletterHTML']);
}

?>
