//Validation function that is a hodge-podge of jquery and regular javascript. 
//Should re-write in all jquery

function validate(stage){
  var isValid = true;
  var errorStr = "Missing or invalid information: ";
  var errorCount = 0;

  var requiredText = new Array();

  if (stage == 1) {
    requiredText.push(["regionID","your region"]);
    
    dateStr = $('#newsletterDate').val();
    
    if(dateStr.search(/^\d{1,2}\/\d{1,2}\/\d{2,4}$/) == -1){
      isValid = false;
      errorCount++;
      errorStr += "newsletter date";
    }

    eventsEndDateStr = $('#eventsEndDate').val();
    
    if(eventsEndDateStr != ""){
      if(eventsEndDateStr.search(/^\d{1,2}\/\d{1,2}\/\d{2,4}$/) == -1){
        isValid = false;
        errorCount++;
        errorStr += "events end date";
      }
    }
  }  

  for(i=0; i<requiredText.length; i++){
    if($('#' + requiredText[i][0]).val().replace(/\s/g,'').length == 0){
      isValid = false;
      errorCount++;

      if(errorCount>1)
        errorStr += ", ";

      errorStr += requiredText[i][1];
    }
  }

  if(!isValid){
    $('#errorBox').text(errorStr);
    return false;
  }

  return true;
}
