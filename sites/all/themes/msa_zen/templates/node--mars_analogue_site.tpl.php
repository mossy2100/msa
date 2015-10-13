<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
  <?php if (!$page && $title): ?>
    <h2<?php print $title_attributes; ?>><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
  <?php endif; ?>
  <?php print render($title_suffix); ?>

  <?php if ($unpublished): ?>
    <div class="unpublished"><?php print t('Unpublished'); ?></div>
  <?php endif; ?>

  <?php if ($display_submitted): ?>
    <div class="submitted">
      <?php print $submitted; ?>
    </div>
  <?php endif; ?>
    
  <?php
  $points = array();
  $n_points = count($node->field_geo_location[$node->language]);
  for ($i = 0; $i < $n_points; $i++) {
    $field_collection_id = $node->field_geo_location[$node->language][$i]['value'];
    $location = field_collection_item_load($field_collection_id);
    $lat = $location->field_geo_lat[$node->language][0]['value'];
    $lng = $location->field_geo_long[$node->language][0]['value'];
    $points[] = array(
      'lat'   => $lat,
      'lng'   => $lng,
      'html'  => "$lat&deg;, $lng&deg;",
    );
  }
  echo theme('msa_geology_map', array('points' => $points));
  ?>

  <div class="content"<?php print $content_attributes; ?>>
    <?php
      // We hide the comments and links now so that we can render them later.
      hide($content['comments']);
      hide($content['links']);
      print render($content);
    ?>
  </div>

  <?php print render($content['links']); ?>

  <?php print render($content['comments']); ?>

</div><!-- /.node -->
