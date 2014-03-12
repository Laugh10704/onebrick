<?php
// $Id: page.tpl.php,v 1.2 2013/12/17 20:06:19 jordan Exp jordan $

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
?>

<style type="text/css">
  #header-wrapper #bgImageLeft {
    background: url("<?php print brick_chapter_header(); ?>") no-repeat 0 0;
  }
</style>

<?php if (!(theme_get_setting('header_image') == 'none' && !$site_name && !$site_slogan)): ?>
  <div id="header-wrapper">
    <div id="header">

      <script language="javascript">
        jQuery(document).ready(function () {
          setupPopupForm("#initialForm");
          setupPopupForm("#initialSignupForm");

          var addVolunteerMenuItem = jQuery("li .expanded .leaf a[title=\"Add a new volunteer\"]");

          <?php
            //$detect = mobile_detect_get_object();
            $is_mobile = false;//$detect->isMobile();
            if ($is_mobile) {
                 echo "jQuery('#loginLink').colorbox({inline: true, href: '#currentPopupForm', transition: 'none', width: '400', fixed: true, left: 0, top: 0, title: 'Login', opacity: '0.50', reposition: false});";
                echo "jQuery('#signupLink').colorbox({inline: true, href: '#currentPopupForm', transition: 'none', width: '400', fixed: true, left: 0, top: 0, title: 'Sign Up', opacity: '0.50', reposition: false});";
               echo "jQuery('#newUserLink').colorbox({inline: true, href: '#currentPopupForm', transition: 'elastic', width: '400', fixed: true, left: 0, top: 0, title: 'Sign Up', opacity: '0.50', reposition: false});";
                echo "addVolunteerMenuItem.colorbox({inline: true, href: '#currentPopupForm', transition: 'none', width: '400', fixed: true, left: 0, top: 0, title: 'Add Volunteer', opacity: '0.50', reposition: false});";
            }
            else {
                echo "jQuery('#loginLink').colorbox({inline: true, href: '#currentPopupForm', transition: 'none', width: '350', title: 'Login', opacity: '0.50', reposition: false});";
                echo "jQuery('#signupLink').colorbox({inline: true, href: '#currentPopupForm', transition: 'none', width: '350', title: 'Sign Up', opacity: '0.50', reposition: false});";
                echo "jQuery('#newUserLink').colorbox({inline: true, href: '#currentPopupForm', transition: 'elastic', width: '350', title: 'Sign Up', opacity: '0.50', reposition: false});";
                echo "addVolunteerMenuItem.colorbox({inline: true, href: '#currentPopupForm', transition: 'none', width: '350', title: 'Add Volunteer', opacity: '0.50', reposition: false});";
            }
          ?>

          addVolunteerMenuItem.click(function () {
            resetPopupForm('#initialSignupForm', 'formWrapper');
            // mark that this is an admin adding a volunteer - not the person themselves
            jQuery("#brick-create-account-form #isAdminSubmit").val("YES");
          });

          // Also make the banner a link
          jQuery('#header').wrap('<a href="/" />');

          // disable parent links by making them go nowhere. This allows sub-menus to work on mobile as well
          jQuery("#header-menu .content li.expanded > a").attr("href", "#");
        });

        function toSignupForm() {
          resetPopupForm('#initialSignupForm', 'formWrapper');
          jQuery.colorbox.resize();
          jQuery("#cboxTitle").text("Create Account");
        }
      </script>

      <!-- aligned to left side -->
      <div id="bgImageLeft">

      </div>

      <!-- aligned to rightSide side -->
      <div id="bgImageRight">
        <div id='loginArea'>
          <div class="loginSpacer"></div>
          <?php
          if (user_is_logged_in()) {
            global $user;

            $loadedUser = user_load($user->uid);
            $fullname = $loadedUser->signature;
            $names = explode(" ", $fullname);
            $chapterId = $_SESSION['CHAPTER'];

            echo "<div class='welcomeMessage'>Welcome " . "<a href='/user'>" . $names[0] . "!</a></div>";
            echo "<a id='logoutLink' href=\"/user/logout?destination=/chapters/$chapterId\">Log out</a>";
          }
          else {
            ?>
            <div>
              <a id="loginLink" onclick="resetPopupForm('#initialForm', 'formWrapper')">Login</a>
            </div>
            <a id="signupLink" onclick="resetPopupForm('#initialSignupForm', 'formWrapper')">Sign Up</a>
            <script language='javascript'>
              jQuery('a:contains("My One Brick")').css('display', 'none');
            </script>
          <?php
          }
          ?>
        </div>
      </div>

      <div style="display:none">
        <div id="initialForm">
          <?php $form = drupal_get_form('brick_login_form');
          print drupal_render($form); ?>
        </div>
        <div id="initialSignupForm">
          <?php $form = drupal_get_form('brick_create_account_form');
          print drupal_render($form); ?>
        </div>
      </div>

      <?php if ($site_name || $site_slogan): ?>
        <div id="branding">
          <?php if ($site_name): ?>
            <?php if ($title): ?>
              <div id="site-name"<?php if (!$site_slogan): ?> class="no-slogan"<?php endif; ?>>
                <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
                   rel="home"><?php print $site_name; ?></a>
              </div>
            <?php else: /* Use h1 when the content title is empty */ ?>
              <h1 id="site-name"<?php if (!$site_slogan): ?> class="no-slogan"<?php endif; ?>>
                <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
                   rel="home"><?php print $site_name; ?></a>
              </h1>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($site_slogan): ?>
            <div
              id="site-slogan"<?php if (!$site_name): ?> class="no-site-name"<?php endif; ?>><?php print $site_slogan; ?></div>
          <?php endif; ?>
        </div> <!-- /#branding -->
      <?php endif; ?>

    </div>
    <!-- /#header -->
  </div> <!-- /#header-wrapper -->
