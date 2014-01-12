<?php
// $Id: page-content.tpl.php,v 1.3 2013/03/19 04:07:52 crc Exp crc $

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/garland.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */
/*
 * Clive:  Code rtemoved from rigth after the render content_top
      if ($breadcrumb): ?><div id="breadcrumb" class="clearfix"><?php print $breadcrumb; ?></div><?php endif; ?>
   Clive: Code removed from right after the tag main-content

      <?php print render($title_prefix); ?>      
      <?php if ($title && !isset($node)): ?>
        <h1 class="page-title"><?php print $title ?></h1>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
 */
?>
<?php if ($page['content']): ?>

  <?php $column++; ?>
  <div id="content-wrapper">
    <div id="content" class="<?php if ($column == 1): ?>first<?php endif; ?><?php if ($column == $main_columns_number): ?> last<?php endif; ?>">

      <?php print render($page['content_top']); ?>
      <?php if ($messages): ?><div id="messages"><?php print $messages; ?></div><?php endif; ?>
      <?php if ($tabs): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
      <?php print $feed_icons; ?>

      <a id="main-content"></a>
  

<?php print render($page['help']); ?>
      <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>

			<?php  // Expand One Brick Variables, skip edit page it's too confusing!
      if (strpos($_SERVER['REQUEST_URI'], "/edit")) {
				print render($page['content']);
			}
			else {
				$event = node_load(32901); // debug load SF Chapter Lead Event for debug
				print brick_expand(render($page['content']), $event);  // Clive edit
			}
			?>
      
      <?php print render($page['content_bottom']); ?>
    </div> <!-- /#content -->
  </div> <!-- /#content-wrapper -->
<?php endif; ?>