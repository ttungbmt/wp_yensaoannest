<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 * @wordpress-plugin
 * Plugin Name:       Laravel DD for Wordpress
 * Plugin URI:
 * Description:       Use Laravel's dd() (die dump) function in your Wordpress projects
 * Version:           1.0.0
 * Author:            Truong Thanh Tung
 * License:           MIT
 */


//Autoload Composer packages
require __DIR__ . '/vendor/autoload.php';