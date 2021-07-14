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
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;
use function is_string;

final class FingersCrossedHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                                       $requestedName
     * @param array<string, (int|string|ActivationStrategyInterface)>|null $options
     * @phpstan-param array{handler: array{type: string, enabled?: bool, options?: array<mixed>}, activationStrategy?: (int|string|ActivationStrategyInterface), bufferSize?: int, bubble?: bool, stopBuffering?: bool, passthruLevel?: (string|LogLevel::*)}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FingersCrossedHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('handler', $options)) {
            throw new ServiceNotCreatedException('No handler provided');
        }

        $handler            = $this->getHandler($container, $options['handler']);
        $activationStrategy = null;

        if (array_key_exists('activationStrategy', $options)) {
            $activationStrategy = $this->getActivationStrategy($container, $options['activationStrategy']);
        }

        $bufferSize = (int) ($options['bufferSize'] ?? 0);

        $bubble = true;

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        $stopBuffering = (bool) ($options['stopBuffering'] ?? true);
        $passthruLevel = $options['passthruLevel'] ?? null;

        $handler = new FingersCrossedHandler(
            $handler,
            $activationStrategy,
            $bufferSize,
            $bubble,
            $stopBuffering,
            $passthruLevel
        );

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }

    /**
     * @param ActivationStrategyInterface|int|string $activationStrategy
     *
     * @return mixed|null
     *
     * @throws ServiceNotFoundException if unable to resolve the service
     */
    private function getActivationStrategy(ContainerInterface $container, $activationStrategy)
    {
        if (!$activationStrategy) {
            return null;
        }

        if (is_string($activationStrategy) && $container->has($activationStrategy)) {
            try {
                return $container->get($activationStrategy);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException('Could not load ActivationStrategy class', 0, $e);
            }
        }

        return $activationStrategy;
    }
}
