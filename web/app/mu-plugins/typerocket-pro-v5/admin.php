<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

delete_transient( 'typerocket-admin-notice' );
$lic_status = get_option( 'typerocket_pro_license_status' );

if($lic_status != 'valid') {
    ?>
    <div class="notice notice-error is-dismissible">
        <?php _e('<p>TypeRocket Pro license is invalid. Try activating again.', 'typerocket-domain'); ?>
    </div>
    <?php
}

if(empty(get_option('rewrite_rules'))) {
    $url = admin_url('options-permalink.php');
    ?>
    <div class="notice notice-error is-dismissible">
        <?php _e('<p>Enable pretty permalinks under your <a href="'.$url.'">admin "Permalink Settings" page</a>. Not sure what permalinks are? <a href="https://wordpress.org/support/article/using-permalinks/">Learn about pretty permalinks</a>.</p>', 'typerocket-domain'); ?>
    </div>
    <?php
}

$tabs = tr_tabs()->layoutLeft();
$tabs->tab('About', 'rocket', function() {
    ?>
    <div class="tr-p-20">
        <?php echo \TypeRocket\Html\Element::title('TypeRocket Pro'); ?>
        <a class="button button-primary button-hero" target="_blank" href="https://typerocket.com/getting-started/">Learn The Basics</a>
        <p class="hide-if-no-customize">or, <a target="_blank" href="https://typerocket.com/docs/v1/">read the full documentation</a></p>
        <h3>First Steps</h3>
        <p><?php _e('We’ve assembled some links to get you started:'); ?></p>
        <ul>
            <li><i class="dashicons dashicons-admin-post"></i> <a target="_blank" href="https://typerocket.com/docs/v1/post-types-making/">Add your first post type.</a></li>
            <li><i class="dashicons dashicons-admin-appearance"></i> <a target="_blank" href="https://typerocket.com/docs/v1/theme-options/">Edit your theme options.</a></li>
            <li><i class="dashicons dashicons-admin-page"></i> <a target="_blank" href="https://typerocket.com/docs/v1/builder/">Working with the page builder.</a></li>
            <li><i class="dashicons dashicons-edit"></i> <a target="_blank" href="https://typerocket.com/docs/v1/custom-resources/">Making an MVC powered resource.</a></li>
        </ul>
        <?php
        $form = tr_form()->setDebugStatus(false);
        $form->setFields(
            $form->text('TypeRocket Pro License Key')
        );
        $form->render([],[],null,'Activate');
        ?>
    </div>
<?php
})->setDescription('Getting started');

$tabs->tab('Configure', 'gear', function() {
    ?>
    <div class="tr-p-20">
        <?php echo \TypeRocket\Html\Element::title('Configuration'); ?>
        <p>The <strong>TypeRocket Pro</strong> WordPress plugin can be further configured in your <code>wp-config.php</code> file.</p>
        <ul>
            <li>To disable auto updates: <code>define('TYPEROCKET_UPDATES', false);</code></li>
            <li>To disable dev mode: <code>define('TYPEROCKET_DEV', false );</code></li>
            <li>To disable page builder: <code>define('TYPEROCKET_PAGE_BUILDER', false);</code></li>
            <li>To disable theme options: <code>define('TYPEROCKET_THEME_OPTIONS', false);</code></li>
            <li>To disable seo: <code>define('TYPEROCKET_SEO', false);</code></li>
            <li>To disable post types UI: <code>define('TYPEROCKET_UI', false);</code></li>
        </ul>
    </div>
    <?php
})->setDescription('Config settings');

echo $tabs;
?>