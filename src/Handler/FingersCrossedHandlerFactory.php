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
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class FingersCrossedHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;
    use GetHandlerTrait;

    /**
     * @param string                                                       $requestedName
     * @param array<string, (int|string|ActivationStrategyInterface)>|null $options
     * @phpstan-param array{handler?: bool|array{type?: string, enabled?: bool, options?: array<mixed>}, activationStrategy?: (null|Level|LevelName|LogLevel::*|ActivationStrategyInterface|array{type?: string, options?: array<mixed>}|string), bufferSize?: int, bubble?: bool, stopBuffering?: bool, passthruLevel?: (Level|LevelName|LogLevel::*)}|null $options
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

        if (!is_array($options['handler'])) {
            throw new ServiceNotCreatedException('HandlerConfig must be an Array');
        }

        $handler = $this->getHandler($container, $options['handler']);

        if (null === $handler) {
            throw new ServiceNotCreatedException('No active handler specified');
        }

        $activationStrategy = null;
        $bufferSize         = 0;
        $bubble             = true;
        $stopBuffering      = true;
        $passthruLevel      = null;

        if (array_key_exists('activationStrategy', $options)) {
            $activationStrategy = $this->getActivationStrategy($container, $options['activationStrategy']);
        }

        if (array_key_exists('bufferSize', $options)) {
            $bufferSize = $options['bufferSize'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('stopBuffering', $options)) {
            $stopBuffering = $options['stopBuffering'];
        }

        if (array_key_exists('passthruLevel', $options)) {
            $passthruLevel = $options['passthruLevel'];
        }

        $handler = new FingersCrossedHandler(
            $handler,
            $activationStrategy,
            $bufferSize,
            $bubble,
            $stopBuffering,
            $passthruLevel
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }

    /**
     * @param ActivationStrategyInterface|array<string, array<mixed>|string>|int|string $activationStrategy
     * @phpstan-param (Level|LevelName|LogLevel::*|ActivationStrategyInterface|array{type?: string, options?: array<mixed>}|string|null) $activationStrategy
     *
     * @return ActivationStrategyInterface|int|string|null
     * @phpstan-return (Level|LevelName|LogLevel::*|ActivationStrategyInterface|null)
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     */
    private function getActivationStrategy(ContainerInterface $container, $activationStrategy)
    {
        if (null === $activationStrategy) {
            return null;
        }

        if (is_int($activationStrategy) || $activationStrategy instanceof ActivationStrategyInterface) {
            return $activationStrategy;
        }

        try {
            $activationStrategyPluginManager = $container->get(ActivationStrategyPluginManager::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(
                sprintf('Could not load service %s', ActivationStrategyPluginManager::class),
                0,
                $e
            );
        }

        if (is_array($activationStrategy)) {
            if (!array_key_exists('type', $activationStrategy)) {
                throw new ServiceNotCreatedException('Options must contain a type for the ActivationStrategy');
            }

            try {
                return $activationStrategyPluginManager->get($activationStrategy['type'], $activationStrategy['options'] ?? []);
            } catch (ServiceNotFoundException | ServiceNotCreatedException $e) {
                throw new ServiceNotFoundException('Could not load ActivationStrategy class', 0, $e);
            }
        }

        if (is_string($activationStrategy) && $activationStrategyPluginManager->has($activationStrategy)) {
            try {
                return $activationStrategyPluginManager->get($activationStrategy);
            } catch (ServiceNotFoundException | ServiceNotCreatedException $e) {
                throw new ServiceNotFoundException('Could not load ActivationStrategy class', 0, $e);
            }
        }

        try {
            /* @phpstan-ignore-next-line */
            return Logger::toMonologLevel($activationStrategy);
        } catch (InvalidArgumentException $e) {
            // do nothing here
        }

        throw new ServiceNotCreatedException('Could not find Class for ActivationStrategy');
    }
}
