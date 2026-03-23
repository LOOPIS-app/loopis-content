<?php
/**
 * Function to create custom field groups and custom fields
 *
 */

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

// Load scripts and files for the datetime picker

add_action( 'admin_enqueue_scripts', 'loopis_enqueue_datetime_picker' );
function loopis_enqueue_datetime_picker( $hook ) {

    // Only load on post edit screens
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }

    // Optional: only on certain CPTs
    // This loads the scripts only for editing the specified post types: post, FAQ, forum and support
    $screen = get_current_screen();
    if ( ! in_array( $screen->post_type, [ 'post', 'support' ], true ) ) {
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
        plugin_dir_url( __FILE__ ) . '../assets/js/loopis-datetime.js',
        [ 'flatpickr' ],
        '1.0',
        true
    );

}

/**
 * Field groups with custom fields
 * remove_when_empty option: if true, meta_key + meta_value will be removed in postmeta table if there is no value in the field
 * if false: meta_key will remain in the postmeta table with an empty string '' as meta_value
 */

function loopis_get_field_groups() {

    return [

        // Field group: 'support_meta', custom fields: 'title', 'link', 'status', 'invited'

        'support_meta' => [
            'title' => 'Support Fields',
            'post_types' => ['support'],
            'fields' => [
                'title' => [
                    'label' => 'Title',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'link' => [
                    'label' => 'Link',
                    'type'  => 'url',
                    'remove_when_empty' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type'  => 'taxonomy',
                    'taxonomy' => 'support-status', // needed for the taxonomy field
                    'remove_when_empty' => false, // false, will never be empty because of the default value
                    'default' => 198, // default status: pågående, remove 'default' => 198, to remove default value
                ],
                'invited' => [
                    'label' => 'Invited',
                    'type'  => 'user_ajax',
                    'multiple' => true, // needed for multiple users
                    'remove_when_empty' => true,
                ],

            ],
        ],

        // Field group: 'post_meta', custom fields: 'location', 'custom_location', etc
        // remove_when_empty, true for all fields except for the user_ajax fields 'participants' and 'fetcher'

        'post_meta' => [
            'title' => 'Post Data Fields',
            'post_types' => ['post'],
            'fields' => [
                'location' => [
                    'label' => 'Location',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'custom_location' => [
                    'label' => 'Location (custom)',
                    'type'  => 'text',
                    'remove_when_empty' => true,
                ],
                'locker_number' => [
                    'label' => 'Locker number',
                    'type'  => 'number',
                    'remove_when_empty' => true, // true but will never be empty because of the default value
                    'default' => 001, // default number: 001, remove 'default' => 001, to remove default value
                ],
                'image_2' => [
                    'label' => 'Extra image?',
                    'type'  => 'image',
                    'remove_when_empty' => true,
                ],
                'participants' => [
                    'label' => 'Participants',
                    'type'  => 'user_ajax',
                    'multiple' => true, // needed for multiple users
                    'remove_when_empty' => false, // will keep the meta_key with empty value ('')
                ],
                'fetcher' => [
                    'label' => 'Fetcher',
                    'type'  => 'user_ajax',
                    'multiple' => false, // needed for single user, difference from ACF: this value can be empty, in ACF it can't be cleared from WPAA/Gutenberg
                    'remove_when_empty' => false, // will keep the meta_key with empty value ('')
                ],
                'queue' => [
                    'label' => 'Queue',
                    'type'  => 'user_ajax',
                    'multiple' => true, // needed for multiple users
                    'remove_when_empty' => true,
                ],
                'raffle_date' => [
                    'label' => 'Raffle date',
                    'type'  => 'datetime', // datetime is a custom created format, see the datetime case in the render meta box function
                    'remove_when_empty' => true,
                ],
                'book_date' => [
                    'label' => 'Book date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'locker_date' => [
                    'label' => 'Locker date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'fetch_date' => [
                    'label' => 'Fetch date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'forward_date' => [
                    'label' => 'Forward date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'remove_date' => [
                    'label' => 'Remove date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'pause_date' => [
                    'label' => 'Pause date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'archive_date' => [
                    'label' => 'Archive date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'extend_date' => [
                    'label' => 'Extend date',
                    'type'  => 'datetime',
                    'remove_when_empty' => true,
                ],
                'forward_post' => [
                    'label' => 'Forward post',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
                'previous_post' => [
                    'label' => 'Previous post',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
                'reminder_leave' => [
                    'label' => 'Reminder leave',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
                'reminder_fetch' => [
                    'label' => 'Reminder fetch',
                    'type'  => 'number',
                    'remove_when_empty' => true,
                ],
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
                echo '<input type="text" class="loopis-user-search" placeholder="Search users..." autocomplete="off">';
                echo '<div class="loopis-user-results"></div>';

                echo '</div>'; // end of wrapper
                echo '<p class="description">Add user|s</p>';
                break;

            case 'url':
                echo '<input type="url" class="regular-text loopis-url" 
                name="' . esc_attr( $key ) . '" 
                value="' . esc_attr( $value ) . '"
                placeholder="https://example.com"
                >';
                echo '<p>Insert a valid URL starting with https://</p>';
                break;

            case 'taxonomy':
                $taxonomy = $field['taxonomy'];

                if ( ! taxonomy_exists( $taxonomy ) ) {
                    echo '<p>Taxonomin finns inte.</p>';
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

                echo '<option value="">— Välj —</option>';

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
                placeholder="YYYY-MM-DD HH:MM:SS"
                title="Format: YYYY-MM-DD HH:MM:SS"
                >';
                echo '<p>Insert a date</p>';
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

            // If a default is configured and no input, use it (so default behavior is preserved)
            if ( empty( $value ) && isset( $field['default'] ) ) {
                $value = $field['default'];
            }

            // If remove_when_empty is true and the value is empty => remove meta (meta_key + meta_value)
            if ( $field['remove_when_empty'] && empty( $value ) ) {
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