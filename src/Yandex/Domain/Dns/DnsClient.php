<?php
/**
 * Yandex DNS
 *
 * @copyright Alexander Medvedev <alexandr.mdr@gmail.com>
 * @link https://github.com/Lexty/YandexDns
 */

/**
 * @namespace
 */
namespace Yandex\Domain\Dns;

use Yandex\Common\AbstractServiceClient;

/**
 * Class DnsClient
 *
 * @category Yandex
 * @package Domain\Dns
 *
 * @author   Alexander Medvedev <alexandr.mdr@gmail.com>
 */
class DnsClient extends AbstractServiceClient
{
    /**
     * API domain
     *
     * @var string
     */
    protected $serviceDomain = 'pddimp.yandex.ru/nsapi';

    /**
     * @var string
     */
    protected $domainName = '';

    /**
     * @param string $domainName Domain name
     * @param string $token access token
     */
    public function __construct($domainName, $token = '')
    {
        $this->setDomainName($domainName);
        $this->setAccessToken($token);
    }

    /**
     * @param string $domainName
     *
     * @return self
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    public function getRequestUrl($path)
    {
        return parent::getServiceUrl() . $path;
    }

    /**
     * @return Response
     */
    public function getDomainRecords()
    {
        $response = $this->sendRequest('get_domain_records');

        return $response->getData();
    }

    /**
     * @param string $method
     * @param array $data
     * @return Response
     */
    protected function sendRequest($method, array $data = array())
    {
        $data = array_merge($data, array(
            'token'  => $this->getAccessToken(),
            'domain' => $this->getDomainName()
        ));
        $request = new Request();
        $response = $request->send($this->getServiceUrl(), $method, $data);

        return $response;
    }
}