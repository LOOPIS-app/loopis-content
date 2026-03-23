<?php
/**
* Plugin Name:  LOOPIS Content
* Plugin URI:   https://github.com/LOOPIS-app/loopis-content
* Description:  Plugin for configuring and creating the post content of LOOPIS.app
* Version:      0.34
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
if (!defined('ABSPATH')) { 
    exit; 
}

// Load taxonomies
require_once plugin_dir_path( __FILE__ ) . '/functions/loopis_register_tax.php';

// Load default terms in taxonomies
require_once plugin_dir_path( __FILE__ ) . '/functions/loopis_default_terms.php';

// Load CPTs
require_once plugin_dir_path( __FILE__ ) . '/functions/loopis_register_cpt.php';

// Flush rewrite rules on activation for CPT archives to resolve correctly
register_activation_hook( __FILE__, function() {
    register_cpts();
    flush_rewrite_rules();
} );

// Load custom fields
require_once plugin_dir_path( __FILE__ ) . '/functions/loopis_custom_fields.php';

// Load Ajax JS for user field and form validation JS for url field in loopis_custom_fields.php
add_action('admin_enqueue_scripts', 'loopis_enqueue_admin_scripts');

function loopis_enqueue_admin_scripts() {

     // Local JS ajax script (jQuery) for adding single or multiple users
    wp_enqueue_script(
        'loopis-user-ajax',
        plugin_dir_url(__FILE__) . '/assets/js/loopis-user-ajax.js',
        ['jquery'],
        '1.0',
        true
    );
 
    // Using WP admin-ajax for single or multiple users
    wp_localize_script('loopis-user-ajax', 'loopisUserAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('loopis_user_search'),
    ]);

    // CSS styling for single or multiple users
    wp_enqueue_style(
        'custom-css',
         plugin_dir_url( __FILE__ ) . '/assets/css/loopis-user-ajax.css',       
        [],
        '1.0'
    );

    // JS for URL validation
    wp_enqueue_script(
        'loopis-form-validate',
        plugin_dir_url( __FILE__ ) . '/assets/js/loopis-form-validate.js',
        [],
        '1.0',
        true
    );

}

// Load PHP-Ajax handler for single and multiple user select in loopis_custom_fields.php
add_action('wp_ajax_loopis_user_search', 'loopis_user_ajax_search');

function loopis_user_ajax_search() {

    check_ajax_referer('loopis_user_search', 'nonce');

    if ( ! current_user_can('edit_posts') ) {
        wp_send_json_error();
    }

    $q = sanitize_text_field($_POST['q'] ?? '');

    if ( strlen($q) < 2 ) {
        wp_send_json_success([]);
    }

    $users = get_users([
        'search'         => '*' . esc_attr($q) . '*',
        'search_columns'=> ['user_login', 'display_name', 'user_email'],
        'number'         => 10,
        'orderby'        => 'display_name',
        'order'          => 'ASC',
    ]);

    $results = [];

    foreach ( $users as $user ) {
        $results[] = [
            'id'    => $user->ID,
            'label' => $user->display_name . ' (' . $user->user_email . ')',
        ];
    }

    wp_send_json_success($results);
}

// Save function for taxonomy fields from loopis_custom_fields.php
function loopis_save_taxonomy_field( $post_id ) {

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( ! isset($_POST['loopis_fields_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['loopis_fields_nonce'], 'loopis_save_fields') ) return;

    // Get field groups to find taxonomy fields
    require_once plugin_dir_path(__FILE__) . '/functions/loopis_custom_fields.php';
    $groups = loopis_get_field_groups();
    $current_post_type = get_post_type($post_id);

    foreach ($groups as $group) {
        if (!in_array($current_post_type, $group['post_types'], true)) {
            continue;
        }

        foreach ($group['fields'] as $key => $field) {
            if ($field['type'] !== 'taxonomy') {
                continue;
            }

            $taxonomy = $field['taxonomy'] ?? '';
            if (!$taxonomy || !taxonomy_exists($taxonomy)) {
                continue;
            }

            $term_id = isset($_POST[$key]) ? intval($_POST[$key]) : 0;

            if ($term_id && term_exists($term_id, $taxonomy)) {
                wp_set_object_terms($post_id, [$term_id], $taxonomy, false);
            } else {
                wp_set_object_terms($post_id, [], $taxonomy, false);
            }
        }
    }
}

// Note: This hook runs at priority 20. If conflicts occur with other plugins,
// consider increasing this priority or reducing to "15" (after postmeta saves).
add_action('save_post', 'loopis_save_taxonomy_field', 20);

