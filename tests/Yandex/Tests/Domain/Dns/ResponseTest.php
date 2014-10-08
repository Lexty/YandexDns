<?php

namespace Yandex\Tests\Domain\Dns;

use Yandex\Tests\TestCase;
use Yandex\Domain\Dns\Response;
use Yandex\Tests\Domain\Dns\Fixtures\Responses;

class ResponseTest extends TestCase
{
    public function testResponse()
    {
        $fixtures = Responses::$responsesFixtures;

        $response = new Response($fixtures[0]);
        $this->assertTrue($response->isSuccess());

        $response = new Response($fixtures[1]);
        $this->assertFalse($response->isSuccess());
    }
}