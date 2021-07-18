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

namespace Mimmi20\LoggerFactory\Formatter;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\LogstashFormatter;

use function array_key_exists;
use function is_array;

final class LogstashFormatterFactory implements FactoryInterface
{
    /**
     * @param string                     $requestedName
     * @param array<string, string>|null $options
     * @phpstan-param array{applicationName?: string, systemName?: string, extraPrefix?: string, contextPrefix?: string}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogstashFormatter
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('applicationName', $options)) {
            throw new ServiceNotCreatedException('No applicationName provided');
        }

        $applicationName = $options['applicationName'];
        $systemName      = null;
        $extraPrefix     = 'extra';
        $contextPrefix   = 'context';

        if (array_key_exists('systemName', $options)) {
            $systemName = $options['systemName'];
        }

        if (array_key_exists('extraPrefix', $options)) {
            $extraPrefix = $options['extraPrefix'];
        }

        if (array_key_exists('contextPrefix', $options)) {
            $contextPrefix = $options['contextPrefix'];
        }

        return new LogstashFormatter($applicationName, $systemName, $extraPrefix, $contextPrefix);
    }
}
