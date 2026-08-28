<?php
/**
 * View and edit custom fields for posts on main site.
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

// Load all admin assets used by custom fields
add_action( 'admin_enqueue_scripts', 'loopis_enqueue_admin_assets' );
function loopis_enqueue_admin_assets( $hook ) {

    // Only load on post edit screens
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }

    // Flatpickr CSS
    wp_enqueue_style(
        'flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
        [],
        '4.6.13'
    );

    // Flatpickr JS
    wp_enqueue_script(
        'flatpickr',
        'https://cdn.jsdelivr.net/npm/flatpickr',
        [],
        '4.6.13',
        true
    );

    // Local JS init script for datetime
    wp_enqueue_script(
        'loopis-datetime',
        LOOPIS_CONTENT_URL . 'assets/js/loopis-datetime.js',
        [ 'flatpickr' ],
        filemtime( LOOPIS_CONTENT_DIR . 'assets/js/loopis-datetime.js' ),
        true
    );

    // Local JS ajax script (jQuery) for adding single or multiple users
    wp_enqueue_script(
        'loopis-user-ajax',
        LOOPIS_CONTENT_URL . 'assets/js/loopis-user-ajax.js',
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
         LOOPIS_CONTENT_URL . 'assets/css/loopis-user-ajax.css',
        [],
        '1.0'
    );

    // JS for URL validation
    wp_enqueue_script(
        'loopis-form-validate',
        LOOPIS_CONTENT_URL . 'assets/js/loopis-form-validate.js',
        [],
        '1.0',
        true
    );

}

// Load PHP-Ajax handler for selecting single and multiple users
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

// Save function for taxonomy fields
function loopis_save_taxonomy_field( $post_id ) {

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( ! isset($_POST['loopis_fields_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['loopis_fields_nonce'], 'loopis_save_fields') ) return;

    // Get field groups to find taxonomy fields
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



/**
 * Field groups with custom fields
 */

