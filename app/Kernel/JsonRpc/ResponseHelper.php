<?php
declare(strict_types = 1);

namespace App\Kernel\JsonRpc;

class ResponseHelper
{

    private static function makeResponse(int $code, ?array $data, ?string $msg) : RpcResponse
    {
        $response = make(RpcResponse::class);
        $response->setCode($code);
        $response->setData($data);
        $response->message($msg);
        return $response;
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
