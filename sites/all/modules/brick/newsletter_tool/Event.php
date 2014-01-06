<?php

class Event {
   const MAX_DESC_LEN = 150;

   public $id;
   public $name = "";
   public $date = "";
   public $description = "";
   public $eventCapacity;
   public $rsvpCount;
   public $maxRsvps;
   public $checked = true;
   public $descriptionChecked = true;

  function __construct($id, $name, $date, $description, $eventCapacity, $rsvpCount, $maxRsvps, $checked, $descriptionChecked){
      $this->id = $id;
      $this->name = $name;
      $this->date = $date;
      $this->description = $description;
      $this->eventCapacity = $eventCapacity;
      $this->rsvpCount = $rsvpCount;
      $this->maxRsvps = $maxRsvps;
      $this->checked = $checked;
      $this->descriptionChecked = $descriptionChecked;
  }

  static function trimDescription($str){
      $str = preg_replace(array('/\n/','/\s\s+/'), array(' ', ' '), $str);
      $strLen = strlen($str);
      
      if($strLen > self::MAX_DESC_LEN){
        $pos = strpos(strrev($str),' ', $strLen - self::MAX_DESC_LEN);
    
        if($pos)
            $str = substr($str, 0, $strLen - $pos - 1) . '...';
      }
      
      return $str;
  }
}
?>
