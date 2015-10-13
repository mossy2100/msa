<div class="<?php print $classes; ?>" <?php print $attributes; ?>>
  <?php if (!$label_hidden): ?>
    <div class="field-label"<?php print $title_attributes; ?>>Email addresses</div>
  <?php endif; ?>
  <div class="field-items"<?php print $content_attributes; ?>>
    <div class="field-item odd"><a href='mailto:<?php echo $mail; ?>'><?php echo $mail; ?></a></div>
  </div>
  <?php
  if ($mail2 != $mail) { ?>
    <div class="field-items"<?php print $content_attributes; ?>>
      <div class="field-item even"><a href='mailto:<?php echo $mail2; ?>'><?php echo $mail2; ?></a></div>
    </div>
  <?php
  } ?>
</div>
