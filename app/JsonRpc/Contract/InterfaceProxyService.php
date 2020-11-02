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
namespace App\JsonRpc\Contract;

/**
 * @deprecated
 * Interface InterfaceProxyService
 * @package App\JsonRpc\Contract
 */
interface InterfaceProxyService
{
    /**
     *@deprecated
     * @param int    $uid
     * @param string $message
     *
     * @return mixed
     */
    public function pushMessage(int $uid, string $message);

    /**
     * @deprecated
     * @param string $message
     *
     * @return mixed
     */
    public function broadcast(string $message);

    /**
     * @deprecated
     * @param int    $groupId
     * @param string $message
     *
     * @return mixed
     */
    public function group(int $groupId, string $message);
}