function loopis_get_field_groups() {

    return [

        // Field group: 'faq_meta' (not yet used)

        'faq_meta' => [
            'title' => 'FAQ Post Data',
            'post_types' => ['faq'],
            'fields' => [
                'title' => [
                    'label' => 'Example text field',
                    'type'  => 'text',
                    'remove_when_empty' => true, // true will remove meta_key + meta_value when empty
                ],
                // Add more fields here ...
            ],
        ],

        // Field group: 'post_meta'

        'post_meta' => [
            'title' => 'Area Post Data',
            'post_types' => ['post'],
            'fields' => [
                'area_subdirectory' => [
                    'label' => 'Area subdirectory',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'area_blog_id' => [
                    'label' => 'Blog ID',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'area_city' => [
                    'label' => 'Area city',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'area_launch_date' => [
                    'label' => 'Area launch date',
                    'type'  => 'datetime', // datetime is a custom created format, see the datetime case in the render meta box function
                    'remove_when_empty' => true,
                ],
                'locker_postal_code' => [
                    'label' => 'Locker postal code',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
                'locker_address' => [
                    'label' => 'Locker street address',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'locker_google_maps' => [
                    'label' => 'Locker link (Google maps)',
                    'type'  => 'url',
                    'remove_when_empty' => true,
                ],
                'locker_model' => [
                    'label' => 'Locker model',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'area_members' => [
                    'label' => 'Area members',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
                'area_circulated_things' => [
                    'label' => 'Area circulated things',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
                // Add more fields here ...
            ],
        ],

        // Add more groups here ...

    ];
}

// Add meta box function

add_action( 'add_meta_boxes', 'loopis_register_field_groups' );

function loopis_register_field_groups() {

    foreach ( loopis_get_field_groups() as $group_key => $group ) {

        foreach ( $group['post_types'] as $post_type ) {

            add_meta_box(
                'loopis_' . $group_key,
                $group['title'],
                'loopis_render_meta_box',
                $post_type,
                'normal',
                'default',
                [
                    'group_key' => $group_key,
                ]
            );
        }
    }
}

// Meta box render function

function loopis_render_meta_box( $post, $box ) {

    $groups = loopis_get_field_groups();
    $group  = $groups[ $box['args']['group_key'] ];

    wp_nonce_field( 'loopis_save_fields', 'loopis_fields_nonce' );

    echo '<table class="form-table">';

    foreach ( $group['fields'] as $key => $field ) {

        $value = get_post_meta( $post->ID, $key, true );

        echo '<tr>';
        echo '<th><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th>';
        echo '<td>';

        switch ( $field['type'] ) {

            case 'text':
                echo '<input type="text" class="regular-text" 
                name="' . esc_attr( $key ) . '" 
                value="' . esc_attr( $value ) . '"
                maxlength="255">';
                echo '<p>Input text (max 255 characters)</p>';
                break;
            
            case 'number':
                // If nothing is entered, check if $field['default'] is set, if so set $value to it
                if ($value === '' || $value === null) {
                    if ( isset($field['default'])) {
                        $value = $field['default'];
                    } else {
                        $value = ''; // Else take user input
                    }
                }

                echo '<input type="number" 
                name="' . esc_attr( $key ) . '" 
                value="' . esc_attr( $value ) . '">';
                echo '<p>Input a number</p>';
                break;

            case 'user_ajax':
                // Decide if the field should be multi or single
                $multiple = ! empty( $field['multiple'] );
                $mode     = $multiple ? 'multi' : 'single';

                // Get the value from post_meta
                $value = get_post_meta( $post->ID, $key, true );

                // Make sure that $user_ids is always an array
                if ( $multiple ) {
                    $user_ids = is_array( $value ) ? $value : [];
                } else {
                    $user_ids = [];
                    if ( is_array( $value ) && ! empty( $value[0] ) ) {
                        $user_ids[] = intval( $value[0] );
                    } elseif ( $value ) {
                        $user_ids[] = intval( $value );
                    }
                }

                // Open wrapper DIV with the correct data-mode
                echo '<div class="loopis-user-ajax" data-key="' . esc_attr( $key ) . '" data-mode="' . esc_attr( $mode ) . '">';

                // Container for the already chosen users
                echo '<div class="loopis-user-selected">';

                foreach ( $user_ids as $uid ) {
                    $u = get_userdata( $uid );
                    if ( $u ) {
                        echo '<span class="loopis-user-chip" data-id="' . esc_attr( $uid ) . '">';
                        echo esc_html( $u->display_name );
                        echo '<button type="button">×</button>';

                        // Hidden input: array for multi, single value for single
                        if ( $multiple ) {
                            echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $uid ) . '">';
                        } else {
                            echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $uid ) . '">';
                        }

                        echo '</span>';
                    }
                }

                echo '</div>'; // end of .loopis-user-selected

                // Search field and result container
                echo '<input type="text" class="loopis-user-search" autocomplete="off">';
                echo '<div class="loopis-user-results"></div>';

                echo '</div>'; // end of wrapper
                echo '<p class="description">Add user(s)</p>';
                break;

            case 'url':
                echo '<input type="url" class="regular-text loopis-url" 
                name="' . esc_attr( $key ) . '" 
                value="' . esc_attr( $value ) . '"
                >';
                echo '<p>Insert URL starting with https://</p>';
                break;

            case 'taxonomy':
                $taxonomy = $field['taxonomy'];

                if ( ! taxonomy_exists( $taxonomy ) ) {
                    echo '<p>Taxonomy does not exist.</p>';
                    break;
                }

                $terms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                ]);

                $selected_terms = wp_get_object_terms(
                    $post->ID,
                    $taxonomy,
                    ['fields' => 'ids']
                );

                // Take the first saved term if it exists
                $selected = $selected_terms[0] ?? '';

                // If nothing is saved, use default from the field array
                if ( empty( $selected ) && ! empty( $field['default'] ) ) {
                    $selected = $field['default'];
                }

                echo '<select name="' . esc_attr( $key ) . '" class="loopis-taxonomy-select">';

                echo '<option value="">— Choose —</option>';

                foreach ( $terms as $term ) {
                    echo '<option value="' . esc_attr( $term->term_id ) . '" ' .
                        selected( $selected, $term->term_id, false ) . '>';
                    echo esc_html( $term->name );
                    echo '</option>';
                }

                echo '</select>';
                echo '<p>Choose taxonomy</p>';

            break;

            case 'datetime':
                echo '<input type="text"
                name="' . esc_attr( $key ) . '"
                value="' . esc_attr( $value ) . '"
                class="loopis-datetime"
                pattern="\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}"
                title="Add date (YYYY-MM-DD HH:MM:SS)"
                >';
                echo '<p>Add date (YYYY-MM-DD HH:MM:SS)</p>';
                break;

            case 'image':
                echo '<input type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
                echo '<p class="description">Input the image media-ID</p>';
                break;
        }

        echo '</td></tr>';
    }

    echo '</table>';
}

// Save function

add_action( 'save_post', 'loopis_save_fields' );

function loopis_save_fields( $post_id ) {

    if ( ! isset( $_POST['loopis_fields_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['loopis_fields_nonce'], 'loopis_save_fields' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Get post type for current post
    $current_post_type = get_post_type( $post_id );

    // Addition by Johan: Blank means truly empty ("", null, or empty array). Values like "0" are not blank.
    $is_blank_value = static function ( $raw ) {
        if ( is_array( $raw ) ) {
            return count( $raw ) === 0;
        }

        if ( null === $raw ) {
            return true;
        }

        return '' === trim( (string) $raw );
    };

    foreach ( loopis_get_field_groups() as $group ) {

        // Skip field groups that don't belong to this post type
        if ( ! isset( $group['post_types'] ) || ! in_array( $current_post_type, $group['post_types'], true ) ) {
            continue;
        }

        foreach ( $group['fields'] as $key => $field ) {

            // remove_when_empty check: Set default remove_when_empty = true (removes meta_key + meta_value when empty)
            $field['remove_when_empty'] = $field['remove_when_empty'] ?? true;

            // Get the value from the form else set to empty string
            $value = $_POST[ $key ] ?? '';
            $value_is_blank = $is_blank_value( $value );

            // If a default is configured and no input, use it (so default behavior is preserved)
            if ( $value_is_blank && isset( $field['default'] ) ) {
                $value = $field['default'];
                $value_is_blank = $is_blank_value( $value );
            }

            // If remove_when_empty is true and the value is empty => remove meta (meta_key + meta_value)
            if ( $field['remove_when_empty'] && $value_is_blank ) {
                delete_post_meta( $post_id, $key );
                continue; // continue to the next field
            }

            // Type specific sanitation / validation
            switch ( $field['type'] ) {

                case 'text':
                    
                    $value = sanitize_text_field( $value );

                    // Limit to 255 characters using mb_substr for multi-byte safety
                    if ( mb_strlen( $value ) > 255 ) {
                        $value = mb_substr( $value, 0, 255 );
                    }

                    break;

                case 'number':
                    // Allow 0 in numbers (not regarded as empty)
                    if ( isset($_POST[$key]) && $_POST[$key] !== '' ) {
                        
                        // Convert to int
                        $value = intval($_POST[$key]);

                    } else {
                        // Set to empty string if nothing is entered
                        $value = '';
                    }
                    break;

                case 'url':

                    // Backend validation
                    $value = trim( $value );

                    // Length check, limit to 2048 characters
                    if ( mb_strlen($value) > 2048 ) {
                        delete_post_meta($post_id, $key);
                        continue 2;
                    }

                    // Validate URL only when not empty; empty handling is handled elsewhere (starting on line 448)
                    if ( ! empty( $value ) ) {
                        // Only accept URLs that start with https://
                        if ( ! str_starts_with( $value, 'https://' ) || ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
                            delete_post_meta( $post_id, $key );
                            continue 2; // skip this field
                        }
                    }

                    $value = esc_url_raw( $value );
                    break;

                case 'user_ajax':

                    if ( ! empty( $field['multiple'] ) ) {
                        // Get value from the form or empty array if nothing is entered
                        $value = isset($_POST[$key]) ? array_map('intval', (array) $_POST[$key]) : [];

                        // If the array is empty ( [] == empty) ) => normalize to empty string
                        if ( empty($value) ) {
                            $value = '';
                        }

                    } else {
                        $value = isset($_POST[$key]) ? intval($_POST[$key]) : '';
                    }
                    break;

                case 'datetime':

                    // Sanitize text
                    $value = sanitize_text_field( $value );

                    // Validate format YYYY-MM-DD HH:MM:SS
                    if ( ! empty( $value ) ) {
                        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                        if ( ! $date || $date->format('Y-m-d H:i:s') !== $value ) {
                            $value = ''; // Invalid → empty string
                        }
                    }
                    break;

                case 'taxonomy':
                    // Validate term ID is numeric and exists
                    if ( ! empty( $value ) ) {
                        $value = intval( $value );
                        $taxonomy = isset( $field['taxonomy'] ) ? $field['taxonomy'] : '';
                        if ( $taxonomy && ! term_exists( $value, $taxonomy ) ) {
                            $value = ''; // Invalid term → empty string
                        }
                    } else {
                        $value = '';
                    }

                    // Continue to update_post_meta() so we keep the taxonomy value in postmeta, too.
                    // See also save function in loopis-content.php, where wp_set_object_terms is also set.
                    // Make sure these two sync, in case other functions/plugins rely on the taxonomy value in postmeta.
                    break;

                case 'image':
                    // Validate media ID is numeric and attachment exists
                    if ( ! empty( $value ) ) {
                        $value = intval( $value );
                        /* for extra strict validation, we could check if the attachment exists and is an image
                        if ( get_post_type( $value ) !== 'attachment' ) {
                            $value = ''; // Invalid attachment → empty string
                        }*/
                    } else {
                        $value = '';
                    }
                    break;

                default:
                    $value = sanitize_text_field( $value );
                    break;
            }

            // Save post meta once per field, after sanitation
            update_post_meta( $post_id, $key, $value );
        }
    }
}