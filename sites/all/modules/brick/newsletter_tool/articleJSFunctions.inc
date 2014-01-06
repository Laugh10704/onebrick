<script type="text/javascript">
var articleNum = 1;
var articleValues = new Array();

var editor = null;

<?php
function escapeSingleQuotes($str){
  return preg_replace("/'/","\'",$str);
}

function array_get_default($array, $idx, $default){
	if (array_key_exists($idx, $array))
		return $array[$idx];
	return $default;
}

//Fill javascript array with article values in session
for($i=1; isSet($_SESSION['articleBody' . $i]); $i++){
  print "articleValues[$i] = new Array();";
  print "articleValues[$i]['articleSectionNum'] = '" . array_get_default($_SESSION, 'articleSectionNum' . $i, '') . "';\n";
  print "articleValues[$i]['existingArticleID'] = '" . array_get_default($_SESSION, 'existingArticleID' . $i, '') . "';\n";
  print "articleValues[$i]['articleTitle'] = '" . escapeSingleQuotes($_SESSION['articleTitle' . $i]) . "';\n";
  print "articleValues[$i]['articleBody'] = '" . $_SESSION['articleBody' . $i] . "';\n";
}
?>

function viewArticle(num){
  //new article
  if(articleValues[num] == null){
    sectionNum = 1;
    
    //pre-fill with section number of previous article if it exists
    if(articleValues[num - 1] != null)
      sectionNum = articleValues[num - 1]['articleSectionNum'];

    articleValues[num] = new Array();
    articleValues[num]['articleSectionNum'] = sectionNum;
    articleValues[num]['existingArticleID'] = "";
    articleValues[num]['articleTitle'] = "";
    articleValues[num]['articleBody'] = "";
  }
  
  //set form values for article from js array
  $('#articleHeading').html('Article ' + num);
  $('#articleSectionNum').val(articleValues[num]['articleSectionNum']); 
  $('#existingArticleID').val(articleValues[num]['existingArticleID']); 
  $('#articleTitle').attr('value', articleValues[num]['articleTitle']); 
  editor.setData(articleValues[num]['articleBody']);

  //set nav buttons
  if(num == 1)
    $('#prevArticleButtonDiv').attr('style','display:none;');
  else
    $('#prevArticleButtonDiv').attr('style','display:inline;');

  if(num == articleValues.length - 1) //first article has index 1
    $('#nextArticleButton').attr('value', 'Add another article >');
  else
    $('#nextArticleButton').attr('value', 'Next article >');

  $('#articleCounter').text('#' + num + '/' + (articleValues.length - 1) ); 
}

function storeArticle(num){
  articleValues[num]['articleSectionNum'] = $('#articleSectionNum').val();
  articleValues[num]['existingArticleID'] = $('#existingArticleID').val();
  articleValues[num]['articleTitle'] = $('#articleTitle').val();
  articleValues[num]['articleBody'] = editor.getData(); 
}

function nextArticle(){
  storeArticle(articleNum);
  viewArticle(++articleNum);
}

function previousArticle(){
  if (articleNum > 1){
     storeArticle(articleNum);
     viewArticle(--articleNum);
  }
}

function deleteArticle(num){
    var numArticles = articleValues.length - 1; //index starts at 1
    articleValues.splice(num, 1);
    viewArticle((num == numArticles && num != 1) ? --articleNum : articleNum); 
}

/*
Stores all the article fields in the form as hidden fields, 
first checking if that hidden element for that article num is already there.
Otherwise it updates that element.
*/
function addArticlesToForm(){
  for(i = 1; articleValues[i] != null; i++){
     articleTitle = articleValues[i]['articleTitle'].replace(/\s\s+/g," ").replace(/"/g,"\\\"");
     articleBody = articleValues[i]['articleBody'].replace(/\s\s+/g," ").replace(/\n/g,"");
     articleSectionNum = articleValues[i]['articleSectionNum'];

     if($('#articleTitle' + i).length == 0){
       $('<input type="hidden" />').attr({name: 'articleTitle' + i, value: articleTitle}).appendTo('form');
     } else {
       $('#articleTitle' + i).attr('value', articleTitle);
     } 

     if($('#articleSectionNum' + i).length == 0){
       $('<input type="hidden" />').attr({name: 'articleSectionNum' + i, value: articleSectionNum}).appendTo('form');
     } else {
       $('#articleSectionNum' + i).attr('value', Section);
     } 

     if($('#articleBody' + i).length == 0){
       $('<input type="hidden" />').attr({name: 'articleBody' + i, value: articleBody}).appendTo('form');
     } else {
       $('#articleBody' + i).attr('value', articleBody);
     } 
  }
}

</script>

