<?php
/**
 * Ninja Photo Feed Autoloader
 *
 * PSR-4 compatible autoloader for the standalone plugin.
 */

spl_autoload_register(function ($class) {
    // Project namespace prefixes and their base directories
    $prefixes = [
        'NinjaPhotoFeed\\App\\' => __DIR__ . '/../app/',
        'NinjaPhotoFeed\\Framework\\' => __DIR__ . '/wpfluent/framework/src/WPFluent/',
        'NinjaPhotoFeed\\Database\\' => __DIR__ . '/../database/',
    ];

    // Check each prefix
    foreach ($prefixes as $prefix => $baseDir) {
        // Does the class use this prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Get the relative class name
        $relativeClass = substr($class, $len);

        // Replace namespace separators with directory separators
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});

// Load globals file
require_once __DIR__ . '/../boot/globals.php';
