<?php

/**
 * @file
 * An addressfield handler for AU.
 */

$plugin = array(
  'title' => t('Address form (AU add-on)'),
  'format callback' => 'addressfield_format_address_au_generate',
  'type' => 'address',
  'weight' => -80,
);

function addressfield_format_address_au_generate(&$format, $address, $context = array()) {
  if ($address['country'] == 'AU' && $context['mode'] == 'form') {
    $format['locality_block']['administrative_area']['#options'] = array(
      ''    => t('--'),
      'ACT' => t('Australian Capital Territory'),
      'NSW' => t('New South Wales'),
      'NT'  => t('Northern Territory'),
      'QLD' => t('Queensland'),
      'SA'  => t('South Australia'),
      'TAS' => t('Tasmania'),
      'VIC' => t('Victoria'),
      'WA'  => t('Western Australia'),
    );
  }
}
