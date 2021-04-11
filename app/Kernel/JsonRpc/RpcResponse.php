<?php
declare(strict_types = 1);

namespace App\Kernel\JsonRpc;

class RpcResponse
{
    private int $code;

    private ?string $message;

    private ?array $data;

    public const SUCCESS = 1;
    public const FAIL = 0;

    final  public function __construct()
    {

    }

    /**
     * @param int $code
     */
    public function setCode(int $code) : void
    {
        $this->code = $code;
    }

    /**
     * @param null|string $message
     */
    public function setMessage(?string $message) : void
    {
        $this->message = $message;
    }

    /**
     * @param null|array $data
     */
    public function setData(?array $data) : void
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCode() : int
    {
        return $this->code;
    }

    /**
     * @return null|array
     */
    public function getData() : ?array
    {
        return $this->data;
    }

    /**
     * @return null|string
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }

    public function isSuccess() : bool
    {
        return $this->getCode() === self::SUCCESS;
    }

}
