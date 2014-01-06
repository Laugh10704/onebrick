<?php 
define('MINUTE_SECONDS', 60);
define('HOUR_SECONDS', MINUTE_SECONDS * 60);
define('DAY_SECONDS', HOUR_SECONDS * 24); 


class DateHelper {
  
  const FORM_DATE_FORMAT = "m/d/Y";
  const DISPLAY_DATE_FORMAT = "n/j/Y";
  const MYSQL_DATE_FORMAT = "Y-m-d";
  const MYSQL_DATETIME_FORMAT = "Y-m-d H:i:s";
  const NEWSLETTER_HEADING_DATE_FORMAT = "F jS, Y"; 
  const NEWSLETTER_EVENT_DATE_FORMAT = "D, M jS (g:ia)";

  
 /*
  * Changes a date string from one format to another 
  */
  public static function changeDateFormat($dateStr, $fromFormat, $toFormat){
    /* Preferred, but only works in PHP 5.3 
    $dt = DateTime::createFromFormat($format, $dateStr);
    return $dt->format(MYSQL_DATE_FORMAT);
    */
    
    switch($fromFormat){
      case self::FORM_DATE_FORMAT:
      case self::DISPLAY_DATE_FORMAT:
        list($m,$d,$y) = explode("/", $dateStr);
        $toDate = new DateTime("$y-$m-$d");
        break;
      case self:: MYSQL_DATE_FORMAT:
        $toDate = new DateTime($dateStr);
        break;
      case self:: MYSQL_DATETIME_FORMAT:
        $toDate = new DateTime($dateStr);
        break;
    }

    $dateStr = $toDate->format($toFormat);

    return $dateStr;
  }

  /*
  * A convenience method to print the duration between two DateTime's in
  * a nice string like '1 day, 3 hours, 20 minutes.' 
  */
  public static function getDurationStr(DateTime $dStart, DateTime $dEnd){
    
    $numSeconds = $dEnd->getTimestamp() - $dStart->getTimestamp();

    $numDays = floor($numSeconds/DAY_SECONDS);
    $numSeconds -= $numDays * DAY_SECONDS;
    $numHours = floor($numSeconds/HOUR_SECONDS);
    $numSeconds -= $numHours * HOUR_SECONDS;
    $numMinutes = floor($numSeconds/MINUTE_SECONDS);
    $numSeconds -= $numMinutes * MINUTE_SECONDS;

    $timeDuration = "";

    if($numDays > 0){
       $dayStr = $numDays > 1 ? "days" : "day";
       $timeDuration .= "$numDays $dayStr";
    }

    if($numHours > 0){
      if(strlen($timeDuration) > 0) 
         $timeDuration .= ", ";
      
      $hourStr = $numHours > 1 ? "hours" : "hour";
      $timeDuration .= "$numHours $hourStr";
    }

    if($numMinutes > 0){
      if(strlen($timeDuration) > 1) 
         $timeDuration .= ", ";

      $minutesStr = $numMinutes > 1 ? "minutes" : "minute";
      $timeDuration .= "$numMinutes $minutesStr";
    }

    return $timeDuration;
  }  

}
?>
