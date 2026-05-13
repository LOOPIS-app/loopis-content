<?php
/**
 * Register custom taxonomies for main site.
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

function loopis_tax_hq() {

    $taxonomies = [

        // Taxonomy for CPT 'faq'
        'faq-tag' => [
            'post_type' => 'faq',
            'slug' => 'faq-tag',
            'name' => 'FAQ-tag',
            'hierarchical' => false, // false for "tags" (not hierarchical taxonomy type)
            'show_ui'           => true,
            'show_in_nav_menus' => false,
            'show_admin_column' => true,
            'show_tagcloud'     => false,
            'public'            => false,
        ],

        // Taxonomy for CPT 'news'
        'news-category' => [
            'post_type' => 'news',
            'slug' => 'news-category',
            'name' => 'News-category',
            'hierarchical' => true, // hierarchical categories 
            'show_ui'           => true,
            'show_in_nav_menus' => false,
            'show_admin_column' => true,
            'show_tagcloud'     => false,
            'public'            => false,
        ],
        

        // Add more taxonomies here
    ];

    foreach ( $taxonomies as $taxonomy => $tax ) {

        register_taxonomy( $taxonomy, $tax['post_type'], [
            'labels' => [
                'name' => $tax['name'],
            ],
            'hierarchical'      => $tax['hierarchical'],
            'rewrite'           => false,
            'query_var'         => false,
            'publicly_queryable' => false,
            'show_ui'           => $tax['show_ui'],
            'show_in_nav_menus' => $tax['show_in_nav_menus'],
            'show_in_rest'      => true,
            'show_admin_column' => $tax['show_admin_column'],
            'show_tagcloud'     => $tax['show_tagcloud'],
            'public'            => $tax['public'],
        ] );
    }

}

add_action( 'init', 'loopis_tax_hq' );