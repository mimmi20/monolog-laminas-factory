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
use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\SlackHandler;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;
use function sprintf;

final class SlackHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{token?: string, channel?: string, userName?: string, useAttachment?: bool, iconEmoji?: string, level?: (string|LogLevel::*), bubble?: bool, useShortAttachment?: bool, includeContextAndExtra?: bool, excludeFields?: array<string>}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SlackHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('token', $options)) {
            throw new ServiceNotCreatedException('No token provided');
        }

        if (!array_key_exists('channel', $options)) {
            throw new ServiceNotCreatedException('No channel provided');
        }

        $token              = $options['token'];
        $channel            = $options['channel'];
        $userName           = null;
        $useAttachment      = true;
        $iconEmoji          = null;
        $level              = LogLevel::DEBUG;
        $bubble             = true;
        $useShortAttachment = false;
        $includeContext     = false;
        $excludeFields      = [];

        if (array_key_exists('userName', $options)) {
            $userName = $options['userName'];
        }

        if (array_key_exists('useAttachment', $options)) {
            $useAttachment = $options['useAttachment'];
        }

        if (array_key_exists('iconEmoji', $options)) {
            $iconEmoji = $options['iconEmoji'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = $options['bubble'];
        }

        if (array_key_exists('useShortAttachment', $options)) {
            $useShortAttachment = $options['useShortAttachment'];
        }

        if (array_key_exists('includeContextAndExtra', $options)) {
            $includeContext = $options['includeContextAndExtra'];
        }

        if (array_key_exists('excludeFields', $options)) {
            $excludeFields = $options['excludeFields'];
        }

        try {
            $handler = new SlackHandler(
                $token,
                $channel,
                $userName,
                $useAttachment,
                $iconEmoji,
                $level,
                $bubble,
                $useShortAttachment,
                $includeContext,
                $excludeFields
            );
        } catch (MissingExtensionException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Could not create %s', SlackHandler::class),
                0,
                $e
            );
        }

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
