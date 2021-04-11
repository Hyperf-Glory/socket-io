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
namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Utils\Str;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen() : array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event) : void
    {
        /** @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        $validatorFactory->extend('mobile', function ($attribute, $value, $parameters, $validator)
        {
            return is_numeric($value) && Str::startsWith((string)$value, '1');
        });
    }
}
