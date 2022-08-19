# monolog-laminas-factory

[Monolog](https://github.com/Seldaek/monolog) Factories for Laminas and Mezzio

This library was inspired by [psr11-monolog](https://gitlab.com/blazon/psr11-monolog)
and [monolog-factory](https://github.com/nikolaposa/monolog-factory).

[![Latest Stable Version](https://poser.pugx.org/mimmi20/monolog-laminas-factory/v/stable?format=flat-square)](https://packagist.org/packages/mimmi20/monolog-laminas-factory)
[![Latest Unstable Version](https://poser.pugx.org/mimmi20/monolog-laminas-factory/v/unstable?format=flat-square)](https://packagist.org/packages/mimmi20/monolog-laminas-factory)
[![License](https://poser.pugx.org/mimmi20/monolog-laminas-factory/license?format=flat-square)](https://packagist.org/packages/mimmi20/monolog-laminas-factory)

## Code Status

[![codecov](https://codecov.io/gh/mimmi20/monolog-laminas-factory/branch/master/graph/badge.svg)](https://codecov.io/gh/mimmi20/monolog-laminas-factory)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/mimmi20/monolog-laminas-factory.svg)](http://isitmaintained.com/project/mimmi20/monolog-laminas-factory "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/mimmi20/monolog-laminas-factory.svg)](http://isitmaintained.com/project/mimmi20/monolog-laminas-factory "Percentage of issues still open")

## Table of Contents

- [Installation](#installation)
- [Usage with Laminas and Mezzio](#usage-with-laminas-and-mezzio)
- [Configuration](#configuration)
  - [Minimal Configuration](#minimal-configuration)
  - [Full Configuration](#full-configuration)

## Installation

Run

```shell
composer require mimmi20/monolog-laminas-factory
```

## Usage with Laminas and Mezzio

You'll need to add configuration and register the services you'd like to use. There are number of ways to do that
but the recommended way is to create a new config file `config/autoload/logger.config.php`

## Configuration

config/autoload/monolog.global.php

```php
<?php
return [
    'log' => [
        \Laminas\Log\Logger::class => [
            'name' => 'name',
            'exceptionhandler' => false,
            'errorhandler' => false,
            'shutdownhandler' => false,
            'writers' => [], // Writers for Laminas Log
            'processors' => [], // Processors for Laminas Log
            'handlers' => [ // Handlers for Monolog
                // At the bare minimum you must include a default handler config.
                // Otherwise log entries will be sent to the void.
                'default' => [
                    'type' => 'stream',
                    'enabled' => true,
                    'options' => [
                        'stream' => '/var/log/some-log-file.txt',
                    ],
                ],
                
                // Another Handler
                'myOtherHandler' => [
                    'type' => 'stream',
                    'enabled' => false,
                    'options' => [
                        'stream' => '/var/log/someother-log-file.txt',
                    ],
                ],
            ],
            'monolog_processors' => [], // Processors for Monolog
        ],
    ],
];
```

## Minimal Configuration

A minimal configuration would consist of at least one default handler and one named service.
Please note that if you don't specify a default handler a NullHandler will be used
when you wire up the default logger.

### Minimal Example (using Mezzio for the example)

```php
<?php

return [
    'log' => [
        \Laminas\Log\Logger::class => [
            'name' => 'name',
            'handlers' => [
                'default' => [
                    'type' => 'stream',
                    'options' => [
                        'stream' => '/var/log/some-log-file.txt',
                    ],
                ],
            ],
        ],
    ],
];
```

## Full Configuration

### Full Example

```php
<?php

return [
    
    'log' => [
        \Laminas\Log\Logger::class => [
            'name' => 'name',
            'handlers' => [
                'default' => [
                    // A Handler type or pre-configured service from the container
                    'type' => 'stream',
                    
                    // Handler specific options.  See handlers below
                    'options' => [
                        'stream' => '/tmp/log_one.txt',
                    
                        // Optional: Formatter for the handler.
                        'formatter' => [
                            'type' => 'line',
                                
                            // formatter specific options.  See formatters below
                            'options' => [], 
                        ], 
                        
                        // Optional: Processor for the handler
                        'processors' => [
                            [
                                // A processor type or pre-configured service from the container
                                'type' => 'psrLogMessage',
                                
                                // processor specific options.  See processors below
                                'options' => [], 
                            ],
                        ],
                    ], 
                ],
            ],
            
            // Processors for Monolog/Logger
            'monolog_processors' => [
                // Array Keys are the names used for the processors
                'processorOne' => [
                    // A processor type or pre-configured service from the container
                    'type' => 'psrLogMessage',
                    
                    // processor specific options.  See processors below
                    'options' => [], 
                ],        
            ],
        ],
    ],
];
```

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
