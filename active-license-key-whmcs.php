<?php
/**
 * Plugin Name: Active license key
 * Plugin URI:
 * Description: Active license key for plugin
 * Author: EzDeFi
 * Version: 1.0.0
 * License: GPL
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ACTIVE_LICENSE_KEY_WHMCS_FILE', __FILE__ );

require_once __DIR__ . '/vendor/autoload.php';

use App\Active_Plugin_License_Key_Whmcs;

// Register activation hook
register_activation_hook( ACTIVE_LICENSE_KEY_WHMCS_FILE, array( Active_Plugin_License_Key_Whmcs::class, 'activate' ) );

// Register deactivation hook
register_deactivation_hook( ACTIVE_LICENSE_KEY_WHMCS_FILE, array( Active_Plugin_License_Key_Whmcs::class, 'deactivate' ) );

// Run plugin
Active_Plugin_License_Key_Whmcs::instance();
