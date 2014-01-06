newsletter_tool_ajax_file = "/webmgr/newsletter-tool-ajax";

(function($) {
    $(document).ready(function() {
        $('#errorBox')
             .ajaxError(function(evt, xhr, ajaxOptions, error) {
                $(this)
                  .html( 'An error occurred retrieving data: ' + ( xhr ? xhr.status : '' )
                              + ' ' + ( error ? error :'Unknown' ) )
                  .show();
             })
             .ajaxSuccess(function() {
                 //$(this).hide();
              });

        $('#regionID').change(function() {
            $.ajax({
                url: newsletter_tool_ajax_file,
                data: {f: 'getTemplates' , regionID: $('#regionID').val()},
                dataType: 'html',
                success: function(html) {
                    $('#templatesDiv').html(html);
                }
            });
            $.ajax({
                url: newsletter_tool_ajax_file,
                data: {f: 'getExistingNewsletters' , regionID: $('#regionID').val()},
                dataType: 'html',
                success: function(html) {
                    $('#existingNewslettersDiv').html(html);
                    $('#existingNewsletterIDs').bind('change', function(){
                        $('#newsletterDate').attr('value', $('#existingNewsletterIDs option:selected').text());
                    });
                }
            });
        });

        $('#existingArticleStatus').show();
        $.ajax({
            url: newsletter_tool_ajax_file,
            data: {f: 'getExistingArticles'},
            dataType: 'html',
            success: function(html) {
                $('#existingArticlesDiv').html(html);
                $('#existingArticleID').bind('change',function() {
                    $.ajax({
                      url: newsletter_tool_ajax_file,
                      data: {f: 'getArticleContent', articleID: $('#existingArticleID').val()},
                      dataType: 'json',
                      success: function(json) {
                        $('#articleTitle').attr('value', json.title); 
                        editor.setData(json.body);
                      }       
                    });
                });

            }
        });
        $('#existingArticleStatus').hide();
        
        
        $('#sendNewsletter').click(function() {
            $.ajax({
                type: 'POST',
                url: newsletter_tool_ajax_file,
                data: {f: 'sendNewsletter', toEmail: $('#toEmail').val(), subject: 'Your test newsletter', newsletterHTML: $('#newsletterHTML').val()},
                dataType: 'html',
                success: function(html) {
                    $('#emailMsg').html(html);
                }
            });
        });
    });
})(jQuery);

