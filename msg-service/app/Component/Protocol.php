<?php
declare(strict_types = 1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */
namespace App\Component;

class Protocol
{
    /**
     * @var array
     */
    protected $ext = [];

    /**
     * Raw data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    protected $fd = 0;

    /**
     * @var float
     */
    protected $requestTime = 0;

    public function __construct(array $data = [], array $ext = [], int $fd = 0, float $requestTime = 0)
    {
        $this->data        = $data;
        $this->ext         = $ext;
        $this->fd          = $fd;
        $this->requestTime = $requestTime;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getExt() : array
    {
        return $this->ext;
    }

    /**
     * @return int
     */
    public function getFd() : int
    {
        return $this->fd;
    }

    /**
     * @return float
     */
    public function getRequestTime() : float
    {
        return $this->requestTime;
    }

}

