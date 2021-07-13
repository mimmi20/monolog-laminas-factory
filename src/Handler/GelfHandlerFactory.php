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

use Gelf\PublisherInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;
use function is_string;

final class GelfHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                                   $requestedName
     * @param array<string, (string|int|bool|PublisherInterface)>|null $options
     * @phpstan-param array{publisher: (string|PublisherInterface), level?: (string|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): GelfHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('publisher', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required exchange class');
        }

        if ($options['publisher'] instanceof PublisherInterface) {
            $publisher = $options['publisher'];
        } elseif (!is_string($options['publisher'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required exchange class');
        } else {
            try {
                $publisher = $container->get($options['publisher']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load exchange class', 0, $e);
            }
        }

        $level = LogLevel::DEBUG;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        $handler = new GelfHandler(
            $publisher,
            $level,
            $bubble
        );

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
