<?php
/**
 * @file
 * cts.features.inc
 */

/**
 * Implements hook_node_info().
 */
function cts_node_info() {
  $items = array(
    'chapter' => array(
      'name' => t('Chapter'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'event' => array(
      'name' => t('Event'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'group' => array(
      'name' => t('Group'),
      'base' => 'node_content',
      'description' => t('The group that will have members and content associated with.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'message' => array(
      'name' => t('Message'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Subject'),
      'help' => '',
    ),
    'opt_in' => array(
      'name' => t('Opt In'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'organization' => array(
      'name' => t('Organization'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'organization_contact' => array(
      'name' => t('Organization Contact'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Job Title'),
      'help' => '',
    ),
    'post' => array(
      'name' => t('Post'),
      'base' => 'node_content',
      'description' => t('Content that will belong to a single or multiple groups.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'rsvp' => array(
      'name' => t('RSVP'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'site' => array(
      'name' => t('Site'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Site'),
      'help' => '',
    ),
  );
  return $items;
}
