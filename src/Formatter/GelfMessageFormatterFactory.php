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
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\GelfMessageFormatter;

use function array_key_exists;
use function is_array;

final class GelfMessageFormatterFactory implements FactoryInterface
{
    /**
     * @param string                           $requestedName
     * @param array<string, (string|int)>|null $options
     * @phpstan-param array{systemName?: string, extraPrefix?: string, contextPrefix?: string, maxLength?: int}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): GelfMessageFormatter
    {
        $systemName    = null;
        $extraPrefix   = null;
        $contextPrefix = 'ctxt_';
        $maxLength     = null;

        if (is_array($options)) {
            if (array_key_exists('systemName', $options)) {
                $systemName = $options['systemName'];
            }

            if (array_key_exists('extraPrefix', $options)) {
                $extraPrefix = $options['extraPrefix'];
            }

            if (array_key_exists('contextPrefix', $options)) {
                $contextPrefix = $options['contextPrefix'];
            }

            if (array_key_exists('maxLength', $options)) {
                $maxLength = $options['maxLength'];
            }
        }

        return new GelfMessageFormatter($systemName, $extraPrefix, $contextPrefix, $maxLength);
    }
}
