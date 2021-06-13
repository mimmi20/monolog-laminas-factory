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

namespace Mimmi20Test\LoggerFactory;

use Cascader\Cascader;
use Interop\Container\ContainerInterface;
use Mimmi20\LoggerFactory\CascaderFactory;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class CascaderFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testInvoce(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new CascaderFactory();

        self::assertInstanceOf(Cascader::class, $factory($container, ''));
    }
}
