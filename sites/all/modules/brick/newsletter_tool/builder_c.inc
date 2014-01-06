<?php
require_once("DBConnection.php");
require_once("NewsletterData.php");
require_once("NewsletterGenerator.php");
require_once("DateHelper.php");
require_once("Event.php");

$newsletterData = new NewsletterData();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && (!isSet($_GET['stage']) || (int) $_GET['stage'] == 1)) {
  
  //$_SESSION['tool'] = 'newsletterBuilder'; 

  $stage = 1;
  $regionRecs = $newsletterData->getRegions();
  $newsletterDefaultDate = date(DateHelper::FORM_DATE_FORMAT);
  $eventsEndDefaultDate = date(DateHelper::FORM_DATE_FORMAT, strtotime('+30 days'));

  unset($_SESSION['eventsArray']);
  unset($_SESSION['includeEventIDs']);
  unset($_SESSION['includeEventDescriptionIDs']);
  foreach ($_SESSION as $key => $value){
     if (preg_match('/(articleSectionNum|existingArticleID|articleTitle|articleBody|descriptionEvent)\d?/', $key))
         unset($_SESSION[$key]);
  } 
  unset($_SESSION['messages']);

} else if (($stage = (int)$_GET['stage']) == 2) {

    //Coming from stage 1 as opposed to making changes
    if (isSet($_POST['regionID'])){
        $_SESSION['regionID'] = $_POST['regionID'];
        $regionRec = $newsletterData->getRegion($_SESSION['regionID']);
        $_SESSION['regionName'] = $regionRec['title'];
        $_SESSION['regionShortName'] = $newsletterData->getRegionShortname($_SESSION['regionID']);

        $_SESSION['templateID'] = $_POST['templateID'];
        $_SESSION['newsletterDate'] = $_POST['newsletterDate'];
        $_SESSION['eventsEndDateDB'] = "";

        if(trim($_POST['eventsEndDate']) != "")
            $_SESSION['eventsEndDateDB'] = DateHelper::changeDateFormat($_POST['eventsEndDate'], DateHelper::FORM_DATE_FORMAT, DateHelper::MYSQL_DATE_FORMAT);

        $_SESSION['newsletterDateDB'] = DateHelper::changeDateFormat($_POST['newsletterDate'],DateHelper::FORM_DATE_FORMAT,DateHelper::MYSQL_DATE_FORMAT);
        $_SESSION['existingNewsletterID'] = ($_POST['existingNewsletterIDs'] == "") ? "" : $_POST['existingNewsletterIDs'];

        if($_SESSION['existingNewsletterID'] != ""){
            $_SESSION['newsletterID'] = $_SESSION['existingNewsletterID'];
            $newsletterArticleRecs = $newsletterData->getNewsletterArticles($_SESSION['existingNewsletterID']);

            //put articles in session, will be loaded into javascript array
            $i = 1;
            foreach($newsletterArticleRecs as $row){
                $_SESSION['articleSectionNum' . $i] = 1; 
                $_SESSION['existingArticleID' . $i] = $row['ID'];
                $_SESSION['articleTitle' . $i] = $row['title'];
                $_SESSION['articleBody' . $i] = $row['body'];
                $i++;
            }
        } else { 
            $_SESSION['newsletterID'] = $newsletterData->addOrUpdateNewsletter($_SESSION['regionID'],$_SESSION['newsletterDateDB']);  
        }

        $eventRecs = $newsletterData->getRegionEvents($_SESSION['regionID'], $_SESSION['newsletterDateDB'], $_SESSION['eventsEndDateDB']);
        $eventsArray = array();

        foreach($eventRecs as $row){
            $checked = true;
            $rsvpCount = 0;
            $description = "";
            $descriptionChecked = true;
    
            if($row['rsvps'] != null)
                $rsvpCount = $row['rsvps'];

            $id = $row['nid'];
	    $node = node_load($id);
            $name = $row['title'];
            $date =  DateHelper::changeDateFormat($row['field_event_date_value'],DateHelper::MYSQL_DATE_FORMAT, DateHelper::DISPLAY_DATE_FORMAT);
            $description = Event::trimDescription(strip_tags($node->body['und'][0]['summary']));
            $eventCapacity = $row['field_event_requested_value'];
            $maxRsvps = $row['field_event_max_rsvp_capacity_value'];
            $event = new Event($id, $name, $date, $description, $eventCapacity, $rsvpCount, $maxRsvps, $checked, $descriptionChecked);
            $eventsArray[] = $event;
	    if ($rsvpCount < $maxRsvps) {
		$_SESSION['includeEventIDs'][] = $id;
	    }
        }
        
        $_SESSION['eventsArray'] = serialize($eventsArray);
    }

    $eventsArray = unserialize($_SESSION['eventsArray']);

    //set checkmarks for and description changes for events
    foreach($eventsArray as $key => $event){
        if(array_key_exists('includeEventIDs', $_SESSION))
            $event->checked = in_array($event->id, $_SESSION['includeEventIDs']) ? true : false;
       
        if(array_key_exists('includeEventDescriptionIDs', $_SESSION))
            $event->descriptionChecked = in_array($event->id, $_SESSION['includeEventDescriptionIDs']) ? true : false;
        
        if(array_key_exists('descriptionEvent' . $event->id, $_SESSION))
            $event->description = $_SESSION['descriptionEvent' . $event->id];

        //put event back in array
        $eventsArray[$key] = $event;
    }

    $_SESSION['eventsArray'] = serialize($eventsArray);

    $allArticleRecs = $newsletterData->getRegionArticles($_SESSION['regionID']);

} else { //submit stage

    //first, remove previous articles from the session
    foreach ($_SESSION as $key => $value){
       if (preg_match('/(articleSectionNum|existingArticleID|articleTitle|articleBody)\d/', $key))
           unset($_SESSION[$key]);
    } 

    //put form in session vars
    foreach ($_POST as $key => $value) {
        $_SESSION[$key] = $value;
    }
  
    //first delete existing articles for this newsletter. 
    //this should eventually be a transaction with adding.
    $newsletterData->deleteNewsletterArticles($_SESSION['newsletterID']);

    for ($articleCount = 1; isSet($_SESSION['articleTitle' . $articleCount]); $articleCount++){
        $articleTitle =  $_SESSION['articleTitle' . $articleCount];
        $articleBody = $_SESSION['articleBody' . $articleCount];
       
        if ($articleBody != "") {
            $newsletterData->newArticle($_SESSION['newsletterID'], $articleCount, $articleTitle, $articleBody);
        }
    }

    header("Location:newsletter-tool-preview");
}
?>
