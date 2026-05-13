<?php
/**
 * Create default terms for main site.
 * 
 * HUBERT TODO:
 * This should be in "LOOPIS Config"? Because it is database configuration.
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

function loopis_terms_hq() {

    // Function to add default tags at the right time (before register_activation_hook)
    loopis_tax_hq();

    $defaults = [

        // Terms for CPT 'faq' taxonomy 'faq-tag'
        'faq-tag' => [
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
        ],

    // Terms for CPT 'news' taxonomy 'news-category'
        'news-category' => [
            [
                'name' => '✨ Uppdatering',
                'slug' => 'update',
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
                'name' => '🎉 Firande',
                'slug' => 'celebration',
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

// Uncomment below line if terms should be recreated if they are removed in WP admin (persist)
// add_action('init', 'loopis_terms_hq');