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

use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\SendGridHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function is_array;
use function sprintf;

/**
 * @phpstan-import-type Level from Logger
 * @phpstan-import-type LevelName from Logger
 */
final class SendGridHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{apiUser?: string, apiKey?: string, from?: string, to?: (string|array<string>), subject?: string, level?: (Level|LevelName|LogLevel::*), bubble?: bool}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): SendGridHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('apiUser', $options)) {
            throw new ServiceNotCreatedException('The required apiUser is missing');
        }

        if (!array_key_exists('apiKey', $options)) {
            throw new ServiceNotCreatedException('The required apiKey is missing');
        }

        if (!array_key_exists('from', $options)) {
            throw new ServiceNotCreatedException('The required from is missing');
        }

        if (!array_key_exists('to', $options)) {
            throw new ServiceNotCreatedException('The required to is missing');
        }

        if (!array_key_exists('subject', $options)) {
            throw new ServiceNotCreatedException('The required subject is missing');
        }

        $apiUser = $options['apiUser'];
        $apiKey  = $options['apiKey'];
        $from    = $options['from'];
        $to      = $options['to'];
        $subject = $options['subject'];
        $level   = LogLevel::DEBUG;
        $bubble  = true;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        try {
            $handler = new SendGridHandler(
                $apiUser,
                $apiKey,
                $from,
                $to,
                $subject,
                $level,
                $bubble,
            );
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', SendGridHandler::class),
                0,
                $e,
            );
        }

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
