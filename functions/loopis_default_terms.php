<?php
/**
 * Function to create default terms
 *
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

// Require register taxonomies function
require_once plugin_dir_path( __FILE__ ) . 'loopis_register_tax.php';

function loopis_add_default_terms() {

    // Function to add default tags at the right time (before register_activation_hook)
    register_taxonomies();

    $defaults = [

        // FAQ categories
        'faq-category' => [
            [
                'name' => 'Instruktioner',
                'slug' => 'instructions',
            ],
            [
                'name' => 'Medlemskap',
                'slug' => 'membership',
            ],
            [
                'name' => 'LOOPIS.app',
                'slug' => 'app',
            ],
            [
                'name' => 'LOOPIS skåp',
                'slug' => 'locker',
            ],
            [
                'name' => 'Om föreningen',
                'slug' => 'organisation',
            ],
            [
                'name' => 'Support',
                'slug' => 'support',
            ],
            [
                'name' => 'Kontakt',
                'slug' => 'contact',
            ],            
        ],

        // Forum categories
        'forum-category' => [
            [
                'name' => '✨ Nyhet',
                'slug' => 'news',
            ],
            [
                'name' => '🌈 Aktuellt',
                'slug' => 'current',
            ],
            [
                'name' => '🗨 Feedback',
                'slug' => 'feedback',
            ],
            [
                'name' => '🙌 Hjälp önskas',
                'slug' => 'help',
            ],
            [
                'name' => '🔔 Startsidan',
                'slug' => 'start',
            ],
                        [
                'name' => '📌 Tips',
                'slug' => 'tips',
            ],
        ],

        // Support categories
        'support-status' => [
            [
                'name' => '⚠ Pågående',
                'slug' => 'active',
            ],
            [
                'name' => '✅ Besvarad',
                'slug' => 'inactive',
            ],
        ],

    ];

    foreach ( $defaults as $taxonomy => $terms ) {

        if ( ! taxonomy_exists( $taxonomy ) ) {
            continue;
        }

        foreach ( $terms as $term ) {

            if ( term_exists( $term['slug'], $taxonomy ) ) {
                continue;
            }

            wp_insert_term(
                $term['name'],
                $taxonomy,
                [
                    'slug' => $term['slug'],
                ]
            );
        }
    }
}

// Add the function in init and on register activation hook

add_action('init', 'loopis_add_default_terms');

register_activation_hook( __FILE__, 'loopis_add_default_terms' );

?>