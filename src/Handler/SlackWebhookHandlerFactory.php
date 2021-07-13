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
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\SlackWebhookHandler;
use Psr\Log\LogLevel;

use function array_key_exists;
use function assert;
use function is_array;

final class SlackWebhookHandlerFactory implements FactoryInterface
{
    use AddFormatterTrait;
    use AddProcessorTrait;

    /**
     * @param string                                $requestedName
     * @param array<string, (string|int|bool)>|null $options
     * @phpstan-param array{webhookUrl?: string, channel?: string, userName?: string, useAttachment?: bool, iconEmoji?: string, useShortAttachment?: bool, includeContextAndExtra?: bool, level?: (string|LogLevel::*), bubble?: bool, excludeFields?: array<string>}|null $options
     *
     * @throws ServiceNotFoundException   if unable to resolve the service
     * @throws ServiceNotCreatedException if an exception is raised when creating a service
     * @throws ContainerException         if any other error occurs
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SlackWebhookHandler
    {
        if (!is_array($options)) {
            throw new ServiceNotCreatedException('Options must be an Array');
        }

        if (!array_key_exists('webhookUrl', $options)) {
            throw new ServiceNotCreatedException('No webhookUrl provided');
        }

        $webhookUrl         = (string) $options['webhookUrl'];
        $channel            = null;
        $userName           = null;
        $useAttachment      = true;
        $iconEmoji          = null;
        $useShortAttachment = false;
        $includeContext     = false;
        $level              = LogLevel::DEBUG;
        $bubble             = true;
        $excludeFields      = [];

        if (array_key_exists('channel', $options)) {
            $channel = (string) $options['channel'];
        }

        if (array_key_exists('userName', $options)) {
            $userName = (string) $options['userName'];
        }

        if (array_key_exists('useAttachment', $options)) {
            $useAttachment = (bool) $options['useAttachment'];
        }

        if (array_key_exists('iconEmoji', $options)) {
            $iconEmoji = (string) $options['iconEmoji'];
        }

        if (array_key_exists('useShortAttachment', $options)) {
            $useShortAttachment = (bool) $options['useShortAttachment'];
        }

        if (array_key_exists('includeContextAndExtra', $options)) {
            $includeContext = (bool) $options['includeContextAndExtra'];
        }

        if (array_key_exists('level', $options)) {
            $level = $options['level'];
        }

        if (array_key_exists('bubble', $options)) {
            $bubble = (bool) $options['bubble'];
        }

        if (array_key_exists('excludeFields', $options)) {
            $excludeFields = (array) $options['excludeFields'];
        }

        $handler = new SlackWebhookHandler(
            $webhookUrl,
            $channel,
            $userName,
            $useAttachment,
            $iconEmoji,
            $useShortAttachment,
            $includeContext,
            $level,
            $bubble,
            $excludeFields
        );

        assert($handler instanceof HandlerInterface);
        assert($handler instanceof FormattableHandlerInterface);
        assert($handler instanceof ProcessableHandlerInterface);

        $this->addFormatter($container, $handler, $options);
        $this->addProcessor($container, $handler, $options);

        return $handler;
    }
}
