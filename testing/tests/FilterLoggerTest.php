<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Logger;

use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Mockery;

class FilterLoggerTest extends PHPUnit_Framework_TestCase
{
    public $logger;

    public function setUp()
    {
        $this->logger = Mockery::spy(LoggerInterface::class);
    }

    public function testAllMessagesLoggedByDefault()
    {
        $filter = new FilterLogger($this->logger);

        $filter->debug('test');
        $filter->alert('test 2');

        $this->logger
            ->shouldHaveReceived('log', ['debug', 'test', []]);
        $this->logger
            ->shouldHaveReceived('log', ['alert', 'test 2', []]);
    }

    public function testMessageNotLoggedWhenBelowLowestLevel()
    {
        $filter = new FilterLogger($this->logger, LogLevel::ERROR);

        $filter->debug('test');

        $this->logger
            ->shouldNotHaveReceived('log');
    }

    public function testMessageLoggedWhenAtLowestLevel()
    {
        $filter = new FilterLogger($this->logger, LogLevel::ERROR);

        $filter->error('test');

        $this->logger
            ->shouldHaveReceived('log', [LogLevel::ERROR, 'test', []]);
    }

    public function testMessageLoggedWhenAboveLowest()
    {
        $filter = new FilterLogger($this->logger, LogLevel::DEBUG);

        $filter->log(LogLevel::ERROR, 'test');

        $this->logger
            ->shouldHaveReceived('log', [LogLevel::ERROR, 'test', []]);
    }

    public function testChangingLowestLevel()
    {
        $filter = new FilterLogger($this->logger, 'warning');
        $filter->setLevel('alert');

        $filter->info('test');
        $filter->error('test 2');
        $filter->emergency('test 3');

        $this->logger
            ->shouldNotHaveReceived('log', ['info', 'test', []]);
        $this->logger
            ->shouldNotHaveReceived('log', ['error', 'test 2', []]);

        $this->logger
            ->shouldHaveReceived('log', ['emergency', 'test 3', []]);
    }

    public function testSetInvalidLevelThrowsException()
    {
        $this->expectException(Exception::class);

        $filter = new FilterLogger($this->logger);
        $filter->setLevel('foo');
    }

    public function testShouldNotLogInvalidLevel()
    {
        $filter = new FilterLogger($this->logger, LogLevel::WARNING);
        $filter->log('foo', 'this is a message');

        $this->logger
            ->shouldNotHaveReceived('log');
    }
}
