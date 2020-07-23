<?php
/**
 * Plugin Name: Active license key
 * Plugin URI:
 * Description: Active license key for plugin
 * Author: Datpt
 * Version: 1.0.0
 * Author URI: https://tuandat77.github.io/
 * License: GPL
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}



define( 'ACTIVE_PLUGIN', __FILE__ );

require_once __DIR__ . '/vendor/autoload.php';

use App\Active_Plucgin;

// Register activation hook
register_activation_hook( ACTIVE_PLUGIN, array( Active_Plucgin::class, 'activate' ) );

// Register deactivation hook
register_deactivation_hook( ACTIVE_PLUGIN, array( Active_Plucgin::class, 'deactivate' ) );

// Run plugin
Active_Plucgin::instance();
