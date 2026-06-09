<?php
/**
* Plugin Name:  LOOPIS Content
* Plugin URI:   https://github.com/LOOPIS-app/loopis-content
* Description:  Plugin for configuring and creating the post content of LOOPIS.app
* Version:      0.36
* Author:       The Develoopers
* Author URI:   https://loopis.org
* License:      GPL-3.0-or-later
* License URI:  https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain:  loopis-content
*/

/*
 * Copyright (C) 2026 LOOPIS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

// Prevent direct access
if (!defined('ABSPATH')) { exit; }

// Define plugin version
define('LOOPIS_CONTENT_VERSION', '0.36');
define('LOOPIS_CONTENT_TERMS_VERSION_OPTION', 'loopis_content_terms_version');

// Define plugin folder path constants
define('LOOPIS_CONTENT_DIR', plugin_dir_path(__FILE__)); // Server-side path to /wp-content/plugins/loopis-content/
define('LOOPIS_CONTENT_URL', plugin_dir_url(__FILE__));  // Client-side path to https://site.com/wp-content/plugins/loopis-content/

// Load different files for main site and single/sub-sites
if ( is_multisite() && is_main_site() ) :

    // Load taxonomies
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_tax_hq.php';

    // Add default terms on activation
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_terms_hq.php';
    register_activation_hook( __FILE__, 'loopis_terms_hq' );

    // Load CPTs
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_cpt_hq.php';

    // Load custom fields
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_cf_hq.php';

else :
    // Load taxonomies
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_tax_local.php';
    
    // Add default terms on activation
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_terms_local.php';
    register_activation_hook( __FILE__, 'loopis_terms_local' );

    // Load CPTs
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_cpt_local.php';

    // Load custom fields
    require_once LOOPIS_CONTENT_DIR . '/functions/loopis_cf_local.php';

endif;

// Flush rewrite rules on activation for CPT archives to resolve correctly
register_activation_hook( __FILE__, function() {
    if ( is_multisite() && is_main_site() ) {
        loopis_cpt_hq();
    } else {
        loopis_cpt_local();
    }
    flush_rewrite_rules();
} );
