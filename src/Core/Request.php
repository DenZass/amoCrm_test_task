<?php

namespace App\Core;

class Request
{
    public function getPath(): string
    {
        $posIndex = strpos($_SERVER['SCRIPT_NAME'], '/index.php');
        $subStrForDel = substr($_SERVER['SCRIPT_NAME'], 0, $posIndex);

        return str_replace($subStrForDel, '', $_SERVER['REQUEST_URI']);;
    }

    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}
