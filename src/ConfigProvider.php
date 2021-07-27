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

namespace Mimmi20\LoggerFactory;

use Bartlett\Monolog\Handler\CallbackFilterHandler;
use CMDISP\MonologMicrosoftTeams\TeamsLogHandler;
use JK\Monolog\Processor\RequestHeaderProcessor;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Mimmi20\LoggerFactory\Formatter\ChromePHPFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\ElasticaFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\FlowdockFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\FluentdFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\GelfMessageFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\HtmlFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\JsonFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\LineFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\LogglyFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\LogmaticFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\LogstashFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\MongoDBFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\NormalizerFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\ScalarFormatterFactory;
use Mimmi20\LoggerFactory\Formatter\WildfireFormatterFactory;
use Mimmi20\LoggerFactory\Handler\AmqpHandlerFactory;
use Mimmi20\LoggerFactory\Handler\BrowserConsoleHandlerFactory;
use Mimmi20\LoggerFactory\Handler\BufferHandlerFactory;
use Mimmi20\LoggerFactory\Handler\CallbackFilterHandlerFactory;
use Mimmi20\LoggerFactory\Handler\ChromePHPHandlerFactory;
use Mimmi20\LoggerFactory\Handler\CouchDBHandlerFactory;
use Mimmi20\LoggerFactory\Handler\CubeHandlerFactory;
use Mimmi20\LoggerFactory\Handler\DeduplicationHandlerFactory;
use Mimmi20\LoggerFactory\Handler\DoctrineCouchDBHandlerFactory;
use Mimmi20\LoggerFactory\Handler\DynamoDbHandlerFactory;
use Mimmi20\LoggerFactory\Handler\ElasticaHandlerFactory;
use Mimmi20\LoggerFactory\Handler\ElasticsearchHandlerFactory;
use Mimmi20\LoggerFactory\Handler\ErrorLogHandlerFactory;
use Mimmi20\LoggerFactory\Handler\FallbackGroupHandlerFactory;
use Mimmi20\LoggerFactory\Handler\FilterHandlerFactory;
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManager;
use Mimmi20\LoggerFactory\Handler\FingersCrossed\ActivationStrategyPluginManagerFactory;
use Mimmi20\LoggerFactory\Handler\FingersCrossedHandlerFactory;
use Mimmi20\LoggerFactory\Handler\FirePHPHandlerFactory;
use Mimmi20\LoggerFactory\Handler\FleepHookHandlerFactory;
use Mimmi20\LoggerFactory\Handler\FlowdockHandlerFactory;
use Mimmi20\LoggerFactory\Handler\GelfHandlerFactory;
use Mimmi20\LoggerFactory\Handler\GroupHandlerFactory;
use Mimmi20\LoggerFactory\Handler\IFTTTHandlerFactory;
use Mimmi20\LoggerFactory\Handler\InsightOpsHandlerFactory;
use Mimmi20\LoggerFactory\Handler\LogEntriesHandlerFactory;
use Mimmi20\LoggerFactory\Handler\LogglyHandlerFactory;
use Mimmi20\LoggerFactory\Handler\LogmaticHandlerFactory;
use Mimmi20\LoggerFactory\Handler\MandrillHandlerFactory;
use Mimmi20\LoggerFactory\Handler\MongoDBHandlerFactory;
use Mimmi20\LoggerFactory\Handler\NativeMailerHandlerFactory;
use Mimmi20\LoggerFactory\Handler\NewRelicHandlerFactory;
use Mimmi20\LoggerFactory\Handler\NoopHandlerFactory;
use Mimmi20\LoggerFactory\Handler\NullHandlerFactory;
use Mimmi20\LoggerFactory\Handler\OverflowHandlerFactory;
use Mimmi20\LoggerFactory\Handler\PHPConsoleHandlerFactory;
use Mimmi20\LoggerFactory\Handler\ProcessHandlerFactory;
use Mimmi20\LoggerFactory\Handler\PsrHandlerFactory;
use Mimmi20\LoggerFactory\Handler\PushoverHandlerFactory;
use Mimmi20\LoggerFactory\Handler\RedisHandlerFactory;
use Mimmi20\LoggerFactory\Handler\RedisPubSubHandlerFactory;
use Mimmi20\LoggerFactory\Handler\RollbarHandlerFactory;
use Mimmi20\LoggerFactory\Handler\RotatingFileHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SamplingHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SendGridHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SlackHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SlackWebhookHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SocketHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SqsHandlerFactory;
use Mimmi20\LoggerFactory\Handler\StreamHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SwiftMailerHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SyslogHandlerFactory;
use Mimmi20\LoggerFactory\Handler\SyslogUdpHandlerFactory;
use Mimmi20\LoggerFactory\Handler\TeamsLogHandlerFactory;
use Mimmi20\LoggerFactory\Handler\TelegramBotHandlerFactory;
use Mimmi20\LoggerFactory\Handler\TestHandlerFactory;
use Mimmi20\LoggerFactory\Handler\WhatFailureGroupHandlerFactory;
use Mimmi20\LoggerFactory\Handler\ZendMonitorHandlerFactory;
use Mimmi20\LoggerFactory\Processor\GitProcessorFactory;
use Mimmi20\LoggerFactory\Processor\HostnameProcessorFactory;
use Mimmi20\LoggerFactory\Processor\IntrospectionProcessorFactory;
use Mimmi20\LoggerFactory\Processor\MemoryPeakUsageProcessorFactory;
use Mimmi20\LoggerFactory\Processor\MemoryUsageProcessorFactory;
use Mimmi20\LoggerFactory\Processor\MercurialProcessorFactory;
use Mimmi20\LoggerFactory\Processor\ProcessIdProcessorFactory;
use Mimmi20\LoggerFactory\Processor\PsrLogMessageProcessorFactory;
use Mimmi20\LoggerFactory\Processor\RequestHeaderProcessorFactory;
use Mimmi20\LoggerFactory\Processor\TagProcessorFactory;
use Mimmi20\LoggerFactory\Processor\UidProcessorFactory;
use Mimmi20\LoggerFactory\Processor\WebProcessorFactory;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Formatter\ElasticaFormatter;
use Monolog\Formatter\FlowdockFormatter;
use Monolog\Formatter\FluentdFormatter;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Formatter\LogmaticFormatter;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Formatter\MongoDBFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Formatter\WildfireFormatter;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\CouchDBHandler;
use Monolog\Handler\CubeHandler;
use Monolog\Handler\DeduplicationHandler;
use Monolog\Handler\DoctrineCouchDBHandler;
use Monolog\Handler\DynamoDbHandler;
use Monolog\Handler\ElasticaHandler;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\FleepHookHandler;
use Monolog\Handler\FlowdockHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\IFTTTHandler;
use Monolog\Handler\InsightOpsHandler;
use Monolog\Handler\LogEntriesHandler;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\LogmaticHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\NoopHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\OverflowHandler;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\ProcessHandler;
use Monolog\Handler\PsrHandler;
use Monolog\Handler\PushoverHandler;
use Monolog\Handler\RedisHandler;
use Monolog\Handler\RedisPubSubHandler;
use Monolog\Handler\RollbarHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SamplingHandler;
use Monolog\Handler\SendGridHandler;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\SqsHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Handler\TestHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Handler\ZendMonitorHandler;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\HostnameProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MercurialProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\TagProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<int|string, string>>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'monolog_handlers' => $this->getMonologHandlerConfig(),
            'monolog_processors' => $this->getMonologProcessorConfig(),
            'monolog_formatters' => $this->getMonologFormatterConfig(),
            'monolog' => $this->getMonologConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<string, string>>
     */
    public function getDependencyConfig(): array
    {
        return [
            'aliases' => [
                LoggerInterface::class => Logger::class,
            ],
            'factories' => [
                ActivationStrategyPluginManager::class => ActivationStrategyPluginManagerFactory::class,
                Logger::class => LoggerFactory::class,
                MonologPluginManager::class => MonologPluginManagerFactory::class,
                MonologHandlerPluginManager::class => MonologHandlerPluginManagerFactory::class,
                MonologProcessorPluginManager::class => MonologProcessorPluginManagerFactory::class,
                MonologFormatterPluginManager::class => MonologFormatterPluginManagerFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function getMonologHandlerConfig(): array
    {
        return [
            'aliases' => [
                'amqp' => AmqpHandler::class,
                'browserconsole' => BrowserConsoleHandler::class,
                'buffer' => BufferHandler::class,
                'callbackfilter' => CallbackFilterHandler::class,
                'chromephp' => ChromePHPHandler::class,
                'couchdb' => CouchDBHandler::class,
                'cube' => CubeHandler::class,
                'deduplication' => DeduplicationHandler::class,
                'doctrinecouchdb' => DoctrineCouchDBHandler::class,
                'dynamodb' => DynamoDbHandler::class,
                'elastica' => ElasticaHandler::class,
                'elasticsearch' => ElasticsearchHandler::class,
                'errorlog' => ErrorLogHandler::class,
                'fallbackgroup' => FallbackGroupHandler::class,
                'filter' => FilterHandler::class,
                'fingerscrossed' => FingersCrossedHandler::class,
                'firephp' => FirePHPHandler::class,
                'fleephook' => FleepHookHandler::class,
                'flowdock' => FlowdockHandler::class,
                'gelf' => GelfHandler::class,
                'group' => GroupHandler::class,
                'ifttt' => IFTTTHandler::class,
                'insightops' => InsightOpsHandler::class,
                'logentries' => LogEntriesHandler::class,
                'loggly' => LogglyHandler::class,
                'logmatic' => LogmaticHandler::class,
                'mandrill' => MandrillHandler::class,
                'mongo' => MongoDBHandler::class,
                'nativemailer' => NativeMailerHandler::class,
                'newrelic' => NewRelicHandler::class,
                'noop' => NoopHandler::class,
                'null' => NullHandler::class,
                'overflow' => OverflowHandler::class,
                'phpconsole' => PHPConsoleHandler::class,
                'process' => ProcessHandler::class,
                'psr' => PsrHandler::class,
                'pushover' => PushoverHandler::class,
                'redis' => RedisHandler::class,
                'redispubsub' => RedisPubSubHandler::class,
                'rollbar' => RollbarHandler::class,
                'rotating' => RotatingFileHandler::class,
                'sampling' => SamplingHandler::class,
                'sendgrid' => SendGridHandler::class,
                'slack' => SlackHandler::class,
                'slackwebhook' => SlackWebhookHandler::class,
                'socket' => SocketHandler::class,
                'sqs' => SqsHandler::class,
                'stream' => StreamHandler::class,
                'swiftmailer' => SwiftMailerHandler::class,
                'syslog' => SyslogHandler::class,
                'syslogudp' => SyslogUdpHandler::class,
                'teams' => TeamsLogHandler::class,
                'telegrambot' => TelegramBotHandler::class,
                'test' => TestHandler::class,
                'whatfailuregrouphandler' => WhatFailureGroupHandler::class,
                'zend' => ZendMonitorHandler::class,
            ],
            'factories' => [
                AmqpHandler::class => AmqpHandlerFactory::class,
                BrowserConsoleHandler::class => BrowserConsoleHandlerFactory::class,
                BufferHandler::class => BufferHandlerFactory::class,
                CallbackFilterHandler::class => CallbackFilterHandlerFactory::class,
                ChromePHPHandler::class => ChromePHPHandlerFactory::class,
                CouchDBHandler::class => CouchDBHandlerFactory::class,
                CubeHandler::class => CubeHandlerFactory::class,
                DeduplicationHandler::class => DeduplicationHandlerFactory::class,
                DoctrineCouchDBHandler::class => DoctrineCouchDBHandlerFactory::class,
                DynamoDbHandler::class => DynamoDbHandlerFactory::class,
                ElasticaHandler::class => ElasticaHandlerFactory::class,
                ElasticsearchHandler::class => ElasticsearchHandlerFactory::class,
                ErrorLogHandler::class => ErrorLogHandlerFactory::class,
                FallbackGroupHandler::class => FallbackGroupHandlerFactory::class,
                FilterHandler::class => FilterHandlerFactory::class,
                FingersCrossedHandler::class => FingersCrossedHandlerFactory::class,
                FirePHPHandler::class => FirePHPHandlerFactory::class,
                FleepHookHandler::class => FleepHookHandlerFactory::class,
                FlowdockHandler::class => FlowdockHandlerFactory::class,
                GelfHandler::class => GelfHandlerFactory::class,
                GroupHandler::class => GroupHandlerFactory::class,
                IFTTTHandler::class => IFTTTHandlerFactory::class,
                InsightOpsHandler::class => InsightOpsHandlerFactory::class,
                LogEntriesHandler::class => LogEntriesHandlerFactory::class,
                LogglyHandler::class => LogglyHandlerFactory::class,
                LogmaticHandler::class => LogmaticHandlerFactory::class,
                MandrillHandler::class => MandrillHandlerFactory::class,
                MongoDBHandler::class => MongoDBHandlerFactory::class,
                NativeMailerHandler::class => NativeMailerHandlerFactory::class,
                NewRelicHandler::class => NewRelicHandlerFactory::class,
                NoopHandler::class => NoopHandlerFactory::class,
                NullHandler::class => NullHandlerFactory::class,
                OverflowHandler::class => OverflowHandlerFactory::class,
                PHPConsoleHandler::class => PHPConsoleHandlerFactory::class,
                ProcessHandler::class => ProcessHandlerFactory::class,
                PsrHandler::class => PsrHandlerFactory::class,
                PushoverHandler::class => PushoverHandlerFactory::class,
                RedisHandler::class => RedisHandlerFactory::class,
                RedisPubSubHandler::class => RedisPubSubHandlerFactory::class,
                RollbarHandler::class => RollbarHandlerFactory::class,
                RotatingFileHandler::class => RotatingFileHandlerFactory::class,
                SamplingHandler::class => SamplingHandlerFactory::class,
                SendGridHandler::class => SendGridHandlerFactory::class,
                SlackHandler::class => SlackHandlerFactory::class,
                SlackWebhookHandler::class => SlackWebhookHandlerFactory::class,
                SocketHandler::class => SocketHandlerFactory::class,
                SqsHandler::class => SqsHandlerFactory::class,
                StreamHandler::class => StreamHandlerFactory::class,
                SwiftMailerHandler::class => SwiftMailerHandlerFactory::class,
                SyslogHandler::class => SyslogHandlerFactory::class,
                SyslogUdpHandler::class => SyslogUdpHandlerFactory::class,
                TeamsLogHandler::class => TeamsLogHandlerFactory::class,
                TelegramBotHandler::class => TelegramBotHandlerFactory::class,
                TestHandler::class => TestHandlerFactory::class,
                WhatFailureGroupHandler::class => WhatFailureGroupHandlerFactory::class,
                ZendMonitorHandler::class => ZendMonitorHandlerFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function getMonologProcessorConfig(): array
    {
        return [
            'aliases' => [
                'git' => GitProcessor::class,
                'hostname' => HostnameProcessor::class,
                'introspection' => IntrospectionProcessor::class,
                'memorypeak' => MemoryPeakUsageProcessor::class,
                'memoryusage' => MemoryUsageProcessor::class,
                'mercurial' => MercurialProcessor::class,
                'processid' => ProcessIdProcessor::class,
                'psrlogmessage' => PsrLogMessageProcessor::class,
                'requestheader' => RequestHeaderProcessor::class,
                'tags' => TagProcessor::class,
                'uid' => UidProcessor::class,
                'web' => WebProcessor::class,
            ],
            'factories' => [
                GitProcessor::class => GitProcessorFactory::class,
                HostnameProcessor::class => HostnameProcessorFactory::class,
                IntrospectionProcessor::class => IntrospectionProcessorFactory::class,
                MemoryPeakUsageProcessor::class => MemoryPeakUsageProcessorFactory::class,
                MemoryUsageProcessor::class => MemoryUsageProcessorFactory::class,
                MercurialProcessor::class => MercurialProcessorFactory::class,
                ProcessIdProcessor::class => ProcessIdProcessorFactory::class,
                PsrLogMessageProcessor::class => PsrLogMessageProcessorFactory::class,
                RequestHeaderProcessor::class => RequestHeaderProcessorFactory::class,
                TagProcessor::class => TagProcessorFactory::class,
                UidProcessor::class => UidProcessorFactory::class,
                WebProcessor::class => WebProcessorFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function getMonologFormatterConfig(): array
    {
        return [
            'aliases' => [
                'chromePHP' => ChromePHPFormatter::class,
                'elastica' => ElasticaFormatter::class,
                'flowdock' => FlowdockFormatter::class,
                'fluentd' => FluentdFormatter::class,
                'gelf' => GelfMessageFormatter::class,
                'html' => HtmlFormatter::class,
                'json' => JsonFormatter::class,
                'line' => LineFormatter::class,
                'loggly' => LogglyFormatter::class,
                'logmatic' => LogmaticFormatter::class,
                'logstash' => LogstashFormatter::class,
                'mongodb' => MongoDBFormatter::class,
                'normalizer' => NormalizerFormatter::class,
                'scalar' => ScalarFormatter::class,
                'wildfire' => WildfireFormatter::class,
            ],
            'factories' => [
                ChromePHPFormatter::class => ChromePHPFormatterFactory::class,
                ElasticaFormatter::class => ElasticaFormatterFactory::class,
                FlowdockFormatter::class => FlowdockFormatterFactory::class,
                FluentdFormatter::class => FluentdFormatterFactory::class,
                GelfMessageFormatter::class => GelfMessageFormatterFactory::class,
                HtmlFormatter::class => HtmlFormatterFactory::class,
                JsonFormatter::class => JsonFormatterFactory::class,
                LineFormatter::class => LineFormatterFactory::class,
                LogglyFormatter::class => LogglyFormatterFactory::class,
                LogmaticFormatter::class => LogmaticFormatterFactory::class,
                LogstashFormatter::class => LogstashFormatterFactory::class,
                MongoDBFormatter::class => MongoDBFormatterFactory::class,
                NormalizerFormatter::class => NormalizerFormatterFactory::class,
                ScalarFormatter::class => ScalarFormatterFactory::class,
                WildfireFormatter::class => WildfireFormatterFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getMonologConfig(): array
    {
        return [
            'factories' => [
                \Monolog\Logger::class => MonologFactory::class,
            ],
        ];
    }
}
