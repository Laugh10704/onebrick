<?php
require_once("NewsletterGenerator.php");
require_once("DateHelper.php");

$stage = 3; //preview stage

if(isSet($_GET['code']))
  $stage = 4; //show code stage

$newsletterGenerator = new NewsletterGenerator($_SESSION['templateID'],$_SESSION['newsletterID']);
$templateStr = $newsletterGenerator->getTemplateString();
$componentHTML = array();
$componentHTML["date"] = $newsletterGenerator->generateHeaderDate();
$componentHTML["events"] = $newsletterGenerator->generateEvents();
$articleHeadings = $newsletterGenerator->getArticleHeadings();

$i = 1;

foreach($articleHeadings as $heading){
  $preDivider = ($i > 1) ? true : false;
  $articleSection = $newsletterGenerator->generateArticleSection($heading, $preDivider);
  $componentHTML["articles[" . $heading . "]"] = $articleSection;

  if(strlen($articleSection) > 0)
      $i++;
}


foreach(array_keys($componentHTML) as $k){
  $templateStr = str_ireplace("[%" . $k . "%]", $componentHTML[$k], $templateStr);
}

if(isSet($_GET['code'])){
  $fileDir =  'sites/all/modules/brick/newsletter_tool/newsletters/' . $_SESSION['regionShortName'];

  if(!file_exists($fileDir))
      mkdir($fileDir, 0777);

  $filePath = $fileDir . '/' . $_SESSION['regionShortName'] . "_" . $_SESSION['newsletterDateDB'] . '.html';
  $fp = fopen($filePath, 'w');
  fwrite($fp, $templateStr);
  fclose($fp);
}

?>
