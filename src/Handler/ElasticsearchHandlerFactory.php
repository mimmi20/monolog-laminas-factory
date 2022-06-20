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

namespace Mimmi20\LoggerFactory\Handler;

use Elastic\Elasticsearch\Client as V8Client;
use Elasticsearch\Client as V7Client;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Mimmi20\LoggerFactory\ClientPluginManager;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function class_exists;
use function date;
use function get_class;
use function gettype;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function str_replace;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class ElasticsearchHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    public const INDEX_PER_DAY   = 'Y-m-d';
    public const INDEX_PER_MONTH = 'Y-m';
    public const INDEX_PER_YEAR  = 'Y';

    /**
     * @param string                                                  $requestedName
     * @param array<string, (string|int|bool|V7Client|V8Client)>|null $options
     * @phpstan-param array{client?: (bool|string|V7Client|V8Client), index?: string, type?: string, ignoreError?: bool, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ElasticsearchHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('client', $options)) {
            throw new ServiceNotCreatedException('No Service name provided for the required service class');
        }

        if ($options['client'] instanceof V8Client || $options['client'] instanceof V7Client) {
            $client = $options['client'];
        } elseif (is_array($options['client'])) {
            if (class_exists(V8Client::class)) {
                $clientType = V8Client::class;
            } else {
                $clientType = V7Client::class;
            }

            try {
                $monologClientPluginManager = $container->get(ClientPluginManager::class);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(
                    sprintf('Could not find service %s', ClientPluginManager::class),
                    0,
                    $e
                );
            }

            assert(
                $monologClientPluginManager instanceof ClientPluginManager || $monologClientPluginManager instanceof AbstractPluginManager,
                sprintf(
                    '$monologConfigPluginManager should be an Instance of %s, but was %s',
                    AbstractPluginManager::class,
                    is_object($monologClientPluginManager) ? get_class($monologClientPluginManager) : gettype($monologClientPluginManager)
                )
            );

            try {
                $client = $monologClientPluginManager->get($clientType, $options['client']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(
                    sprintf('Could not find service %s', $clientType),
                    0,
                    $e
                );
            }

            if (!$client instanceof V8Client && !$client instanceof V7Client) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', $clientType)
                );
            }
        } elseif (!is_string($options['client'])) {
            throw new ServiceNotCreatedException('No Service name provided for the required service class');
        } else {
            try {
                $client = $container->get($options['client']);
            } catch (ContainerExceptionInterface $e) {
                throw new ServiceNotFoundException(
                    sprintf('Could not load client class for %s class', ElasticsearchHandler::class),
                    0,
                    $e
                );
            }

            if (!$client instanceof V8Client && !$client instanceof V7Client) {
                throw new ServiceNotCreatedException(
                    sprintf('Could not create %s', ElasticsearchHandler::class)
                );
            }
        }

        $index           = 'monolog';
        $dateFormat      = self::INDEX_PER_DAY;
        $indexNameFormat = '{indexname}';
        $type            = 'record';
        $ignoreError     = false;
        $level           = LogLevel::DEBUG;
        $bubble          = true;

        if (array_key_exists('index', $options)) {
            $index = $options['index'];
        }

        if (
            array_key_exists('dateFormat', $options)
            && in_array($options['dateFormat'], [self::INDEX_PER_DAY, self::INDEX_PER_MONTH, self::INDEX_PER_YEAR], true)
        ) {
            $dateFormat = $options['dateFormat'];
        }

        if (
            array_key_exists('indexNameFormat', $options)
            && false !== mb_strpos($options['indexNameFormat'], '{indexname}')
        ) {
            $indexNameFormat = $options['indexNameFormat'];
        }

        if (array_key_exists('type', $options)) {
            $type = $options['type'];
        }

        if (array_key_exists('ignoreError', $options)) {
            $ignoreError = $options['ignoreError'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        $handler = new ElasticsearchHandler(
            $client,
            [
                'index' => str_replace(['{indexname}', '{date}'], [$index, date($dateFormat)], $indexNameFormat),
                'type' => $type,
                'ignore_error' => $ignoreError,
            ],
            $level,
            $bubble
        );

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
