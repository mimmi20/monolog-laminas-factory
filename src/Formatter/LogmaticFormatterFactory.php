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
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogmaticFormatter;

use function array_key_exists;
use function is_array;

final class LogmaticFormatterFactory implements FactoryInterface
{
    /**
     * @param string                                $requestedName
     * @param array<string, (int|bool|string)>|null $options
     * @phpstan-param array{batchMode?: JsonFormatter::BATCH_MODE_*, appendNewline?: bool, hostname?: string, appName?: string}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogmaticFormatter
    {
        $batchMode     = JsonFormatter::BATCH_MODE_JSON;
        $appendNewline = true;

        if (is_array($options)) {
            if (array_key_exists('batchMode', $options)) {
                $batchMode = (int) $options['batchMode'];
            }

            if (array_key_exists('appendNewline', $options)) {
                $appendNewline = (bool) $options['appendNewline'];
            }
        }

        $formatter = new LogmaticFormatter($batchMode, $appendNewline);

        if (is_array($options)) {
            if (array_key_exists('hostname', $options)) {
                $formatter->setHostname((string) $options['hostname']);
            }

            if (array_key_exists('appName', $options)) {
                $formatter->setAppname((string) $options['appName']);
            }
        }

        return $formatter;
    }
}
