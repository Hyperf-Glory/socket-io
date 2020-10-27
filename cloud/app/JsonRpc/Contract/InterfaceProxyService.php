<?php

declare(strict_types = 1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\JsonRpc;

interface InterfaceProxyService
{
    /**
     * @param string $keys 用户的唯一token
     * @param string $message
     *
     * @return mixed
     */
    public function pushMessage(string $keys, string $message);

    public function broadcast(string $message);

    public function group(int $groupId, string $message);
}
