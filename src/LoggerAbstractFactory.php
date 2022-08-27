<?php
/**
 * This file is part of the mimmi20/monolog-laminas-factory package.
 *
 * Copyright (c) 2021-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LoggerFactory;

use DateTimeZone;
use Interop\Container\Exception\ContainerException;
use Laminas\Log\Exception\InvalidArgumentException;
use Laminas\Log\Logger;
use Laminas\Log\Processor\ProcessorInterface;
use Laminas\Log\Writer\Noop;
use Laminas\Log\Writer\Psr;
use Laminas\Log\Writer\WriterInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function assert;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Factory for logger instances.
 */
final class LoggerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Factory for laminas-servicemanager v3.
     *
     * @param string            $requestedName
     * @param array<mixed>|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): Logger
    {
        // Configure the logger
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotFoundException(sprintf('Could not find service %s', 'config'), 0, $e);
        }

        assert(is_array($config));

        $logConfig = [];

        if (
            array_key_exists('log', $config)
            && is_array($config['log'])
            && array_key_exists($requestedName, $config['log'])
            && is_array($config['log'][$requestedName])
        ) {
            $logConfig = $config['log'][$requestedName];
        }

        $loggerOptions = [
            'exceptionhandler' => $logConfig['exceptionhandler'] ?? false,
            'errorhandler' => $logConfig['errorhandler'] ?? false,
            'fatal_error_shutdownfunction' => $logConfig['shutdownhandler'] ?? false,
        ];

        try {
            $logger = new Logger($loggerOptions);
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException('An error occured while initialing the logger', 0, $e);
        }

        if ($container->has('LogProcessorManager')) {
            try {
                $logger->setProcessorPluginManager($container->get('LogProcessorManager'));
            } catch (ContainerExceptionInterface | InvalidArgumentException $e) {
                throw new ServiceNotCreatedException('An error occured while setting the ProcessorPluginManager', 0, $e);
            }
        }

        if ($container->has('LogWriterManager')) {
            try {
                $logger->setWriterPluginManager($container->get('LogWriterManager'));
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotCreatedException('An error occured while setting the LogWriterManager', 0, $e);
            }
        }

        try {
            $logger->addWriter(new Noop());
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException('An error occured while adding a noop writer', 0, $e);
        }

        if (array_key_exists('writers', $logConfig) && is_array($logConfig['writers'])) {
            foreach ($logConfig['writers'] as $writer) {
                if (array_key_exists('enabled', $writer) && !$writer['enabled']) {
                    continue;
                }

                if (!isset($writer['name'])) {
                    throw new ServiceNotCreatedException('Options must contain a name for the writer');
                }

                if (!is_string($writer['name']) && !($writer['name'] instanceof WriterInterface)) {
                    continue;
                }

                $priority      = $writer['priority'] ?? null;
                $writerOptions = $writer['options'] ?? null;

                try {
                    $logger->addWriter($writer['name'], $priority, $writerOptions);
                } catch (ServiceNotCreatedException | ServiceNotFoundException | InvalidArgumentException $e) {
                    throw new ServiceNotCreatedException('An error occured while adding a writer', 0, $e);
                }
            }
        }

        if (array_key_exists('processors', $logConfig) && is_array($logConfig['processors'])) {
            foreach ($logConfig['processors'] as $processor) {
                if (array_key_exists('enabled', $processor) && !$processor['enabled']) {
                    continue;
                }

                if (!isset($processor['name'])) {
                    throw new ServiceNotCreatedException('Options must contain a type for the processor');
                }

                if (!is_string($processor['name']) && !($processor['name'] instanceof ProcessorInterface)) {
                    continue;
                }

                $priority         = $processor['priority'] ?? null;
                $processorOptions = $processor['options'] ?? null;

                try {
                    $logger->addProcessor($processor['name'], $priority, $processorOptions);
                } catch (ServiceNotCreatedException | ServiceNotFoundException | InvalidArgumentException $e) {
                    throw new ServiceNotCreatedException('An error occured while adding a processor', 0, $e);
                }
            }
        }

        if (
            isset($logConfig['name'])
            && array_key_exists('handlers', $logConfig)
            && is_array($logConfig['handlers'])
        ) {
            $processors = [];

            if (array_key_exists('monolog_processors', $logConfig) && is_array($logConfig['monolog_processors'])) {
                $processors = $logConfig['monolog_processors'];
            }

            $monologConfig = [
                'name' => $logConfig['name'],
                'handlers' => $logConfig['handlers'],
                'processors' => $processors,
            ];

            if (
                array_key_exists('timezone', $logConfig)
                && (is_string($logConfig['timezone']) || $logConfig['timezone'] instanceof DateTimeZone)
            ) {
                $monologConfig['timezone'] = $logConfig['timezone'];
            }

            try {
                $monolog = $container->get(MonologPluginManager::class)->get(
                    \Monolog\Logger::class,
                    $monologConfig,
                );
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotCreatedException(sprintf('Could not find service %s', MonologPluginManager::class), 0, $e);
            }

            try {
                $logger->addWriter(new Psr($monolog));
            } catch (InvalidArgumentException $e) {
                throw new ServiceNotCreatedException('An error occured while adding monolog', 0, $e);
            }
        }

        return $logger;
    }

    /**
     * Can the factory create an instance for the service?
     *
     * @param string $requestedName
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        try {
            $config = $container->get('config');
        } catch (ContainerExceptionInterface) {
            return false;
        }

        return array_key_exists('log', $config)
            && is_array($config['log'])
            && array_key_exists($requestedName, $config['log']);
    }
}
