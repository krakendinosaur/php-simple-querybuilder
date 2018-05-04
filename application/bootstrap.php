<?php

/**
 * Autoload method for one core directory of objects
 */
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $path = dirname(__FILE__).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$class.'.php';

    if (is_readable($path)) {
        require_once($path);
    }
});
