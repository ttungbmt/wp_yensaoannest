<?php
/**
 * Plugin Name:       Font Awesome Pro
 * Plugin URI:        https://fontawesome.com/how-to-use/on-the-web/using-with/wordpress
 * Description:       The official way to use Font Awesome Free or Pro icons on your site, brought to you by the Font Awesome team.
 * Version:          5.15.1
 * Author:            Font Awesome
 * Author URI:        https://fontawesome.com/
 * License:           GPLv2 (or later)
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ABSPATH') or die("No script kiddies please!");
define('FAWE_VERSION', '5.15.1');
define('FAWE_URL', plugins_url('', __FILE__));

class FontAwesomePro
{
    /**
     * Name of this plugin's shortcode tag.
     *
     * @since 4.0.0
     */
    const SHORTCODE_TAG = 'icon';

    const DEFAULT_PREFIX = 'fas';

    const RESOURCE_HANDLE = 'font-awesome-pro';

    /**
     * @internal
     * @ignore
     */
    protected static $instance = null;

    /**
     * Returns the singleton instance of the FontAwesome plugin.
     *
     * @return FontAwesomePro
     * @see fa()
     * @since 4.0.0
     *
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        add_shortcode(
            self::SHORTCODE_TAG,
            [$this, 'process_shortcode']
        );

        add_filter('wp_nav_menu_items', 'do_shortcode');
        add_filter('widget_title', 'do_shortcode');
        add_filter('widget_text', 'do_shortcode');
        add_filter( 'the_title', function ($title){return do_shortcode($title);});

        $this->enqueue_style();
    }

    public function enqueue_style()
    {
        foreach (['wp_enqueue_scripts', 'admin_enqueue_scripts', 'login_enqueue_scripts'] as $action) {
            add_action($action, function () {
                wp_enqueue_style(self::RESOURCE_HANDLE . '-css', FAWE_URL . '/assets/css/all.min.css', [], FAWE_VERSION);
            });
        }
    }

    public function process_shortcode($params)
    {
        /**
         * TODO: add extras to shortcode
         * class: just add extra classes
         */
        $atts = shortcode_atts(
            [
                'name' => 'wordpress',
                'prefix' => self::DEFAULT_PREFIX,
                'class' => '',
                'size' => '',
                'color' => '',
            ],
            $params,
            self::SHORTCODE_TAG
        );

        $prefix_and_name_classes = $atts['prefix'] . ' fa-' . $atts['name'];
        $size = $atts['size'] ? 'fa-'.$atts['size'] : '';
        $color = $atts['color'] ? ' style="color: '.$atts['color'] .'"': '';

        $classes = rtrim(implode(' ', [$prefix_and_name_classes, $size, $atts['class']]));
        return '<i class="' . $classes . '"'.$color.'></i>';
    }
}

FontAwesomePro::instance()->init();
