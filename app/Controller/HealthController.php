<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

class HealthController extends AbstractController
{
    public function health():ResponseInterface
    {
        return $this->response->success('ok');
    }
}
