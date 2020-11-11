<?php
// Add custom Theme Functions here
function flatsome_scripts_styles() {
    $uri = get_stylesheet_directory_uri();
    $main_style = 'flatsome-main';

    wp_enqueue_style('tailwind-css', 'https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css');
}

add_action( 'wp_enqueue_scripts', 'flatsome_scripts_styles' );


add_shortcode('menu', function ($atts, $content = null){
    /** @var TYPE_NAME $name */
    extract(shortcode_atts(['name' => null], $atts));
//    dd($name);
    $menus = wp_get_nav_menus();
    return wp_nav_menu(['menu' => $name]);
});

