<?php

require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class)
{
    if (0 === strpos($class, 'Fazy\\AsseticConfigBundle')) {
        $parts = array_slice(explode('\\', $class), 2);
        $path = __DIR__  . '/../' . implode('/', $parts) . '.php';

        require_once $path;
    }
});
