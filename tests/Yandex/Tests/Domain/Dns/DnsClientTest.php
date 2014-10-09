<?php

namespace Yandex\Tests\Domain\Dns;

use Yandex\Tests\TestCase;
use Yandex\Domain\Dns\DnsClient;
use Yandex\Tests\Domain\Dns\Fixtures\ResponseData;

class DnsClientTest extends TestCase
{
    public function testGetDomainRecords()
    {
        $data = array(array(
            'domain' => 'yourdomain.ru',
            'priority' => '',
            'ttl' => 21600,
            'subdomain' => 'www',
            'type' => 'A',
            'content' => '127.0.0.1',
            'record_id' => 342432432,
        ));

        $responseStub = $this->getMockBuilder('\Yandex\Domain\Dns\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $responseStub->expects($this->any())
            ->method('isSuccess')
            ->will($this->returnValue(true));
        $responseStub->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $requestStub = $this->getMock('\Yandex\Domain\Dns\Request');
        $requestStub->expects($this->any())
            ->method('send')
            ->will($this->returnValue($responseStub));

        $domainName = 'example.org';
        $accessToken = '1gsc2e3df76068ac87de6b49de32340a6034e3s81872238d07e7f3cm';
        $dns = new DnsClient($domainName, $accessToken);
        $dns->setRequest($requestStub);
        $response = $dns->getDomainRecords();
        var_dump($response);
        $this->assertEquals($response, $data);

        return $dns;
    }

    /**
     * @covers \Yandex\Domain\Dns\DnsClient::__construct
     * @covers \Yandex\Domain\Dns\DnsClient::setDomainName
     * @covers \Yandex\Domain\Dns\DnsClient::setAccessToken
     * @covers \Yandex\Domain\Dns\DnsClient::getDomainName
     * @covers \Yandex\Domain\Dns\DnsClient::getAccessToken
     * @covers \Yandex\Domain\Dns\DnsClient::setServiceDomain
     * @covers \Yandex\Domain\Dns\DnsClient::setServiceScheme
     * @covers \Yandex\Domain\Dns\DnsClient::getServiceDomain
     * @covers \Yandex\Domain\Dns\DnsClient::getServiceScheme
     * @covers \Yandex\Domain\Dns\DnsClient::getServiceUrl
     */
    public function testSettersAndGetters()
    {
        $domainName = 'example.org';
        $accessToken = '1gsc2e3df76068ac87de6b49de32340a6034e3s81872238d07e7f3cm';
        $dns = new DnsClient($domainName, $accessToken);
        $this->assertEquals($dns->getDomainName(), $domainName);
        $this->assertEquals($dns->getAccessToken(), $accessToken);

        $domainName = 'another.example.org';
        $accessToken = '3h0mth38fjctcys90lng1zziankktluacla5ikwqydjbn7he9a04nhzd';
        $dns->setDomainName($domainName);
        $dns->setAccessToken($accessToken);
        $this->assertEquals($dns->getDomainName(), $domainName);
        $this->assertEquals($dns->getAccessToken(), $accessToken);

        $serviceDomain = 'domain.com';
        $serviceScheme = 'http';
        $resource = 'api/v1';
        $dns->setServiceDomain($serviceDomain);
        $dns->setServiceScheme($serviceScheme);
        $this->assertEquals($dns->getServiceDomain(), $serviceDomain);
        $this->assertEquals($dns->getServiceScheme(), $serviceScheme);
        $this->assertEquals(
            $dns->getServiceUrl($resource),
            $serviceScheme . '://' . $serviceDomain . '/' . rawurlencode($resource)
        );
    }

    public function correctResponseProvider()
    {
        $fixtures = ResponseData::getFixtures();
        return array();
    }
}