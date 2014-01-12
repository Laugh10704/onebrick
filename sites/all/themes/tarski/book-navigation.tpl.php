<?php
// $Id: book-navigation.tpl.php,v 1.1 2012/01/25 02:43:38 ubuntu Exp $

/**
 * @file
 * Default theme implementation to navigate books. Presented under nodes that
 * are a part of book outlines.
 *
 * Available variables:
 * - $tree: The immediate children of the current node rendered as an
 *   unordered list.
 * - $current_depth: Depth of the current node within the book outline.
 *   Provided for context.
 * - $prev_url: URL to the previous node.
 * - $prev_title: Title of the previous node.
 * - $parent_url: URL to the parent node.
 * - $parent_title: Title of the parent node. Not printed by default. Provided
 *   as an option.
 * - $next_url: URL to the next node.
 * - $next_title: Title of the next node.
 * - $has_links: Flags TRUE whenever the previous, parent or next data has a
 *   value.
 * - $book_id: The book ID of the current outline being viewed. Same as the
 *   node ID containing the entire outline. Provided for context.
 * - $book_url: The book/node URL of the current outline being viewed.
 *   Provided as an option. Not used by default.
 * - $book_title: The book/node title of the current outline being viewed.
 *   Provided as an option. Not used by default.
 *
 * @see template_preprocess_book_navigation()
 */
?>
<?php if ($tree || $has_links): ?>
  <div id="book-navigation-<?php print $book_id; ?>" class="book-navigation">
    <?php print $tree; ?>

    <?php if ($has_links): ?>
    <div class="page-links">
      <?php if ($prev_url) : ?>
        <div class="page-previous">
          <a href="<?php print $prev_url; ?>" title="<?php print t('Go to previous page'); ?>"><?php print t('‹ ') . $prev_title; ?></a>
        </div>
      <?php endif; ?>
      <?php if ($parent_url) : ?>
        <div class="page-up">
          <a href="<?php print $parent_url; ?>" title="<?php print t('Go to parent page'); ?>"><?php print t('up'); ?></a>
        </div>
      <?php endif; ?>
      <?php if ($next_url) : ?>
        <div class="page-next" >
          <a href="<?php print $next_url; ?>" title="<?php print t('Go to next page'); ?>"><?php print $next_title . t(' ›'); ?></a>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
<?php endif; ?>