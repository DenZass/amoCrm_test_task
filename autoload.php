<?php

require_once './vendor/autoload.php';

function classLoader(string $className): void
{
    $newClassName = substr_replace($className,'src', 0, 3);
    $fileName = __DIR__ . '/' . str_replace('\\','/', $newClassName) . '.php';
    if (file_exists($fileName)) {
        require_once $fileName;
    }
}

spl_autoload_register('classLoader');