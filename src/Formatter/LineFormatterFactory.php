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
use Monolog\Formatter\LineFormatter;

use function array_key_exists;
use function is_array;

final class LineFormatterFactory implements FactoryInterface
{
    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{format?: string, dateFormat?: string, allowInlineLineBreaks?: bool, ignoreEmptyContextAndExtra?: bool, includeStacktraces?: bool, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws void
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
        $maxNormalizeDepth          = NormalizerFormatterFactory::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount      = NormalizerFormatterFactory::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint                = false;

        if (is_array($options)) {
            if (array_key_exists('format', $options)) {
                $format = $options['format'];
            }

            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = $options['dateFormat'];
            }

            if (array_key_exists('allowInlineLineBreaks', $options)) {
                $allowInlineLineBreaks = $options['allowInlineLineBreaks'];
            }

            if (array_key_exists('ignoreEmptyContextAndExtra', $options)) {
                $ignoreEmptyContextAndExtra = $options['ignoreEmptyContextAndExtra'];
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

        $formatter = new LineFormatter($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);

        if (is_array($options) && array_key_exists('includeStacktraces', $options)) {
            $formatter->includeStacktraces($options['includeStacktraces']);
        }

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
