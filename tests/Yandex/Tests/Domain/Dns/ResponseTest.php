<?php

namespace Yandex\Tests\Domain\Dns;

use Yandex\Tests\TestCase;
use Yandex\Domain\Dns\Response;
use Yandex\Tests\Domain\Dns\Fixtures\IncomingData;

class ResponseTest extends TestCase
{
    /**
     * @param string $incomingData
     * @param bool   $expectSuccess
     * @param string $expectStatus
     * @param array  $expectData
     * @dataProvider correctResponseProvider
     * @covers \Yandex\Domain\Dns\Response::__construct
     * @covers \Yandex\Domain\Dns\Response::isSuccess
     * @covers \Yandex\Domain\Dns\Response::getStatus
     * @covers \Yandex\Domain\Dns\Response::getData
     */
    public function testCorrectResponse($incomingData, $expectSuccess, $expectStatus, $expectData)
    {
        $response = new Response($incomingData);
        $this->assertEquals($response->isSuccess(), $expectSuccess);
        $this->assertEquals($response->getStatus(), $expectStatus);
        $this->assertEquals($response->getData(),   $expectData);
    }

    public function correctResponseProvider()
    {
        $fixtures = IncomingData::responsesFixtures();
        $expectData = array(array(
            'domain' => 'yourdomain.ru',
            'priority' => '',
            'ttl' => 21600,
            'subdomain' => 'www',
            'type' => 'A',
            'content' => '127.0.0.1',
            'record_id' => 342432432,
        ));
        return array(
            array($fixtures['correctSuccess'], true, 'ok', $expectData),
            array($fixtures['correctError'], false, 'error', $expectData),
            array($fixtures['correctEmptyData'], true, 'ok', array()),
            array(simplexml_load_string($fixtures['correctSuccess']), true, 'ok', $expectData),
            array(simplexml_load_string($fixtures['correctError']), false, 'error', $expectData),
        );
    }

    /**
     * @param string $incomingData
     * @dataProvider incorrectResponseProvider
     * @expectedException \Yandex\Common\Exception\YandexException
     * @expectedExceptionMessage Bad response
     * @covers \Yandex\Domain\Dns\Response::__construct
     */
    public function testIncorrectResponse($incomingData)
    {
        new Response($incomingData);
    }

    public function incorrectResponseProvider()
    {
        $fixtures = IncomingData::responsesFixtures();
        return array(
            array($fixtures['anotherStructure']),
            array($fixtures['invalidXml']),
            array(new \StdClass()),
        );
    }
}