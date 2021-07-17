<?php
/**
 * This file is part of the mimmi20/monolog-laminas-factory package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LoggerFactory\Handler;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Monolog\Handler\HandlerInterface;

use function array_key_exists;
use function is_array;

trait GetHandlersTrait
{
    use GetHandlerTrait;

    /**
     * @phpstan-param array{handlers?: bool|array<array{type?: string, enabled?: bool, options?: array<mixed>}>} $options
     *
     * @return array<int, HandlerInterface>
     *
     * @throws ContainerException
     */
    private function getHandlers(ContainerInterface $container, array $options): array
    {
        if (!array_key_exists('handlers', $options) || !is_array($options['handlers'])) {
            throw new ServiceNotCreatedException(
                'No Service names provided for the required handler classes'
            );
        }

        $return = [];

        foreach ($options['handlers'] as $handler) {
            $handler = $this->getHandler($container, $handler);

            if (null === $handler) {
                continue;
            }

            $return[] = $handler;
        }

        if ([] === $return) {
            throw new ServiceNotCreatedException(
                'No active handlers specified'
            );
        }

        return $return;
    }
}
