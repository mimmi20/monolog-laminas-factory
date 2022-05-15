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

use Laminas\ServiceManager\Factory\FactoryInterface;
use Monolog\Formatter\NormalizerFormatter;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;

final class NormalizerFormatterFactory implements FactoryInterface
{
    public const DEFAULT_NORMALIZER_DEPTH      = 9;
    public const DEFAULT_NORMALIZER_ITEM_COUNT = 1000;

    /**
     * @param string                              $requestedName
     * @param array<string, bool|int|string>|null $options
     * @phpstan-param array{dateFormat?: string, maxNormalizeDepth?: int, maxNormalizeItemCount?: int, prettyPrint?: bool}|null $options
     *
     * @throws void
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NormalizerFormatter
    {
        $dateFormat            = null;
        $maxNormalizeDepth     = self::DEFAULT_NORMALIZER_DEPTH;
        $maxNormalizeItemCount = self::DEFAULT_NORMALIZER_ITEM_COUNT;
        $prettyPrint           = false;

        if (is_array($options)) {
            if (array_key_exists('dateFormat', $options)) {
                $dateFormat = $options['dateFormat'];
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

        $formatter = new NormalizerFormatter($dateFormat);

        $formatter->setMaxNormalizeDepth($maxNormalizeDepth);
        $formatter->setMaxNormalizeItemCount($maxNormalizeItemCount);
        $formatter->setJsonPrettyPrint($prettyPrint);

        return $formatter;
    }
}
