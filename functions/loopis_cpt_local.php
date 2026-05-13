<?php
/**
 * Register custom post types for single/sub sites.
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit;
}

function loopis_cpt_local() {

 $cpts = [

    // Custom post type 'faq'
    'faq' => [
        'labels' => [
            'name'          => '📌 Frågor & svar',
            'singular_name' => '📌 Fråga & svar',
			'add_new_item'  => 'Add new FAQ',
            'search_items'  => 'Search FAQs',
            'not_found'     => 'No FAQs found',
        ],

        'public'                => true,
        'publicly_queryable'    => true,
        'show_in_rest'          => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'exclude_from_search'   => false,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_icon'             => 'dashicons-sticky',
        'hierarchical'          => false, // for sorting date/desc, treat as post
        'has_archive'           => 'faq',
        'query_var'             => 'faq',
        'map_meta_cap'          => true,
        'menu_position'         => 13,
        'taxonomies'            => ['faq-tag'],

        'rewrite' => [
            'slug'          => 'faq',
            'with_front'    => true,
            'pages'         => true,
            'feeds'         => true,
            'ep_mask'       => EP_PERMALINK,
        ],

        'supports' => [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'author',
        ],
    ],

    // Custom post type 'forum'
    'forum' => [
        'labels' => [
            'name'          => '📡 Nyheter',
            'singular_name' => '📡 Nyhet',
			'add_new_item'  => 'Add new Forum post',
            'search_items'  => 'Search Forum posts',
            'not_found'     => 'No forum posts found',
        ],

        'public'                => true,
        'publicly_queryable'    => true,
        'show_in_rest'          => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'exclude_from_search'   => true,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_icon'             => 'dashicons-admin-comments',
        'hierarchical'          => false, // for sorting date/desc, treat as post
        'has_archive'           => true,
        'query_var'             => 'forum',
        'map_meta_cap'          => true,
        'menu_position'         => 14,

        'rewrite' => [
            'slug'          => 'forum',
            'with_front'    => true,
            'pages'         => true,
            'feeds'         => true,
            'ep_mask'       => EP_PERMALINK,
        ],

        'supports' => [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'author',
            'comments',
        ],
    ],

    // Custom post type 'support'
    'support' => [
        'labels' => [
            'name'          => '🛟 Support-frågor',
            'singular_name' => '🛟 Support-fråga',
            'add_new_item'  => 'Add new support post',
            'search_items'  => 'Search support posts',
            'not_found'     => 'No support posts found',
        ],

        'public'                => true,
        'publicly_queryable'    => true,
        'show_in_rest'          => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'exclude_from_search'   => true,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_icon'             => 'dashicons-sos',
        'hierarchical'          => false, // for sorting date/desc, treat as post
        'has_archive'           => true,
        'query_var'             => 'support',
        'map_meta_cap'          => true,
        'menu_position'         => 15,

        'rewrite' => [
            'slug'          => 'support',
            'with_front'    => true,
            'pages'         => true,
            'feeds'         => true,
            'ep_mask'       => EP_PERMALINK,
        ],

        'supports' => [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'author',
            'comments',
        ],
    ],

    // Add more CPTs here

    ];

    foreach ( $cpts as $post_type => $args ) {
    
        register_post_type( $post_type, $args );
    }

}

add_action( 'init', 'loopis_cpt_local' );