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

namespace Mimmi20\LoggerFactory\Handler;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mimmi20\LoggerFactory\AddFormatterTrait;
use Mimmi20\LoggerFactory\AddProcessorTrait;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\ProcessableHandlerInterface;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;

final class NativeMailerHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{to?: array<string>, subject?: string, from?: string, level?: (string|LogLevel::*), bubble?: bool, maxColumnWidth?: int}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NativeMailerHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('to', $options)) {
            throw new ServiceNotCreatedException('The required to is missing');
        }

        if (!array_key_exists('subject', $options)) {
            throw new ServiceNotCreatedException('The required subject is missing');
        }

        if (!array_key_exists('from', $options)) {
            throw new ServiceNotCreatedException('The required from is missing');
        }

        $toEmail        = (array) $options['to'];
        $subject        = (string) $options['subject'];
        $fromEmail      = (string) $options['from'];
        $level          = LogLevel::DEBUG;
        $bubble         = true;
        $maxColumnWidth = 70;

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        if (array_key_exists('maxColumnWidth', $options)) {
            $maxColumnWidth = (int) $options['maxColumnWidth'];
        }

        $handler = new NativeMailerHandler($toEmail, $subject, $fromEmail, $level, $bubble, $maxColumnWidth);

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}