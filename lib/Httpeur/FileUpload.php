<?php

namespace Httpeur;

/**
 * Simple file upload helper.
 * 
 * @package Httpeur
 */
class FileUpload
{
    /**
     * Get uploaded file information.
     * 
     * @param string $fieldName Form field name.
     * @return array|null File info array or null if not uploaded.
     */
    public static function get(string $fieldName): ?array
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        return [
            'name' => $_FILES[$fieldName]['name'],
            'type' => $_FILES[$fieldName]['type'],
            'tmp_name' => $_FILES[$fieldName]['tmp_name'],
            'size' => $_FILES[$fieldName]['size'],
            'error' => $_FILES[$fieldName]['error'],
        ];
    }

    /**
     * Check if a file was uploaded.
     * 
     * @param string $fieldName Form field name.
     * @return bool
     */
    public static function has(string $fieldName): bool
    {
        return isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Move uploaded file to destination.
     * 
     * @param string $fieldName Form field name.
     * @param string $destination Destination path.
     * @return bool True on success.
     */
    public static function move(string $fieldName, string $destination): bool
    {
        $file = self::get($fieldName);
        if ($file === null) {
            return false;
        }

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return move_uploaded_file($file['tmp_name'], $destination);
    }

    /**
     * Get file extension from uploaded file.
     * 
     * @param string $fieldName Form field name.
     * @return string|null File extension or null.
     */
    public static function extension(string $fieldName): ?string
    {
        $file = self::get($fieldName);
        if ($file === null) {
            return null;
        }
        return strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    }

    /**
     * Validate file type.
     * 
     * @param string $fieldName Form field name.
     * @param array $allowedExtensions Allowed file extensions (e.g., ['jpg', 'png']).
     * @return bool True if file type is allowed.
     */
    public static function validateType(string $fieldName, array $allowedExtensions): bool
    {
        $ext = self::extension($fieldName);
        return $ext !== null && in_array($ext, $allowedExtensions);
    }

    /**
     * Validate file size.
     * 
     * @param string $fieldName Form field name.
     * @param int $maxSize Maximum size in bytes.
     * @return bool True if file size is within limit.
     */
    public static function validateSize(string $fieldName, int $maxSize): bool
    {
        $file = self::get($fieldName);
        return $file !== null && $file['size'] <= $maxSize;
    }
}
