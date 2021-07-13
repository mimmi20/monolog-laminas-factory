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
use Monolog\Formatter\LineFormatter;

use function array_key_exists;
use function is_array;

final class LineFormatterFactory implements FactoryInterface
{
    /**
     * @param string                            $requestedName
     * @param array<string, (string|bool)>|null $options
     * @phpstan-param array{format?: string, dateFormat?: string, allowInlineLineBreaks?: bool, ignoreEmptyContextAndExtra?: bool, includeStacktraces?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LineFormatter
    {
        $format                     = null;
        $dateFormat                 = null;
        $allowInlineLineBreaks      = false;
        $ignoreEmptyContextAndExtra = false;

        if (is_array($options)) {
            if (array_key_exists('format', $options)) {
                $format = (string) $options['format'];
            }

            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = (string) $options['dateFormat'];
            }

            if (array_key_exists('allowInlineLineBreaks', $options)) {
                $allowInlineLineBreaks = (bool) $options['allowInlineLineBreaks'];
            }

            if (array_key_exists('ignoreEmptyContextAndExtra', $options)) {
                $ignoreEmptyContextAndExtra = (bool) $options['ignoreEmptyContextAndExtra'];
            }
        }

        $formatter = new LineFormatter($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);

        if (is_array($options) && array_key_exists('includeStacktraces', $options)) {
            $formatter->includeStacktraces((bool) $options['includeStacktraces']);
        }

        return $formatter;
    }
}
