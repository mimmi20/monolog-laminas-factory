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
use Monolog\Formatter\MongoDBFormatter;

use function array_key_exists;
use function is_array;

final class MongoDBFormatterFactory implements FactoryInterface
{
    /**
     * @param string                         $requestedName
     * @param array<string, (bool|int)>|null $options
     * @phpstan-param array{maxNestingLevel?: int, exceptionTraceAsString?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MongoDBFormatter
    {
        $maxNestingLevel        = 3;
        $exceptionTraceAsString = true;

        if (is_array($options)) {
            if (array_key_exists('maxNestingLevel', $options)) {
                $maxNestingLevel = $options['maxNestingLevel'];
            }

            if (array_key_exists('exceptionTraceAsString', $options)) {
                $exceptionTraceAsString = $options['exceptionTraceAsString'];
            }
        }

        return new MongoDBFormatter($maxNestingLevel, $exceptionTraceAsString);
    }
}
