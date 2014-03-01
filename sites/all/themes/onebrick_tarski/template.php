<?php
// $Id: template.php,v 1.4 2013/03/17 02:33:21 jordan Exp $

/**
 * Implements hook_css_alter().
 */
function onebrick_tarski_css_alter(&$css) {
  unset($css[drupal_get_path('module', 'system') . '/admin.css']);
  unset($css[drupal_get_path('module', 'system') . '/admin-rtl.css']);
  unset($css[drupal_get_path('module', 'aggregator') . '/aggregator.css']);
  unset($css[drupal_get_path('module', 'aggregator') . '/aggregator-rtl.css']);
  unset($css[drupal_get_path('module', 'block') . '/block.css']);
  unset($css[drupal_get_path('module', 'block') . '/block-rtl.css']);
  unset($css[drupal_get_path('module', 'book') . '/book.css']);
  unset($css[drupal_get_path('module', 'book') . '/book-rtl.css']);
  unset($css[drupal_get_path('module', 'comment') . '/comment.css']);
  unset($css[drupal_get_path('module', 'comment') . '/comment-rtl.css']);
  unset($css[drupal_get_path('module', 'dashboard') . '/dashboard.css']);
  unset($css[drupal_get_path('module', 'dashboard') . '/dashboard-rtl.css']);
  unset($css[drupal_get_path('module', 'dblog') . '/dblog.css']);
  unset($css[drupal_get_path('module', 'dblog') . '/dblog-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/defaults.css']);
  unset($css[drupal_get_path('module', 'system') . '/defaults-rtl.css']);
  unset($css[drupal_get_path('module', 'field') . '/theme/field.css']);
  unset($css[drupal_get_path('module', 'field') . '/theme/field-rtl.css']);
  unset($css[drupal_get_path('module', 'filter') . '/filter.css']);
  unset($css[drupal_get_path('module', 'filter') . '/filter-rtl.css']);
  unset($css[drupal_get_path('module', 'field_ui') . '/field_ui.css']);
  unset($css[drupal_get_path('module', 'field_ui') . '/field_ui-rtl.css']);
  unset($css[drupal_get_path('module', 'forum') . '/forum.css']);
  unset($css[drupal_get_path('module', 'forum') . '/forum-rtl.css']);
  unset($css[drupal_get_path('module', 'locale') . '/locale.css']);
  unset($css[drupal_get_path('module', 'locale') . '/locale-rtl.css']);
  unset($css[drupal_get_path('module', 'node') . '/node.css']);
  unset($css[drupal_get_path('module', 'node') . '/node-rtl.css']);
  unset($css[drupal_get_path('module', 'openid') . '/openid.css']);
  unset($css[drupal_get_path('module', 'openid') . '/openid-rtl.css']);
  unset($css[drupal_get_path('module', 'poll') . '/poll.css']);
  unset($css[drupal_get_path('module', 'poll') . '/poll-rtl.css']);
  unset($css[drupal_get_path('module', 'search') . '/search.css']);
  unset($css[drupal_get_path('module', 'search') . '/search-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.css']);
  unset($css[drupal_get_path('module', 'system') . '/system-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.base.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.base-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.behavior.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.behavior-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.theme.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.theme-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.menus.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.menus-rtl.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.messages.css']);
  unset($css[drupal_get_path('module', 'system') . '/system.messages-rtl.css']);
  unset($css[drupal_get_path('module', 'user') . '/user.css']);
  unset($css[drupal_get_path('module', 'user') . '/user-rtl.css']);
  unset($css['misc/vertical-tabs.css']);
  unset($css['misc/vertical-tabs-rtl.css']);
}

function onebrick_tarski_html_head_alter(&$head_elements) {
  // Remove metadata
  unset($head_elements['system_meta_generator']);
  
  // Remove metadata that exposes user's email address.
  unset($head_elements['rdf_user_username']);  
}

function onebrick_tarski_username_alter(&$name, $account) {
  // Display the user's name instead of email address
  $user = user_load($account->uid);
  $name = brick_format_name($user->field_user_fullname['und'][0]['safe_value']);
}

