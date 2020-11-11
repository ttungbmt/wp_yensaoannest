<?php
/*
Plugin Name: TypeRocket Pro - Andromeda
Plugin URI: https://typerocket.com/
Description: TypeRocket Pro is a framework that joins refined UI elements and modern programming architecture together.
Version: 5.0.0
Requires at least: 5.5
Requires PHP: 7.2
Author: TypeRocket
Author URI: https://typerocket.com/
License: GPLv3 or later
*/
defined( 'ABSPATH' ) or die( 'Nothing here to see!' );

define('TYPEROCKET_PLUGIN_PRO', true);
define('TYPEROCKET_PLUGIN_VERSION', '5.0.0');
define('TYPEROCKET_PLUGIN_DL_PRO_ID', 1416);
define('TYPEROCKET_PLUGIN_INSTALL', __DIR__);

if(!defined('TYPEROCKET_ROOT_WP'))
    define('TYPEROCKET_ROOT_WP', ABSPATH);

final class TypeRocketProPlugin
{
    public $path = null;
    public $message = '';
    public $activating = false;
    public $id = 'settings_typerocket';

    public function __construct()
    {
        $this->loadConfig();
        require 'typerocket/init.php';

        if(typerocket_env('TYPEROCKET_UPDATES', true)) {
            if(get_option('typerocket_pro_license_status') === 'valid') {

                // maybe add? http_request_host_is_external
                add_filter('http_request_host_is_external', function($value, $host) {
                    return $value || $host == 'typerocket.com';
                }, 10, 2);

                new \TypeRocketProPlugin\Updater(
                    'https://typerocket.com',
                    __FILE__,
                    [
                        'version' => TYPEROCKET_PLUGIN_VERSION, // current version number.
                        'license' => get_option('typerocket_pro_license_key'), // license key (used get_option above to retrieve from DB).
                        'item_id' => TYPEROCKET_PLUGIN_DL_PRO_ID, // id of this product in EDD.
                        'author'  => 'Robojuice', // author of this plugin.
                        'url'     => home_url(),
                    ]
                );
            }
        }

        $this->path = plugin_dir_path(__FILE__);
        define('TYPEROCKET_AUTO_LOADER', '__return_false');
        register_activation_hook( __FILE__, [$this, 'activation']);
        add_action('admin_notices',  [$this, 'activation_notice']);
        add_action('typerocket_loaded', [$this, 'typerocket_loaded']);
        add_filter('plugin_action_links', [$this, 'links'], 10, 2 );
    }

    public function links($actions, $plugin_file) {
        if( $found = strpos(__FILE__, $plugin_file) ) {
            $url = menu_page_url($this->id, false);
            $actions['settings'] = '<a href="'.$url.'" aria-label="TypeRocket Settings">Settings</a>';
        }

        return $actions;
    }

    public function loadConfig()
    {
        $temp_dir = get_template_directory();

        // maybe get config from theme
        if( !defined('TYPEROCKET_CORE_CONFIG_PATH') ) {
            if(file_exists( $temp_dir . '/config/galaxy.php')) {
                define('TYPEROCKET_CORE_CONFIG_PATH', $temp_dir . '/config' );
            }
        }

        // maybe get app from theme
        if(!defined('TYPEROCKET_AUTOLOAD_APP')) {
            if(file_exists( $temp_dir . '/app/Http/Kernel.php')) {
                if(!defined('TYPEROCKET_APP_NAMESPACE') ) {
                    define('TYPEROCKET_APP_NAMESPACE', 'App');
                }

                define('TYPEROCKET_APP_ROOT_PATH', $temp_dir);
                define('TYPEROCKET_ALT_PATH', $temp_dir);
                define('TYPEROCKET_AUTOLOAD_APP', [
                    'prefix' => TYPEROCKET_APP_NAMESPACE . '\\',
                    'folder' => $temp_dir . '/app/',
                ]);
            }
        }
    }

    public function activation() {
        $this->activating = true;
        flush_rewrite_rules();
        set_transient( 'typerocket-admin-notice' , true );
    }

    public function activation_notice() {
        $page = $_GET['page'] ?? null;
        if( $this->id != $page && get_transient( 'typerocket-admin-notice' ) && ! $this->activating ) {
            $url = menu_page_url($this->id, false);
            $alert = __("TypeRocket wants you to <a href=\"{$url}\">check your TypeRocket settings</a> to validate your installation is correct.", 'typerocket-domain')
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php echo $alert; ?></p>
            </div>
            <?php
        }
    }

    public function typerocket_loaded() {
        tr_page('settings@TypeRocketProPlugin\\SettingsController', 'typerocket', __('TypeRocket Settings'), [
            'menu' => __('TypeRocket'),
            'capability' => 'activate_plugins'
        ])->mapAction('PUT', 'activate');
    }
}

new TypeRocketProPlugin();