<?php endif; ?>

<?php if ($page['header_menu']): ?>
  <div id="header-menu-wrapper">
    <div id="header-menu">
      <?php print render($page['header_menu']); ?>
    </div>
  </div>
<?php endif; ?>

<div id="main-wrapper">
  <div id="main">

    <div id="main-columns" class="clearfix">
      <?php
      $column = 0;
      for ($n = -2; $n <= 2; $n++) {
        foreach (array('content', 'sidebar-first', 'sidebar-second') as $a) {
          if ($weight[$a] == $n) {
            include 'page-' . $a . '.tpl.php';
          }
        }
      }
      ?>
    </div>
    <!-- /#main-columns -->

  </div>
  <!-- /#main -->
</div> <!-- /#main-wrapper -->

<div id="footer-wrapper">
  <div id="footer">

    <?php if ($page['footer_column_first'] || $page['footer_column_second'] || $page['footer_column_third'] || $page['footer_column_fourth']): ?>
      <h2 class="element-invisible"><?php print t('Footer'); ?></h2>
      <div id="footer-columns" class="columns-<?php print $footer_columns_number; ?>">
        <?php if ($page['footer_column_first']): ?>
          <div id="footer-column-first"
               class="column first<?php if (!$page['footer_column_second'] && !$page['footer_column_third'] && !$page['footer_column_fourth']): ?> last<?php endif; ?>">
            <?php print render($page['footer_column_first']); ?>
          </div> <!-- /#footer-column-first -->
        <?php endif; ?>

        <?php if ($page['footer_column_second']): ?>
          <div id="footer-column-second"
               class="column <?php if (!$page['footer_column_first']): ?> first<?php endif; ?><?php if (!$page['footer_column_third'] && !$page['footer_column_fourth']): ?> last<?php endif; ?>">
            <?php print render($page['footer_column_second']); ?>
          </div> <!-- /#footer-column-second -->
        <?php endif; ?>

        <?php if ($page['footer_column_third']): ?>
          <div id="footer-column-third"
               class="column <?php if (!$page['footer_column_first'] && !$page['footer_column_second']): ?> first<?php endif; ?><?php if (!$page['footer_column_fourth']): ?> last<?php endif; ?>">
            <?php print render($page['footer_column_third']); ?>
          </div> <!-- /#footer-column-third -->
        <?php endif; ?>

        <?php if ($page['footer_column_fourth']): ?>
          <div id="footer-column-fourth"
               class="column last <?php if (!$page['footer_column_first'] && !$page['footer_column_second'] && !$page['footer_column_third']): ?> first<?php endif; ?>">
            <?php print render($page['footer_column_fourth']); ?>
          </div> <!-- /#footer-column-fourth -->
        <?php endif; ?>
      </div><!-- /#footer-columns -->
    <?php endif; ?>

    <div id="closure">

      <div id="info" style="display: none">
        <span id="copyright"><?php print theme_get_setting('copyright_information'); ?></span>Theme by <a
          href="http://www.kiwi-themes.com">Kiwi Drupal Themes</a>, based on <a href="http://tarskitheme.com/about/">Tarski</a>
        project.
      </div>

      <?php if ($page['footer_menu']): ?>
        <div id="footer-menu">
          <?php print render($page['footer_menu']); ?>
        </div> <!-- /#footer-menu -->
      <?php endif; ?>

    </div>
    <!-- /#closure -->

  </div>
  <!-- /#footer -->
</div> <!-- /#footer-wrapper -->


