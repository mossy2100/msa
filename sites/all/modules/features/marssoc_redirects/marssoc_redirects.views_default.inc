<?php
/**
 * @file
 * marssoc_redirects.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function marssoc_redirects_views_default_views() {
  $export = array();

  $view = new view;
  $view->name = 'marssoc_redirects';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'Mars Society Redirects';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Mars Society Redirects';
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '100';
  $handler->display->display_options['style_plugin'] = 'table';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = 'Old path';
  $handler->display->display_options['fields']['title']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['title']['alter']['make_link'] = 1;
  $handler->display->display_options['fields']['title']['alter']['path'] = 'http://marssociety.org.au/[title]';
  $handler->display->display_options['fields']['title']['alter']['absolute'] = 1;
  $handler->display->display_options['fields']['title']['alter']['external'] = 1;
  $handler->display->display_options['fields']['title']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['title']['alter']['target'] = '_blank';
  $handler->display->display_options['fields']['title']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = 0;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = 0;
  $handler->display->display_options['fields']['title']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['title']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['title']['alter']['html'] = 0;
  $handler->display->display_options['fields']['title']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['title']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['title']['hide_empty'] = 0;
  $handler->display->display_options['fields']['title']['empty_zero'] = 0;
  $handler->display->display_options['fields']['title']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['title']['link_to_node'] = 0;
  /* Field: Content: New path */
  $handler->display->display_options['fields']['field_old_site_file_new_path']['id'] = 'field_old_site_file_new_path';
  $handler->display->display_options['fields']['field_old_site_file_new_path']['table'] = 'field_data_field_old_site_file_new_path';
  $handler->display->display_options['fields']['field_old_site_file_new_path']['field'] = 'field_old_site_file_new_path';
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['make_link'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['path'] = '[field_old_site_file_new_path]';
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['external'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['alt'] = '[field_old_site_file_new_path]';
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['target'] = '_blank';
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['alter']['html'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['hide_empty'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['empty_zero'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_new_path']['field_api_classes'] = 0;
  /* Field: Content: Status */
  $handler->display->display_options['fields']['field_old_site_file_status']['id'] = 'field_old_site_file_status';
  $handler->display->display_options['fields']['field_old_site_file_status']['table'] = 'field_data_field_old_site_file_status';
  $handler->display->display_options['fields']['field_old_site_file_status']['field'] = 'field_old_site_file_status';
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['external'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['alter']['html'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_status']['hide_empty'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['empty_zero'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_status']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_status']['field_api_classes'] = 0;
  /* Field: Content: Category */
  $handler->display->display_options['fields']['field_old_site_file_category']['id'] = 'field_old_site_file_category';
  $handler->display->display_options['fields']['field_old_site_file_category']['table'] = 'field_data_field_old_site_file_category';
  $handler->display->display_options['fields']['field_old_site_file_category']['field'] = 'field_old_site_file_category';
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['external'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['alter']['html'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_category']['hide_empty'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['empty_zero'] = 0;
  $handler->display->display_options['fields']['field_old_site_file_category']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['field_old_site_file_category']['field_api_classes'] = 0;
  /* Field: Content: Link */
  $handler->display->display_options['fields']['view_node']['id'] = 'view_node';
  $handler->display->display_options['fields']['view_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['view_node']['field'] = 'view_node';
  $handler->display->display_options['fields']['view_node']['label'] = 'View';
  $handler->display->display_options['fields']['view_node']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['view_node']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['view_node']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['view_node']['alter']['html'] = 0;
  $handler->display->display_options['fields']['view_node']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['view_node']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['view_node']['hide_empty'] = 0;
  $handler->display->display_options['fields']['view_node']['empty_zero'] = 0;
  $handler->display->display_options['fields']['view_node']['hide_alter_empty'] = 1;
  /* Field: Content: Edit link */
  $handler->display->display_options['fields']['edit_node']['id'] = 'edit_node';
  $handler->display->display_options['fields']['edit_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['edit_node']['field'] = 'edit_node';
  $handler->display->display_options['fields']['edit_node']['label'] = 'Edit';
  $handler->display->display_options['fields']['edit_node']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['edit_node']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['edit_node']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['edit_node']['alter']['html'] = 0;
  $handler->display->display_options['fields']['edit_node']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['edit_node']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['edit_node']['hide_empty'] = 0;
  $handler->display->display_options['fields']['edit_node']['empty_zero'] = 0;
  $handler->display->display_options['fields']['edit_node']['hide_alter_empty'] = 1;
  /* Field: Content: Delete link */
  $handler->display->display_options['fields']['delete_node']['id'] = 'delete_node';
  $handler->display->display_options['fields']['delete_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['delete_node']['field'] = 'delete_node';
  $handler->display->display_options['fields']['delete_node']['label'] = 'Delete';
  $handler->display->display_options['fields']['delete_node']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['delete_node']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['delete_node']['alter']['more_link'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['delete_node']['alter']['html'] = 0;
  $handler->display->display_options['fields']['delete_node']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['delete_node']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['delete_node']['hide_empty'] = 0;
  $handler->display->display_options['fields']['delete_node']['empty_zero'] = 0;
  $handler->display->display_options['fields']['delete_node']['hide_alter_empty'] = 1;
  /* Sort criterion: Content: Status (field_old_site_file_status) */
  $handler->display->display_options['sorts']['field_old_site_file_status_value']['id'] = 'field_old_site_file_status_value';
  $handler->display->display_options['sorts']['field_old_site_file_status_value']['table'] = 'field_data_field_old_site_file_status';
  $handler->display->display_options['sorts']['field_old_site_file_status_value']['field'] = 'field_old_site_file_status_value';
  $handler->display->display_options['sorts']['field_old_site_file_status_value']['order'] = 'DESC';
  /* Sort criterion: Content: Title */
  $handler->display->display_options['sorts']['title']['id'] = 'title';
  $handler->display->display_options['sorts']['title']['table'] = 'node';
  $handler->display->display_options['sorts']['title']['field'] = 'title';
  /* Filter criterion: Content: Published */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'node';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = 1;
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'old_site_file' => 'old_site_file',
  );
  /* Filter criterion: Content: Status (field_old_site_file_status) */
  $handler->display->display_options['filters']['field_old_site_file_status_value']['id'] = 'field_old_site_file_status_value';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['table'] = 'field_data_field_old_site_file_status';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['field'] = 'field_old_site_file_status_value';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_old_site_file_status_value']['expose']['operator_id'] = 'field_old_site_file_status_value_op';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['expose']['label'] = 'Status';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['expose']['operator'] = 'field_old_site_file_status_value_op';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['expose']['identifier'] = 'field_old_site_file_status_value';
  $handler->display->display_options['filters']['field_old_site_file_status_value']['expose']['reduce'] = 0;
  /* Filter criterion: Content: Category (field_old_site_file_category) */
  $handler->display->display_options['filters']['field_old_site_file_category_value']['id'] = 'field_old_site_file_category_value';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['table'] = 'field_data_field_old_site_file_category';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['field'] = 'field_old_site_file_category_value';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_old_site_file_category_value']['expose']['operator_id'] = 'field_old_site_file_category_value_op';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['expose']['label'] = 'Category';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['expose']['operator'] = 'field_old_site_file_category_value_op';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['expose']['identifier'] = 'field_old_site_file_category_value';
  $handler->display->display_options['filters']['field_old_site_file_category_value']['expose']['reduce'] = 0;

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'admin/config/marssoc_/redirects';
  $handler->display->display_options['menu']['type'] = 'normal';
  $handler->display->display_options['menu']['title'] = 'Manage Mars Society redirects';
  $handler->display->display_options['menu']['description'] = 'View, edit, delete redirects from old site to new.';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['name'] = 'management';
  $handler->display->display_options['menu']['context'] = 0;
  $export['marssoc_redirects'] = $view;

  return $export;
}
