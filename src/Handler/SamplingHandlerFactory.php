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
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\SamplingHandler;

use function array_key_exists;
use function assert;
use function is_array;

final class SamplingHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                           $requestedName
     * @param array<string, (string|int)>|null $options
     * @phpstan-param array{handler: array{type: string, enabled?: bool, options?: array<mixed>}, factor?: int}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SamplingHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        $handler = $this->getHandler($container, $options['handler']);

        $factor = null;

        if (array_key_exists('factor', $options)) {
            $factor = (int) $options['factor'];
        }

        if (null === $factor || 1 > $factor) {
            throw new ServiceNotCreatedException(
                'Factor is missing or is less then 1'
            );
        }

        $handler = new SamplingHandler(
            $handler,
            $factor
        );

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}