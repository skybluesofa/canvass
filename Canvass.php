<?php
/*
 * Plugin Name: Canvass
 * Plugin URI: http://www.skybluesofa.com/
 * Description: Provides mapping for recording the covering of an area of addresses
 * Version: 0.0.1
 * Author: Sky Blue Sofa
 * Author URI: http://www.skybluesofa.com
 */
namespace SkyBlueSofa\Canvass;

use SkyBlueSofa\Canvass\Admin\Admin;
use SkyBlueSofa\Canvass\Admin\Ajax;
use SkyBlueSofa\Canvass\Frontend\Frontend;
use SkyBlueSofa\Canvass\Support\Loader;
use SkyBlueSofa\Canvass\Support\Wordpress;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Load and instantiate files uesd by this plugin
 *
 * @return void
 */
function bootstrap()
{
    define('SBS_CANVASS_LOADED', true);
    require_once 'Classes/Support/Autoload.php';
    Loader::instantiate(__FILE__);

    if (Wordpress::isDashboard()) {
        new Admin();
    } elseif (Wordpress::isAdmin()) {
        new Ajax();
    } else {
        new Frontend();
    }
}
bootstrap();
