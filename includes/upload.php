<?php
/**
 * upload.php
 * Handles image file uploads and deletion of uploaded files.
 */

// ----------------------------------------------------------------
// Configuration
// ----------------------------------------------------------------

define('UPLOAD_BASE_DIR',  dirname(__DIR__) . '/uploads');
define('IMAGE_UPLOAD_DIR', UPLOAD_BASE_DIR  . '/images');
define('MAX_IMAGE_SIZE',   5 * 1024 * 1024);  // 5 MB

/** Allowed MIME types for images. */
const ALLOWED_IMAGE_MIMES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

// ----------------------------------------------------------------
// Ensure upload directories exist
// ----------------------------------------------------------------

foreach ([UPLOAD_BASE_DIR, IMAGE_UPLOAD_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ----------------------------------------------------------------
// Image upload handler
// ----------------------------------------------------------------

/**
 * Handle an image file upload from a $_FILES field.
 *
 * Validates file size, MIME type (using finfo, not just extension),
 * generates a unique filename, and moves the file to the image upload
 * directory.
 *
 * @param  array  $file         The element from $_FILES (e.g. $_FILES['image']).
 * @param  string $oldFilename  Optional existing filename to delete on success.
 * @return array{
 *     success: bool,
 *     filename: string|null,
 *     error: string|null
 * }
 */
function handleImageUpload(array $file, string $oldFilename = ''): array {
    // Check for upload errors.
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => _uploadErrorMessage($file['error']),
        ];
    }

    // Validate file size.
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => 'Image file size must not exceed 5 MB.',
        ];
    }

    // Validate MIME type using finfo (not the client-supplied type).
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime, ALLOWED_IMAGE_MIMES)) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => 'Invalid image type. Allowed formats: JPEG, PNG, WebP, GIF.',
        ];
    }

    // Validate that this is an actual image (not a disguised file).
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => 'The uploaded file does not appear to be a valid image.',
        ];
    }

    // Generate a unique, safe filename.
    $extension = ALLOWED_IMAGE_MIMES[$mime];
    $filename  = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
    $destPath  = IMAGE_UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return [
            'success'  => false,
            'filename' => null,
            'error'    => 'Failed to save the uploaded image. Please try again.',
        ];
    }

    // Delete the old file if one exists.
    if ($oldFilename !== '') {
        deleteUploadedFile($oldFilename, 'image');
    }

    return [
        'success'  => true,
        'filename' => $filename,
        'error'    => null,
    ];
}

// ----------------------------------------------------------------
// File deletion
// ----------------------------------------------------------------

/**
 * Delete an uploaded image file from the filesystem.
 *
 * @param  string $filename  Basename of the file (no path).
 * @return bool  True on success or if file does not exist; false on failure.
 */
function deleteUploadedFile(string $filename, string $type = 'image'): bool {
    if ($filename === '') {
        return true;
    }

    // Prevent directory traversal by ensuring only basenames are used.
    $safeBasename = basename($filename);
    $fullPath     = IMAGE_UPLOAD_DIR . '/' . $safeBasename;

    if (!file_exists($fullPath)) {
        return true; // Nothing to delete.
    }

    return unlink($fullPath);
}

// ----------------------------------------------------------------
// Internal helpers
// ----------------------------------------------------------------

/**
 * Translate a PHP upload error code into a human-readable message.
 *
 * @param  int $code  One of the UPLOAD_ERR_* constants.
 * @return string
 */
function _uploadErrorMessage(int $code): string {
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the maximum allowed size.',
        UPLOAD_ERR_PARTIAL                         => 'The file was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE                         => 'No file was selected for upload.',
        UPLOAD_ERR_NO_TMP_DIR                      => 'Server configuration error: missing temporary upload directory.',
        UPLOAD_ERR_CANT_WRITE                      => 'Server configuration error: failed to write file to disk.',
        UPLOAD_ERR_EXTENSION                       => 'The upload was blocked by a server extension.',
        default                                    => 'An unknown upload error occurred (code: ' . $code . ').',
    };
}
