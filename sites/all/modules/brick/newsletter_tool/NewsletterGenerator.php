<?php
require_once("NewsletterData.php");

/*
 * Class that generates user's articles and events and inserts them in HTML template.
 * A note about CSS: gmail only supports inline styles, so unfortunately some css must
 * be put in here rather than in the template.
 */
class NewsletterGenerator{
  private $newsletterData = null;
  private $templateID = "";
  private $newsletterID = "";
  private $templateStr = "";
  private $articleHeadings = null;
  private $articlesArray = null;

  function __construct($templateID, $newsletterID){
    $this->newsletterData = new NewsletterData();
    $this->templateID = $templateID;
    $this->newsletterID = $newsletterID;

    $templateRec = $this->newsletterData->getTemplate($templateID);
    $this->templateStr = $templateRec['HTML'];
  }


  public function getTemplateString(){
    return $this->templateStr;
  }


  public function getArticleHeadings(){
    if($this->articleHeadings == null){
      $this->articleHeadings = array();
    }

    //put complete place holder in array
    $articlePlaceholders = array();
    preg_match_all("/\[%ARTICLES\[.+\]%\]/", $this->templateStr, $articlePlaceholders);
 
    //extract article headings and put in headings array
    foreach($articlePlaceholders[0] as $p){
      $heading = preg_replace('/\[%ARTICLES\[(.*)\]%\]/', '$1', $p);
      $this->articleHeadings[] = $heading;
    }

    return $this->articleHeadings;
  }


  public function generateHeaderDate(){
   return DateHelper::changeDateFormat($_SESSION['newsletterDate'], DateHelper::FORM_DATE_FORMAT, DateHelper::NEWSLETTER_HEADING_DATE_FORMAT); 
  }


  private function getArticlesArray(){
    if($this->articlesArray == null){

      //A three dimensional array. 
      //First index is section heading. 
      //Second index is array with title and body for each article in that section.
      $this->articlesArray = array();

      $articleHeadings =  $this->getArticleHeadings();
      $articleRecs = $this->newsletterData->getNewsletterArticles($this->newsletterID);

      foreach($articleRecs as $row){
        $articleNum = $row['count'];
        $articleSectionNum = $_SESSION['articleSectionNum' . $articleNum];
        $heading = $articleHeadings[$articleSectionNum - 1];

        if(!isSet($this->articlesArray[$heading]))
          $this->articlesArray[$heading] = array();
         
        array_push($this->articlesArray[$heading], array("title" => $row['title'], "body" => $row['body']));
       }
     } 
     
     return $this->articlesArray;
   }
     
  public function generateArticleSection($heading, $preDivider){
    $articlesArray = $this->getArticlesArray();

    if(isSet($articlesArray[$heading]))
        $sectionArticles = $articlesArray[$heading];
    else 
        return ""; 

    if(count($sectionArticles) == 0)
      return "";
     
    $articleStr = ""; 
    
    if($preDivider)
        $articleStr .= "<hr style='width:100%; height:1px; color:#999999; background-color:#999999; border:0; margin-top:10px; margin-bottom:10px;'>\n";
    
    $articleStr .= "<span style='color:#3573a3; font-size:15px; font-weight:bold; text-transform:uppercase; padding-top:5px; padding-bottom:5px;'>{$heading}</span><br>\n";
      
    for($i = 0; $i < count($sectionArticles); $i++){
      $article = $sectionArticles[$i];
      $articleStr .= "<p>\n";
      
      if(strlen(trim($article["title"])) > 0 ){
          $articleStr .= "<span style='color:#000000; font-size:14px; font-weight:bold; padding-bottom:3px;'>"; 
          $articleStr .= $article["title"];
          $articleStr .= "</span><br>\n";
      }
      
      //replace newlines after paragraph tags that were removed in articleJSFunctions.inc
      $articleStr .= preg_replace("/(<p>|<\/p>)/i", "\n$1\n", $article["body"]);
      $articleStr .= "\n</p>\n";
    }

    return $articleStr;
  }

  public function generateEvents(){
      $eventStr = "";
      
      $eventRecs = $this->newsletterData->getRegionEvents($_SESSION['regionID'], $_SESSION['newsletterDateDB'], $_SESSION['eventsEndDateDB']);
      
      foreach($eventRecs as $row){
        $description =  $_SESSION['descriptionEvent' . $row['nid']];

        //trim description
        if (strlen($description) > 150){
          $description = substr($description, 0, 149) . "... ";
          $_SESSION['descriptionEvent' . $row['nid']] = $description;
        }

        if(in_array($row['nid'],$_SESSION['includeEventIDs'])){
          $eventDateStr = DateHelper::changeDateFormat($row['field_event_date_value'], DateHelper::MYSQL_DATETIME_FORMAT, DateHelper::NEWSLETTER_EVENT_DATE_FORMAT);
          $nameColor = ($row['field_event_type_value'] == "Social") ? "#008000" : "#de712c"; 
          $eventStr .= "<p>\n<div style='font-size:11px; text-align:left;'>\n";
          $eventStr .= "{$eventDateStr}<br>\n";
          $eventStr .= "<a href='http:////v3.onebrick.org/node/{$row['nid']}' style='font-family:verdana, sans-serif; font-size:12px; font-weight:bold; color:{$nameColor}; text-decoration:none;'>";
          $eventStr .= "{$row['title']}";
          $eventStr .= "</a><br>\n"; 
          
          if(in_array($row['nid'],$_SESSION['includeEventDescriptionIDs']))
            $eventStr .= $_SESSION['descriptionEvent' . $row['nid']];
          
          $eventStr .= "\n</div>\n</p>\n";
        }
      }
      
      return $eventStr;
  }
}
?>
