<?php
/**
 * PHPMailer SPL autoloader.
 */
spl_autoload_register(function ($classname) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'class.' . strtolower($classname) . '.php';

    if (is_readable($file)) {
        require $file;
    }
});
