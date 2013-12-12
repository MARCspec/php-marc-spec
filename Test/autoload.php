<?php

if (!@include(__DIR__ . '/../vendor/autoload.php')) {
    spl_autoload_register(
        function ($class) {
            if (0 === strpos($class, 'CK\\MarcSpec\\')) {
                $path = implode('/', array_slice(explode('\\', $class), 2)) . '.php';
                require_once __DIR__ . '/../' . $path;

                return true;
            }
        }
    );
}