
// necessary for chrome and the like
jQuery(document).ajaxComplete(function(e, xhr, settings) {
   jQuery.colorbox.resize();
});

jQuery.fn.reloadPage = function(data) {
   location.reload();
};

var initialFormTag = "_initial";
var popupFormDivId = "currentPopupForm";
var popupInternalFormDivId = "internalPopupFormId";


// initialization script for JANRAIN login, retrieved from the JANRAIN site
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};
    
    janrain.settings.tokenUrl='http://onebrick.org/rpx/token_handler';
    janrain.settings.tokenAction='event';

    function isReady() { janrain.ready = true; };
    if (document.addEventListener) {
      document.addEventListener("DOMContentLoaded", isReady, false);
    } else {
      window.attachEvent('onload', isReady);
    }

    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainAuthWidget';

    if (document.location.protocol === 'https:') {
      e.src = 'https://rpxnow.com/js/lib/onebrick/engage.js';
    } else {
      e.src = 'http://widget-cdn.rpxnow.com/js/lib/onebrick/engage.js';
    }

    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();


function janrainWidgetOnload() {
    // gets called after the user logs in with janrain
    janrain.events.onProviderLoginToken.addHandler(function(tokenResponse) {
       jQuery.ajax({
                type: "POST",
                url: "/rpx/brick_token_handler",
                data: "token=" + tokenResponse.token,
                success: function(res) {
			alert("YAY");	
		}
        });
    })
}

function setupPopupForm(initialFormSelector) {
   // replace each id in the original form to have "_initial" on it (to prevent duplicates later)
   jQuery(initialFormSelector).find("*[id]").each(function() {
	jQuery(this).attr("id", jQuery(this).attr("id").replace(initialFormTag, "") + initialFormTag);
   })

   // if our form display wrapper doesnt exist, we create it
   if (jQuery("#" + popupFormDivId).length == 0) {
     jQuery('body').append("<div style='display:none'><div id='" + popupFormDivId + "'><div id='" + popupInternalFormDivId + "'></div></div></div>");
   } 
}

function resetPopupForm(initialFormSelector, initialFormWrapSelector) {
   // clone the original form (with events)
   var clone = jQuery(initialFormSelector + " form").clone(true);
 
   // fix social media links in the clone
   var engage = jQuery(clone).find(".janrainEngage");
   var actual = jQuery("#janrainEngageSignin");

   if (actual.length > 0 && engage.length > 0) {
      engage.bind("click", function() {
         actual.click();
      });
   }

   // wrap it in our form wrapper
   clone = jQuery("<div />", { "class":"formBlock", id:initialFormWrapSelector })
     .append(clone);

   var targetSelector = "#" + popupInternalFormDivId;

   // clear out our destination
   jQuery(targetSelector).empty();
  
   // now copy the form over
   clone.appendTo(targetSelector); 

   // simply remove the _initial parts of the id from our destination.
   jQuery(targetSelector).find("*[id]").each(function() { 
        jQuery(this).attr("id", jQuery(this).attr("id").replace(initialFormTag, "")); 
   })

   var newForm = jQuery(targetSelector + " form");
  
   // now we have to modify Drupal's internal ajax variables to point to the newly cloned elements 
   for (var item in Drupal.ajax) {
      var selector = Drupal.ajax[item].selector;
      if (selector) {
	 var inFormElem = jQuery(selector);

         if (inFormElem) {
	    Drupal.ajax[item].form = newForm;
            Drupal.ajax[item].element = inFormElem[0];
	 }
      }
   }
}

Drupal.ajax.prototype.commands.brick_raw_html = function(ajax, response, status) {
    jQuery(response.selector).html(response.html);
};

Drupal.ajax.prototype.commands.brick_close_colorbox = function(ajax, response, status) {
    jQuery.colorbox.close();
};
