<?php

if (!defined('ABSPATH')) {
    exit;
}

/** Remove WebP EXIF and XMP chunks without re-encoding or removing color profiles. */
function loopis_media_strip_webp_metadata($path) {
    $input = @fopen($path, 'rb');
    if (!$input) {
        return new WP_Error('webp_metadata_read_failed', 'Could not open the WebP for metadata removal.');
    }
    $header = fread($input, 12);
    $stat = fstat($input);
    if (strlen($header) !== 12 || substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WEBP'
        || !$stat || unpack('V', substr($header, 4, 4))[1] + 8 !== $stat['size']) {
        fclose($input);
        return new WP_Error('webp_metadata_invalid', 'The generated WebP has an invalid container.');
    }

    $temp = tempnam(dirname($path), '.loopis-webp-');
    $output = $temp ? @fopen($temp, 'wb') : false;
    if (!$output) {
        fclose($input);
        if ($temp && !unlink($temp)) {
            error_log('LOOPIS Image Uploads: could not remove a temporary metadata file.');
        }
        return new WP_Error('webp_metadata_write_failed', 'Could not create a temporary WebP for metadata removal.');
    }
    $error = '';
    $remaining = $stat['size'] - 12;
    $written = 12;
    if (fwrite($output, $header) !== 12) {
        $error = 'Could not write the WebP header.';
    }
    while ('' === $error && $remaining > 0) {
        if ($remaining < 8 || strlen($chunk = fread($input, 8)) !== 8) {
            $error = 'The generated WebP has an incomplete chunk header.';
            break;
        }
        $length = unpack('V', substr($chunk, 4, 4))[1];
        $padded = $length + ($length % 2);
        $remaining -= 8;
        if ($padded > $remaining) {
            $error = 'The generated WebP has an invalid chunk length.';
            break;
        }
        $type = substr($chunk, 0, 4);
        if (in_array($type, array('EXIF', 'XMP '), true)) {
            if (fseek($input, $padded, SEEK_CUR) !== 0) {
                $error = 'Could not skip WebP metadata.';
            }
        } else {
            if (fwrite($output, $chunk) !== 8) {
                $error = 'Could not write a WebP chunk.';
                break;
            }
            if ('VP8X' === $type) {
                if ($length !== 10 || strlen($data = fread($input, $padded)) !== $padded) {
                    $error = 'The generated WebP has an invalid extended header.';
                    break;
                }
                $data[0] = chr(ord($data[0]) & ~0x0c);
                if (fwrite($output, $data) !== $padded) {
                    $error = 'Could not update the WebP metadata flags.';
                    break;
                }
            } elseif (stream_copy_to_stream($input, $output, $padded) !== $padded) {
                $error = 'Could not copy a WebP chunk.';
                break;
            }
            $written += 8 + $padded;
        }
        $remaining -= $padded;
    }
    if ('' === $error && (fseek($output, 4) !== 0 || fwrite($output, pack('V', $written - 8)) !== 4 || !fflush($output))) {
        $error = 'Could not finish the WebP metadata update.';
    }
    fclose($input);
    if (!fclose($output) && '' === $error) {
        $error = 'Could not finish writing the WebP.';
    }
    if ('' === $error && (!chmod($temp, $stat['mode'] & 0777) || !rename($temp, $path))) {
        $error = 'Could not replace the WebP after metadata removal.';
    }
    if ('' !== $error) {
        if (file_exists($temp) && !unlink($temp)) {
            error_log('LOOPIS Image Uploads: could not remove a temporary metadata file.');
        }
        return new WP_Error('webp_metadata_failed', $error);
    }
    clearstatcache(true, $path);
    return true;
}
