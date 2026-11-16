<?php
/**
 * LOOPIS Image Uploads
 *
 * Handles image upload processing, including resizing, re-encoding to WebP,
 * and metadata stripping for supported image formats.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/loopis_webp_metadata.php';

// Set compression quality for supported images and generated thumbnails.
function loopis_media_image_quality($quality, $mime_type) {
    if ('image/webp' === $mime_type) {
        return 75;
    }
    return in_array($mime_type, array('image/jpeg', 'image/png'), true) ? 70 : $quality;
}
add_filter('wp_editor_set_quality', 'loopis_media_image_quality', 10, 2);

// Prevent WordPress from generating additional intermediate image sizes.
function loopis_media_upload_sizes($sizes) {
    // Keep only the thumbnail; the full-size file is capped to the configured large dimensions.
    return isset($sizes['thumbnail']) ? array('thumbnail' => $sizes['thumbnail']) : array();
}
add_filter('intermediate_image_sizes_advanced', 'loopis_media_upload_sizes', 999);

// Resize and re-encode supported images before WordPress creates their attachments.
function loopis_process_media_upload($upload) {
    if (empty($upload['file']) || empty($upload['type'])) {
        return $upload;
    }

    $source_file = $upload['file'];
    $fail = static function($message, $extra_file = '') use ($source_file) {
        foreach (array_unique(array_filter(array($source_file, $extra_file))) as $file) {
            if (is_file($file) && !wp_delete_file($file)) {
                error_log('LOOPIS Image Uploads: could not remove a failed upload file.');
            }
        }

        return array('error' => $message);
    };

    $output_types = array(
        'image/jpeg'          => 'image/webp',
        'image/png'           => 'image/webp',
        'image/webp'          => 'image/webp',
        'image/heic'          => 'image/webp',
        'image/heif'          => 'image/webp',
        'image/heic-sequence' => 'image/webp',
        'image/heif-sequence' => 'image/webp',
    );
    $source_type = $upload['type'];

    // Leave formats outside this policy to WordPress and other upload handlers.
    if (!isset($output_types[$source_type])) {
        return $upload;
    }

    $output_type = $output_types[$source_type];
    $editor = wp_get_image_editor($upload['file']);

    if (is_wp_error($editor)) {
        return $fail('Image processing failed: ' . $editor->get_error_message());
    }

    if (!call_user_func(array(get_class($editor), 'supports_mime_type'), $output_type)) {
        return $fail('Image processing failed: the server cannot encode WebP images.');
    }

    $result = $editor->maybe_exif_rotate();
    if (is_wp_error($result)) {
        return $fail('Image rotation failed: ' . $result->get_error_message());
    }

    $dimensions = $editor->get_size();
    $max_width = absint(get_option('large_size_w'));
    $max_height = absint(get_option('large_size_h'));
    if (($max_width && $dimensions['width'] > $max_width) || ($max_height && $dimensions['height'] > $max_height)) {
        $result = $editor->resize($max_width, $max_height, false);
        if (is_wp_error($result)) {
            return $fail('Image resizing failed: ' . $result->get_error_message());
        }
    }

    $result = $editor->set_quality(75);
    if (is_wp_error($result)) {
        return $fail('Image compression failed: ' . $result->get_error_message());
    }

    $destination = $source_file;

    if ($source_type !== $output_type) {
        $filename = pathinfo($source_file, PATHINFO_FILENAME) . '.webp';
        $destination = trailingslashit(dirname($source_file)) . wp_unique_filename(dirname($source_file), $filename);
    }

    $saved = $editor->save($destination, $output_type);
    if (is_wp_error($saved)) {
        return $fail('Image saving failed: ' . $saved->get_error_message(), $destination);
    }

    $result = loopis_media_strip_webp_metadata($saved['path']);
    if (is_wp_error($result)) {
        return $fail('Image metadata removal failed: ' . $result->get_error_message(), $saved['path']);
    }

    if ($source_file !== $saved['path'] && !wp_delete_file($source_file)) {
        // Don't leave both source and converted files when cleanup fails.
        if (!wp_delete_file($saved['path'])) {
            error_log('LOOPIS Image Uploads: could not remove the converted image after cleanup failed.');
        }
        error_log('LOOPIS Image Uploads: could not remove the source image after format conversion.');
        return array('error' => 'Could not clean up the source image after conversion.');
    }

    $upload['file'] = $saved['path'];
    $upload['url'] = trailingslashit(dirname($upload['url'])) . wp_basename($saved['path']);
    $upload['type'] = $output_type;

    return $upload;
}
add_filter('wp_handle_upload', 'loopis_process_media_upload');
