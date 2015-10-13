<div id="page-wrapper"><div id="page">

  <div id="header"><div class="section clearfix">

    <?php print render($page['header']); ?>

  </div></div> <!-- /.section, /#header -->

  <div id="main-wrapper">
    
    <?php if ($page['navigation'] || $main_menu): ?>
      <div id="navigation"><div class="section clearfix">

        <?php
        // This has been replaced by #nice-menu-1
        /* print theme('links__system_main_menu', array(
          'links' => $main_menu,
          'attributes' => array(
            'id' => 'main-menu',
            'class' => array('links', 'clearfix'),
          ),
          'heading' => array(
            'text' => t('Main menu'),
            'level' => 'h2',
            'class' => array('element-invisible'),
          ),
        )); */ ?>

        <?php print render($page['navigation']); ?>

      </div></div> <!-- /.section, /#navigation -->
    <?php endif; ?>

    
    <div class="section">
      <?php print render($page['highlight']); ?>
      <?php print $breadcrumb; ?>

        <?php /*
      <div id="branches-menu">
        <?php print $branches_menu; ?>
      </div> */ ?>

      <div id="content-inner">
        <a id="main-content"></a>
        <?php print render($title_prefix); ?>
        <?php if ($title): ?>
          <h1 class="title" id="page-title"><?php print $title; ?></h1>
        <?php endif; ?>
        <?php print render($title_suffix); ?>
        <?php print $messages; ?>
        <?php

        // I added this because the tabs div was rendering when the $tabs variable
        // looks like this. This is obviously not the best solution, but need more
        // data before fixing another way.
        $x = array(
          "#theme" => "menu_local_tasks",
          "#primary" => "",
          "#secondary" => "",
        );

        if ($tabs && $tabs != $x) { ?>
          <div class="tabs"><?php print render($tabs); ?></div>
        <?php } ?>
        <?php print render($page['help']); ?>
        <?php if ($action_links): ?>
          <ul class="action-links"><?php print render($action_links); ?></ul>
        <?php endif; ?>
        <?php print render($page['content']); ?>
        <?php print $feed_icons; ?>
      </div> <!-- content-inner -->

    </div> <!-- /.section, /#content -->

  </div> <!-- /#main-wrapper -->


  <?php
  if ($page['footer'] || $secondary_menu): ?>
    <div id="footer"><div class="section">

      <?php print render($page['footer']); ?>

    </div></div> <!-- /.section, /#footer -->
  <?php endif; ?>

</div></div> <!-- /#page, /#page-wrapper -->

<?php print render($page['bottom']); ?>
