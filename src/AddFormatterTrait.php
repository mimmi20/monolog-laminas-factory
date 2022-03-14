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

namespace Mimmi20\LoggerFactory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function assert;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function sprintf;

trait AddFormatterTrait
{
    use CreateFormatterTrait;

    /**
     * @param array<array<string, array<string, mixed>|bool|string>|FormatterInterface>|null $options
     * @phpstan-param HandlerInterface&FormattableHandlerInterface $handler
     * @phpstan-param array{formatter?: (bool|FormatterInterface|array{enabled?: bool, type?: string, options?: array<mixed>})}|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function addFormatter(ContainerInterface $container, HandlerInterface $handler, ?array $options = null): void
    {
        if (
            !$handler instanceof FormattableHandlerInterface
            || !is_array($options)
            || !array_key_exists('formatter', $options)
        ) {
            return;
        }

        if (!is_array($options['formatter']) && !$options['formatter'] instanceof FormatterInterface) {
            throw new ServiceNotCreatedException(
                sprintf('Formatter must be an Array or an Instance of %s', FormatterInterface::class)
            );
        }

        try {
            $monologFormatterPluginManager = $container->get(MonologFormatterPluginManager::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not find service %s', MonologFormatterPluginManager::class),
                0,
                $e
            );
        }

        assert(
            $monologFormatterPluginManager instanceof MonologHandlerPluginManager || $monologFormatterPluginManager instanceof AbstractPluginManager,
            sprintf(
                '$monologFormatterPluginManager should be an Instance of %s, but was %s',
                AbstractPluginManager::class,
                is_object($monologFormatterPluginManager) ? get_class($monologFormatterPluginManager) : gettype($monologFormatterPluginManager)
            )
        );

        $formatter = $this->createFormatter($options['formatter'], $monologFormatterPluginManager);

        if (null === $formatter) {
            return;
        }

        $handler->setFormatter($formatter);
    }
}
