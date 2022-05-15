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

namespace Mimmi20\LoggerFactory\Processor;

use ArrayAccess;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;
use function is_string;

final class WebProcessorFactory implements FactoryInterface
{
    /**
     * @param string                                                                                         $requestedName
     * @param array<string, (array<(int|string), string>|ArrayAccess<(int|string), string>|int|string)>|null $options
     * @phpstan-param array{extraFields?: array<int|string, string>|string, serverData?: (array<string, string>|ArrayAccess<string, string>|int|string)}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): WebProcessor
    {
        $serverData  = null;
        $extraFields = null;

        if (is_array($options)) {
            $serverData = $this->getServerDataService($container, $options['serverData'] ?? []);

            if (array_key_exists('extraFields', $options)) {
                $extraFields = (array) $options['extraFields'];
            }
        }

        return new WebProcessor(
            $serverData,
            $extraFields
        );
    }

    /**
     * @param array<string, mixed>|ArrayAccess<string, mixed>|int|string $serverData
     * @phpstan-param array<string, string>|ArrayAccess<string, string>|int|string $serverData
     *
     * @return array<string, string>|ArrayAccess<string, string>|null
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     */
    public function getServerDataService(ContainerInterface $container, $serverData)
    {
        if (empty($serverData)) {
            return null;
        }

        if (
            is_array($serverData)
            || $serverData instanceof ArrayAccess
        ) {
            return $serverData;
        }

        if (!is_string($serverData) || !$container->has($serverData)) {
            throw new ServiceNotFoundException(
                'No serverData service found'
            );
        }

        try {
            return $container->get($serverData);
        } catch (ContainerExceptionInterface $e) {
            throw new ServiceNotCreatedException('Could not load ServerData', 0, $e);
        }
    }
}
