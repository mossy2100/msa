<?php
/**
 * @file
 * marssoc_redirects.features.inc
 */

/**
 * Implements hook_views_api().
 */
function marssoc_redirects_views_api() {
  list($module, $api) = func_get_args();
  if ($module == "views" && $api == "views_default") {
    return array("version" => "3.0");
  }
}

/**
 * Implements hook_node_info().
 */
function marssoc_redirects_node_info() {
  $items = array(
    'old_site_file' => array(
      'name' => t('Old site file'),
      'base' => 'node_content',
      'description' => t('Represents a file on the old site.'),
      'has_title' => '1',
      'title_label' => t('Old path'),
      'help' => '',
    ),
  );
  return $items;
}
