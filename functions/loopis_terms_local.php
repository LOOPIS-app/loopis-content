<?php
/**
 * Create default terms for single/sub sites.
 * 
 * HUBERT TODO:
 * This should be in "LOOPIS Config"? Because it is database configuration.
 *
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

function loopis_terms_local() {

    // Function to add default tags at the right time (before register_activation_hook)
    loopis_tax_local();

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

        // Terms for CPT 'forum' taxonomy 'forum-category'
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

        // Terms for CPT 'support' taxonomy 'support-category'
        'support-category' => [
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

// Uncomment below line if terms should be recreated if they are removed in WP admin (persist)
//add_action('init', 'loopis_terms_local');
