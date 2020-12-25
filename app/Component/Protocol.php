<?php

declare(strict_types=1);
/**
 *
 * This is my open source code, please do not use it for commercial applications.
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 *
 * @author CodingHePing<847050412@qq.com>
 * @link   https://github.com/Hyperf-Glory/socket-io
 */
namespace App\Component;

class Protocol
{
    /**
     * @var array
     */
    protected $ext = [];

    /**
     * Raw data.
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
        $this->data = $data;
        $this->ext = $ext;
        $this->fd = $fd;
        $this->requestTime = $requestTime;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getExt(): array
    {
        return $this->ext;
    }

    public function getFd(): int
    {
        return $this->fd;
    }

    public function getRequestTime(): float
    {
        return $this->requestTime;
    }
}
