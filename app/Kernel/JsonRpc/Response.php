<?php
declare(strict_types = 1);

namespace App\Kernel\JsonRpc;

class Response
{

    private static function makeResponse(int $code, ?array $data, ?string $msg) : array
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg'  => $msg
        ];
    }

    public static function success(?array $data, ?string $msg) : array
    {
        return self::makeResponse(1, $data, $msg);
    }

    public static function fail(?array $data, ?string $msg) : array
    {
        return self::makeResponse(0, $data, $msg);
    }
}
