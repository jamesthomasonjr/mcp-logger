<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\Logger;

use HttpRequest;
use MCP\Logger\Message\Message;
use MCP\Logger\Service\PeclHttpService;
use MCP\Logger\Testing\IntegrationTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * @coversNothing
 * @group integration
 */
class PeclHttpTest extends PHPUnit_Framework_TestCase
{
    use IntegrationTestTrait;

    public function test()
    {
        $request = new HttpRequest;
        $request->setOptions([
            'timeout' => 5,
            'connecttimeout' => 5,
            'useragent' => 'MCP-TEST'
        ]);

        $service = new PeclHttpService($request, $this->renderer, $this->uri);

        $message = new Message(array_merge($this->defaultMessage, ['message' => 'PECL ' . $this->defaultMessage['message']]));
        $response = $service->send($message);

        $this->assertNull($response);
    }
}
