<?php
require_once('NewsletterData.php');

$msg = "";

if (!isSet($_GET['regionID']) && !isSet($_SESSION['regionID']))
   header("Location: builder.php");

if (isSet($_GET['regionID']))
  $_SESSION['regionID'] = $_GET['regionID'];

define("MAX_FILE_SIZE",100000);

$newsletterData = new NewsletterData();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  if(isSet($_POST['save'])){
      if (isSet($_POST['templateDefault']))
        $newsletterData->setDefaultTemplate($_SESSION['regionID'], $_POST['templateDefault']);  

      if (isSet($_POST['deleteTemplateIDs'])){
        foreach($_POST['deleteTemplateIDs'] as $templateID){
            $newsletterData->deleteTemplate($templateID);
        }
      }
  } else if(isSet($_POST['upload'])){
     $allowedExtensions = array("htm","html");
     $nameParts = explode(".", $_FILES['templateFile']['name']);

     if(strlen(trim($_POST['templateName'])) == 0){
       $msg = "Invalid template name.";
     }else if(!in_array($nameParts[count($nameParts) - 1], $allowedExtensions)){
       $msg = "That type of file is not allowed. Must be an html file.";
     } else {
       $templateStr = file_get_contents($_FILES['templateFile']['tmp_name']);       

       if($_FILES['templateFile']['error'] == UPLOAD_ERR_OK) { 
          $templateID = $newsletterData->newTemplate($_SESSION['regionID'],$_POST['templateName'],$templateStr);

          if(isSet($_POST['setDefault']))
            $newsletterData->setDefaultTemplate($_SESSION['regionID'], $templateID);
       
       } else {
         $msg = "There was a problem uploading your file: ". $_FILES['templateFile']['error'];
       }
     }
  }
}

$templateRecs = $newsletterData->getRegionTemplates($_SESSION['regionID']);
?>
