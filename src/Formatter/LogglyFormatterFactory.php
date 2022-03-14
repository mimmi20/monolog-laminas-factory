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

namespace Mimmi20\LoggerFactory\Formatter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogglyFormatter;

use function array_key_exists;
use function is_array;

final class LogglyFormatterFactory implements FactoryInterface
{
    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{batchMode?: JsonFormatter::BATCH_MODE_*, appendNewline?: bool, includeStacktraces?: bool, dateFormat?: string, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogglyFormatter
    {
        $batchMode             = LogglyFormatter::BATCH_MODE_NEWLINES;
        $appendNewline         = true;
        $maxNormalizeDepth     = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;

        if (is_array($options)) {
            if (array_key_exists('batchMode', $options)) {
                $batchMode = $options['batchMode'];
            }

            if (array_key_exists('appendNewline', $options)) {
                $appendNewline = $options['appendNewline'];
            }

            if (array_key_exists('maxNormalizeDepth', $options)) {
                $maxNormalizeDepth = $options['maxNormalizeDepth'];
            }

            if (array_key_exists('maxNormalizeItemCount', $options)) {
                $maxNormalizeItemCount = $options['maxNormalizeItemCount'];
            }

            if (array_key_exists('prettyPrint', $options)) {
                $prettyPrint = $options['prettyPrint'];
            }
        }

        $formatter = new LogglyFormatter($batchMode, $appendNewline);

        if (is_array($options)) {
            if (array_key_exists('includeStacktraces', $options)) {
                $formatter->includeStacktraces($options['includeStacktraces']);
            }

            if (array_key_exists('dateFormat', $options)) {
                $formatter->setDateFormat($options['dateFormat']);
            }
        }

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
