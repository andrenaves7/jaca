<?php
// src/Jaca/Support/FileUploader.php

namespace Jaca\Support;

use Jaca\Config\Config;

class FileUploader
{
    public static function store(array $file, string $field = ''): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $default = Config::get('filesystems', 'default');
        $config = Config::get('filesystems', 'disks');
        $config = $config[$default];
        $root = $config['root']?? '/';

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($field . '_') . '.' . $ext;
        $targetPath = rtrim($root) . '/' . $filename;

        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0777, true);
        }

        move_uploaded_file($file['tmp_name'], $targetPath);

        return $filename;
    }
}
