<?php

namespace App\Helpers;

class JsonResponse
{
    public static function message($message): false|string
    {
        return json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    }
    public static function error($message, $code = 500) :void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo self::message($message);
    }
    public static function success($message) :void
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo self::message($message);
    }
    public static function successResult($result)
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
