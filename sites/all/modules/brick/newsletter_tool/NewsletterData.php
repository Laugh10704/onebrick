<?php
require_once("DBConnection.php");

class NewsletterData{
  private $news_db = null;
  private $drupal_db = null;

  function __construct(){
    $this->news_db = DBConnection::getInstance()->getNewsletterDb();
    $this->drupal_db = DBConnection::getInstance()->getDrupalDb();
  }

  public function getRegions(){
    $sqlStr = "SELECT nid, title FROM node WHERE type='chapter' ORDER BY title";
    return $this->drupal_db->query($sqlStr)->fetchAll();
  }

  public function getRegion($regionID){
    $stmt = $this->drupal_db->prepare("SELECT nid, title FROM node WHERE type='chapter' AND nid=? ORDER BY title");
    $stmt->execute(array($regionID));
    return $stmt->fetch();
  }

  public function getRegionShortname($regionID){
    $stmt = $this->news_db->prepare("SELECT shortname FROM newsletter_templates WHERE region_ID=?");
    $stmt->execute(array($regionID));
    $record = $stmt->fetch();
    return $record['shortname'];
  }

  public function getRegionTemplates($regionID){
    $stmt = $this->news_db->prepare("select * from newsletter_templates where region_ID = ? order by name");
    $stmt->execute(array($regionID));
    return $stmt->fetchAll(); 
  }

  public function getRegionNewsletters($regionID){
    $stmt = $this->news_db->prepare("select max(id) as ID, date from newsletters where region_ID = ? group by date order by date desc limit 5");
    $stmt->execute(array($regionID));
    return $stmt->fetchAll(); 
  }
  
  public function getTemplate($templateID){
    $stmt = $this->news_db->prepare("select * from newsletter_templates where ID = ?");
    $stmt->execute(array($templateID));
    return $stmt->fetch();
  }

  public function getGenericTemplate(){
    $sqlStr = "select * from newsletter_templates where ID = 0";
    return $this->news_db->query($sqlStr)->fetchAll();
  }

  public function getRegionEvents($regionID, $startDate, $endDate){
    $datesClause = "and to_days(field_event_date_value) >= to_days(?) ";

    if($endDate != "")
        $datesClause .= "and to_days(field_event_date_value2) <= to_days(?) ";

    $prepareStmt = "select node.nid, field_event_date_value, node.title, field_event_requested_value, " .
	"field_event_max_rsvp_capacity_value, field_event_status_value, field_event_type_value, " .
	"count(field_data_field_event_chapter.entity_id) as rsvps " .
	"from field_data_field_event_chapter " .
	"inner join field_data_field_event_status on " .
	" field_data_field_event_chapter.entity_id = field_data_field_event_status.entity_id " .
	"left join field_data_field_event_type on " .
	" field_data_field_event_chapter.entity_id = field_data_field_event_type.entity_id " .
	"left join field_data_field_event_max_rsvp_capacity on " .
	" field_data_field_event_chapter.entity_id = field_data_field_event_max_rsvp_capacity.entity_id " .
	"left join field_data_field_event_date on " .
	" field_data_field_event_chapter.entity_id = field_data_field_event_date.entity_id " .
	"left join field_data_field_event_requested on " .
	" field_data_field_event_chapter.entity_id = field_data_field_event_requested.entity_id " .
	"left join node on field_data_field_event_chapter.entity_id = node.nid " .
	"left join field_data_field_rsvp_event on field_data_field_event_chapter.entity_id = field_rsvp_event_nid " .
	"left join field_data_field_rsvp_person on " .
	" field_data_field_rsvp_event.entity_id = field_data_field_rsvp_person.entity_id " .
	"where field_event_chapter_nid = ? " . $datesClause .
	"and field_event_status_value = 'Open' " .
	"and (field_event_type_value = 'Volunteer' or field_event_type_value = 'Social') " .
	"group by field_data_field_event_chapter.entity_id " .
	"order by field_event_date_value, node.title";

    $stmt = $this->drupal_db->prepare($prepareStmt);

    if($endDate != ""){
      $stmt->execute(array($regionID, $startDate, $endDate));
    } else {
      $stmt->execute(array($regionID, $startDate));
    }

    return $stmt->fetchAll();
  }

  public function getRegionArticles($regionID){
    $stmt = $this->news_db->prepare("select n.ID, n.date as articleDate, na.ID as articleID, na.title as articleTitle" .
	" from newsletters as n, newsletter_articles as na" .
        " where n.ID = na.newsletter_ID and n.region_ID = ? order by articleDate desc, articleTitle limit 15");
    $stmt->execute(array($regionID));
    return $stmt->fetchAll();
  }

  public function getNewsletterArticles($newsletterID){
    $stmt = $this->news_db->prepare("select * from newsletter_articles as na where na.newsletter_ID=? order by count"); 
    $stmt->execute(array($newsletterID));
    return $stmt->fetchAll();
  }

  public function getArticle($articleID){
    $stmt = $this->news_db->prepare("select * from newsletter_articles as na where na.ID=?");
    $stmt->execute(array($articleID));
    return $stmt->fetch();
  }

  public function addOrUpdateNewsletter($regionID, $date){
    $stmt = $this->news_db->prepare("select ID from newsletters where region_ID=? and date=?");
    $stmt->execute(array($regionID,$date));
    $rec = $stmt->fetch();

    if($rec != null) {
        return $rec['ID'];
    }

    $stmt = $this->news_db->prepare("insert into newsletters (region_ID, date) values (?,?)");
    $stmt->execute(array($regionID,$date));

    $sqlStr = "select max(ID) from newsletters";
    $newsletterID = $this->news_db->query($sqlStr)->fetchColumn(0);

    return $newsletterID;
  }

  public function deleteNewsletterArticles($newsletterID){
    $stmt = $this->news_db->prepare("delete from newsletter_articles where newsletter_ID=?");
    $stmt->execute(array($newsletterID));
  }

  public function newArticle($newsletterID, $count, $articleTitle, $articleBody){
    $stmt = $this->news_db->prepare("insert into newsletter_articles (newsletter_ID, count, title, body) values (?,?,?,?)");
    $stmt->execute(array($newsletterID, $count, $articleTitle, $articleBody));
  }

  public function setDefaultTemplate($regionID, $templateID){
    $stmt = $this->news_db->prepare("update newsletter_templates set default_template='0' where region_ID=?");  
    $stmt->execute(array($regionID));

    $stmt = $this->news_db->prepare("update newsletter_templates set default_template='1' where ID=?");
    $stmt->execute(array($templateID));
  }

  public function newTemplate($regionID, $templateName, $templateStr){
    $stmt = $this->news_db->prepare("insert into newsletter_templates (region_ID,name,HTML,default_template) values (?,?,?,'0')");
    $stmt->execute(array($regionID, $templateName, $templateStr));

    $sqlStr = "select max(ID) from newsletter_templates";
    $templateID = $this->news_db->query($sqlStr)->fetchColumn(0);

    return $templateID;
  }

  public function deleteTemplate($templateID){
    $stmt = $this->news_db->prepare("delete from newsletter_templates where ID=?");
    $stmt->execute(array($templateID));
  }

}
?>
