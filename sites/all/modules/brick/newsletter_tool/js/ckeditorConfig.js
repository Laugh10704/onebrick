/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{

config.toolbar = 'ArticleBody';

config.toolbar_ArticleBody =
[
    ['Cut','Copy','Paste'],
    ['Undo','Redo','-','Find','Replace'],
    ['NumberedList','BulletedList','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight'],
    ['SpecialChar'],['Image'],
    '/',
    ['Font','FontSize','-','Bold','Italic','-','TextColor','BGColor',],
    ['Link','Unlink','Anchor']
];
  
config.font_names = 'Arial;Lucida Sans Unicode;Times New Roman;Trebuchet;Verdana';  
config.font_defaultLabel = 'Verdana';
config.fontSize_defaultLabel = '12px';
config.resize_enabled = false;
config.toolbarCanCollapse = false;
config.height = '325px';
config.width = '500px';

//use <br> instead of <p> when hitting enter
config.enterMode = CKEDITOR.ENTER_BR;
}

/*
 * Customize the default settings in the dialog boxes.
 */
CKEDITOR.on( 'dialogDefinition', function( ev ) {
    // Take the dialog name and its definition from the event data
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;
    
    if ( dialogName == 'link' ) {
        dialogDefinition.removeContents('upload');

        var infoTab = dialogDefinition.getContents( 'info' );
        infoTab.remove('browse');

        var advancedTab = dialogDefinition.getContents('advanced');
        var linkStyle = advancedTab.get('advStyles');
        linkStyle['default'] = 'font-weight:bold; color:#DE712C; text-decoration:none';
    }

    if ( dialogName == 'image' ) {
        dialogDefinition.removeContents('Upload');

        var infoTab = dialogDefinition.getContents('info');
        var hspace = infoTab.get('txtHSpace');
        var vspace = infoTab.get('txtVSpace');
        hspace['default'] = '5';
        vspace['default'] = '3';
    }
});

