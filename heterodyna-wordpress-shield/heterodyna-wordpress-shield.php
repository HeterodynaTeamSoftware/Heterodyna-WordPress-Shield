<?php
/*
Plugin Name: Heterodyna WordPress Shield
Plugin URI: https://github.com/HeterodynaTeamSoftware
Description: Nagłówki bezpieczeństwa, podstawowe meta SEO/social oraz utwardzenie wybranych endpointów technicznych dla WordPress.
Version: 1.0.0
Author: Heterodyna Team Software
Author URI: https://github.com/HeterodynaTeamSoftware
Text Domain: heterodyna-wordpress-shield
License: GPL-2.0-or-later
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) {
    exit;
}

define('HST_VERSION', '1.0.0');
define('HST_OPTION', 'heterodyna_seo_tarcza_options'); // Preserved for upgrades from Heterodyna SEO Tarcza
define('HST_FILE', __FILE__);
define('HST_DIR', plugin_dir_path(__FILE__));
define('HST_URL', plugin_dir_url(__FILE__));

require_once HST_DIR . 'includes/class-hst-plugin.php';

Heterodyna_WordPress_Shield_Plugin::init();
