<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Logger\Service;

use MCP\Logger\MessageInterface;
use Mockery;
use PHPUnit_Framework_TestCase;

class NullServiceTest extends PHPUnit_Framework_TestCase
{
    public function testNothing()
    {
        $service = new NullService();

        $message = Mockery::mock(MessageInterface::class);

        $this->assertEquals(null, $service->send($message));
    }
}
