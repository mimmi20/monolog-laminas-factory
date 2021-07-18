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
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;

final class FilterHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                                    $requestedName
     * @param array<string, (string|int|array<(int|string)>|bool)>|null $options
     * @phpstan-param array{handler?: bool|array{type?: string, enabled?: bool, options?: array<mixed>}, minLevelOrList?: (array<(string|LogLevel::*)>|string|LogLevel::*), maxLevel?: (string|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FilterHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        if (!is_array($options['handler'])) {
            throw new ServiceNotCreatedException('HandlerConfig must be an Array');
        }

        $handler = $this->getHandler($container, $options['handler']);

        if (null === $handler) {
            throw new ServiceNotCreatedException('No active handler specified');
        }

        $minLevelOrList = $options['minLevelOrList'] ?? LogLevel::DEBUG;
        $maxLevel       = $options['maxLevel'] ?? LogLevel::EMERGENCY;

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        $handler = new FilterHandler(
            $handler,
            $minLevelOrList,
            $maxLevel,
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