function tarski_menu_tree($variables) {
  return '<ul class="menu">' . $variables['tree'] . '</ul><div class="divForClear"></div>';
}


/**
 * Implements template_preprocess_html().
 */
function tarski_preprocess_html(&$variables) {
  // Add reset CSS
  drupal_add_css($data = path_to_theme() . '/reset.css', $options['type'] = 'file', $options['weight'] = CSS_SYSTEM - 1);

  // Add conditional stylesheet for IEs
  drupal_add_css(path_to_theme() . '/ie8.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'lte IE 8', '!IE' => FALSE), 'preprocess' => FALSE));
  drupal_add_css(path_to_theme() . '/ie7.css', array('group' => CSS_THEME, 'browsers' => array('IE' => 'lte IE 7', '!IE' => FALSE), 'preprocess' => FALSE)); 
}

/**
 * Implements template_preprocess_page().
 */
function tarski_preprocess_page(&$variables) {
   // Add variables with weight value for each main column
  $variables['weight']['content'] = 0;
  $variables['weight']['sidebar-first'] = 'disabled';
  $variables['weight']['sidebar-second'] = 'disabled';
  if ($variables["page"]["sidebar_first"]) {
    $variables['weight']['sidebar-first'] = theme_get_setting('sidebar_first_weight');
  }
  if ($variables["page"]["sidebar_second"]) {
    $variables['weight']['sidebar-second'] = theme_get_setting('sidebar_second_weight');
  }

  // Add $main_columns_number variable (used in page-*.tpl.php files)
  $columns = 0;
  foreach (array('content', 'sidebar_first', 'sidebar_second') as $n) {
    if ($variables["page"]["$n"]) {
      $columns++;
    }
  }
  $variables['main_columns_number'] = $columns;  

  // Add $footer_columns_number variable to page.tpl.php file
  $columns = 0;
  foreach (array('first', 'second', 'third', 'fourth') as $n) {
    if ($variables["page"]["footer_column_$n"]) {
      $columns++;
    }
  }
  $variables['footer_columns_number'] = $columns;

  // Generate dynamic styles
  $base_font_size = theme_get_setting('base_font_size');
  if ($variables['main_columns_number'] == 1) {
    $layout_min_width = theme_get_setting('layout_1_min_width');
    $layout_max_width = theme_get_setting('layout_1_max_width');
  }
  if ($variables['main_columns_number'] == 2) {
    $layout_min_width = theme_get_setting('layout_2_min_width');
    $layout_max_width = theme_get_setting('layout_2_max_width');
  }
  if ($variables['main_columns_number'] == 3) {
    $layout_min_width = theme_get_setting('layout_3_min_width');
    $layout_max_width = theme_get_setting('layout_3_max_width');
  }
  $dynamic_styles = "html { font-size: $base_font_size; } #header, #header-menu, #main, #footer { width: 100%; min-width: $layout_min_width; max-width: $layout_max_width;}";
  drupal_add_css($data = $dynamic_styles, $options['type'] = 'inline', $options['preprocess'] = TRUE );
}

/**
 * Implements template_preprocess_block().
 */
function tarski_preprocess_block(&$variables) {
  // Remove "block" class from blocks in "Main page content" region
  if ($variables['elements']['#block']->region == 'content') {
    foreach ($variables['classes_array'] as $key => $val) {
      if ($val == 'block') {
        unset($variables['classes_array'][$key]);
      }
    }
  }
  $variables['classes_array'][] = 'block-content';
}

/**
 * Overrides theme_messages().
 */
function tarski_messages($variables) {

  // If there are serveral messages, print them in separate divs.
  $display = $variables['display'];
  $output = '';
  $status_heading = array(
    'status' => t('Status message'),
    'error' => t('Error message'),
    'warning' => t('Warning message'),
  );
  foreach (drupal_get_messages($display) as $type => $messages) {
    if (!empty($status_heading[$type])) {
      $output .= '<h2 class="element-invisible">' . $status_heading[$type] . "</h2>\n";
    }
    foreach ($messages as $message) {
      $output .= "<div class=\"messages message-$type\">\n";
      $output .= $message;
      $output .= "</div>\n";
    }
  }
  return $output;
}


/**
 * Overrides theme_node_recent_block().
 */
function tarski_node_recent_block($variables) {

  // Make output for "Recent content" block consistent with other blocks
  $output = '';
  foreach ($variables['nodes'] as $node) {
    $items[] = theme('node_recent_content', array('node' => $node));
  }
  if (user_access('access content overview')) {
    $items[] = theme('more_link', array('url' => url('admin/content'), 'title' => t('more ›')));
  }  
  return theme('item_list', array('items' => $items));
}

/**
 * Overrides theme_node_recent_content().
 */
function tarski_node_recent_content($variables) {

  // Make output for "Recent content" block consistent with other blocks
  $node = $variables['node'];
  $output = l($node->title, 'node/' . $node->nid);
  $output .= theme('mark', array('type' => node_mark($node->nid, $node->changed)));
  return $output;
}

/**
 * Overrides theme_tablesort_indicator().
 */
function tarski_tablesort_indicator($variables) {
  // Use custom arrow images
  if ($variables['style'] == "asc") {
    return theme('image', array('path' => path_to_theme() . '/images/arrow-up-white.png', 'alt' => t('sort ascending'), 'title' => t('sort ascending')));
  }
  else {
    return theme('image', array('path' => path_to_theme() . '/images/arrow-down-white.png', 'alt' => t('sort descending'), 'title' => t('sort descending')));
  }
}

/**
 * Overrides theme_more_link().
 */
function tarski_more_link($variables) {
  // Append arrow
  return '<div class="more-link">' . t('<a href="@link" title="@title">more ›</a>', array('@link' => check_url($variables['url']), '@title' => $variables['title'])) . '</div>';
}

/**
 * Override of theme_pager().
 */
function tarski_pager($variables) {
  // Reimplement the pager
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $pager_width = theme_get_setting('trim_pager');
  global $pager_page_array, $pager_total;
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $total_number_of_pages = $pager_total[$element];
  $current_page_number = $pager_page_array[$element] + 1;

  /* If there is just one page we don't need a pager. */
  if ($total_number_of_pages <= 1) {
    return;
  }

  /* Elipsis does not make sense if there is just one page more than the pager width. */
  if ($total_number_of_pages - $pager_width == 1) {
    $pager_width++;
  }

  /* Genarate pager without any elipsis */
  if ($total_number_of_pages <= $pager_width) {
    if ($li_previous) {
      $items[] = array('class' => array('pager-previous'), 'data' => $li_previous,);
    }
    for ($i = 1; $i <= $total_number_of_pages; $i++) {
      if ($i < $current_page_number) {
        $items[] = array('class' => array('pager-item'), 'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($current_page_number - $i), 'parameters' => $parameters)) );
      }
      if ($i == $current_page_number) {
        $items[] = array('class' => array('pager-current'), 'data' => $i);
      }
      if ($i > $current_page_number) {
        $items[] = array('class' => array('pager-item'), 'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $current_page_number), 'parameters' => $parameters)) );
      }
    }
    if ($li_next) {
      $items[] = array('class' => array('pager-next'), 'data' => $li_next );
    }
  }

  /* Genarate pager with elipsis */
  if ($total_number_of_pages > $pager_width) {

    /* Genarate pager with elpisis on right side. */
    if ($current_page_number < $pager_width) {
      if ($li_previous) {
        $items[] = array('class' => array('pager-previous'), 'data' => $li_previous,);
      }
      for ($i = 1; $i <= $pager_width; $i++) {
        if ($i < $current_page_number) {
          $items[] = array('class' => array('pager-item'), 'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($current_page_number - $i), 'parameters' => $parameters)) );
        }
        if ($i == $current_page_number) {
          $items[] = array('class' => array('pager-item pager-current'), 'data' => $i);
        }
        if ($i > $current_page_number) {
          $items[] = array('class' => array('pager-item'), 'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $current_page_number), 'parameters' => $parameters)) );
        }
      }
      $items[] = array(
        'class' => array('pager-ellipsis'),
        'data' => '…',
      );
      $items[] = array('class' => array('pager-item'), 'data' => theme('pager_last', array('text' => $total_number_of_pages, 'element' => $element, 'interval' => 1, 'parameters' => $parameters)) );
      $items[] = array('class' => array('pager-next'), 'data' => $li_next );
    }

    /* Genarate pager with elpisis on both sides. */
    if($current_page_number >= $pager_width && $current_page_number <= $total_number_of_pages - $pager_width + 1) {
      if ($li_previous) {
        $items[] = array('class' => array('pager-previous'), 'data' => $li_previous,);
      }
      $items[] = array('class' => array('pager-item'), 'data' => theme('pager_first', array('text' => "1", 'element' => $element, 'interval' => 1, 'parameters' => $parameters)) );
      $items[] = array(
        'class' => array('pager-ellipsis'),
        'data' => '…',
      );
      function isEven($num){
        return ($num%2) ? TRUE : FALSE;
      }
      if (isEven($pager_width) == TRUE) {
        $a = floor($pager_width/2);
      }
      if (isEven($pager_width) == FALSE) {
        $a = floor($pager_width/2) - 1;
      }
      for ($i = $current_page_number - $a; $i <= $current_page_number + floor($pager_width/2); $i++) {
        if ($i < $current_page_number) {
          $items[] = array('class' => array('pager-item'), 'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($current_page_number - $i), 'parameters' => $parameters)) );
        }
        if ($i == $current_page_number) {
          $items[] = array('class' => array('pager-current'), 'data' => $i);
        }
        if ($i > $current_page_number) {
          $items[] = array('class' => array('pager-item'), 'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $current_page_number), 'parameters' => $parameters)) );
        }
      }
      $items[] = array(
        'class' => array('pager-ellipsis'),
        'data' => '…',
      );
      $items[] = array('class' => array('pager-item'), 'data' => theme('pager_last', array('text' => $total_number_of_pages, 'element' => $element, 'interval' => 1, 'parameters' => $parameters)) );
      $items[] = array('class' => array('pager-next'), 'data' => $li_next );
    }

    /* Genarate pager with elpisis on left side. */
    if($current_page_number >= $pager_width && $current_page_number > $total_number_of_pages - $pager_width + 1) {
      if ($li_previous) {
        $items[] = array('class' => array('pager-previous'), 'data' => $li_previous,);
      }
      $items[] = array('class' => array('pager-item'), 'data' => theme('pager_first', array('text' => "1", 'element' => $element, 'interval' => 1, 'parameters' => $parameters)) );
      $items[] = array(
        'class' => array('pager-ellipsis'),
        'data' => '…',
      );
      for ($i = $total_number_of_pages - $pager_width + 1; $i <= $total_number_of_pages; $i++) {
        if ($i < $current_page_number) {
          $items[] = array('class' => array('pager-item'), 'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($current_page_number - $i), 'parameters' => $parameters)) );
        }
        if ($i == $current_page_number) {
          $items[] = array('class' => array('pager-current'), 'data' => $i);
        }
        if ($i > $current_page_number) {
          $items[] = array('class' => array('pager-item'), 'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $current_page_number), 'parameters' => $parameters)) );
        }
      }
      $items[] = array('class' => array('pager-next'), 'data' => $li_next );
    }
  }

  /* Print generated pager */
  return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array('items' => $items, 'title' => NULL, 'type' => 'ul', 'attributes' => array('class' => array('pager'))));
}

/**
* Implemention of hook theme.
*
* Register custom them functions.
*/


/*
function tarski_theme() {
  global $theme;
  return array(
  'event_node_form' => array(
    'arguments' => array('form' => NULL, 'theme' => $theme),
    'template' => 'event-form'
    ),
  );
}
*/
?>
