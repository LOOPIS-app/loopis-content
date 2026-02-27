<?php
/**
 * Function to register custom CPTs
 *
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit;
}

function register_cpts() {

 $cpts = [

    // CTP faq

    'faq' => [
        'labels' => [
            'name'          => 'FAQ-posts',
            'singular_name' => 'FAQ-post',
			'add_new_item'  => 'Add new FAQ',
            'search_items'  => 'Search FAQs',
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
        'hierarchical'          => true,
        'has_archive'           => 'faq',
        'query_var'             => 'faq',
        'map_meta_cap'          => true,
        'menu_position'         => 13,

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
        ],
    ],

    // CPT forum

    'forum' => [
        'labels' => [
            'name'          => 'Forum-posts',
            'singular_name' => 'Forum-post',
			'add_new_item'  => 'Add new Forum post',
            'search_items'  => 'Search Forum posts',
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
        'hierarchical'          => true,
        'has_archive'           => false,
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
        ],
    ],

    // CPT support
    
    'support' => [
        'labels' => [
            'name'          => 'Support-posts',
            'singular_name' => 'Support-post',
            'add_new_item'  => 'Add new support post',
            'search_items'  => 'Search support posts',
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
        'hierarchical'          => true,
        'has_archive'           => false,
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
        ],
    ],

    // Add more CPTs here

    ];

    foreach ( $cpts as $post_type => $args ) {
    
        register_post_type( $post_type, $args );
    }

}

add_action( 'init', 'register_cpts' );

?>