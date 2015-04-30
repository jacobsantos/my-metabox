<?php
/*
Plugin Name: My MetaBox
Description: Add a custom metabox to your Posts page
Version:     0.1
Author:      Jonathan Wondrusch
Author URI:  https://jonathanwondrusch.com
Text Domain: my-mb
*/

/**
 * Set up a reference to our path for convenience
 */
if (!defined('MY_MB_PATH')) {
    define('MY_MB_PATH', plugin_dir_path(__FILE__));
}

require_once(MY_MB_PATH . 'classes/MyMetaboxPlugin.php');
MyMetaboxPlugin::getInstance();
