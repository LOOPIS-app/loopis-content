<?php
/**
 * Function to register custom taxonomies
 *
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

function register_taxonomies() {

    $taxonomies = [

        // A taxonomy for the CPT type 'faq' with the name 'faq-categories' etc
        
        'faq-category' => [
            'post_type' => 'faq',
            'slug' => 'faq-category',
            'name' => 'FAQ-categories',
            'hierarchical' => true,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_admin_column' => true,
        ],

        'forum-category' => [
            'post_type' => 'forum',
            'slug' => 'forum-category',
            'name' => 'Forum-categories',
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_tagcloud'     => true,
        ],
        
        'support-status' => [
            'post_type' => 'support',
            'slug' => 'support-status',
            'name' => 'Support-status',
            'hierarchical' => true,
            'show_tagcloud'     => true,
            'show_admin_column' => true,
        ],

        // Add more taxonomies here
    ];

    foreach ( $taxonomies as $taxonomy => $tax ) {

        register_taxonomy( $taxonomy, $tax['post_type'], [
            'labels' => [
                'name' => $tax['name'],
            ],
            'hierarchical' => $tax['hierarchical'],
            'rewrite' => [ 'slug' => $tax['slug'] ],
            'show_in_rest'      => true,
            'show_admin_column' => $tax['show_admin_column'],
            'show_tagcloud'     => $tax['show_tagcloud'],
        ] );
    }

}

add_action( 'init', 'register_taxonomies' );

?>