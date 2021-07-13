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
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Swift_Message;

use function is_callable;
use function sprintf;

trait SwiftMessageTrait
{
    /**
     * @param callable|string $message
     *
     * @return callable|Swift_Message
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getSwiftMessage(ContainerInterface $container, $message)
    {
        if (empty($message)) {
            throw new ServiceNotCreatedException(
                'No message service name or callback provided'
            );
        }

        if (is_callable($message)) {
            return $message;
        }

        if (!$container->has($message)) {
            throw new ServiceNotFoundException(
                'No Message service found'
            );
        }

        try {
            return $container->get($message);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not load service %s', $message),
                0,
                $e
            );
        }
    }
}
