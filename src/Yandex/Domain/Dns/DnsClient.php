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
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @return bool
     */
    public function addARecord($content, $subdomain = null, $ttl = null)
    {
        return $this->addRecord('a', $content, $subdomain, $ttl);
    }

    /**
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @return bool
     */
    public function addAaaaRecord($content, $subdomain = null, $ttl = null)
    {
        return $this->addRecord('aaaa', $content, $subdomain, $ttl);
    }

    /**
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @return bool
     */
    public function addCnameRecord($content, $subdomain = null, $ttl = null)
    {
        return $this->addRecord('cname', $content, $subdomain, $ttl);
    }

    /**
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @param integer|null $priority
     * @return bool
     */
    public function addMxRecord($content, $subdomain = null, $ttl = null, $priority = null)
    {
        return $this->addRecord('mx', $content, $subdomain, $ttl, array('priority' => $priority));
    }

    /**
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @return bool
     */
    public function addNsRecord($content, $subdomain = null, $ttl = null)
    {
        return $this->addRecord('ns', $content, $subdomain, $ttl);
    }

    /**
     * @param integer $weight
     * @param integer $port
     * @param string $target
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @param integer|null $priority
     * @return bool
     */
    public function addSrvRecord($weight, $port, $target, $subdomain = null, $ttl = null, $priority = null)
    {
        return $this->addRecord('srv', null, $subdomain, $ttl, array(
            'weight'   => $weight,
            'port'     => $port,
            'target'   => $target,
            'priority' => $priority,
        ));
    }

    /**
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @return bool
     */
    public function addTxtRecord($content, $subdomain = null, $ttl = null)
    {
        return $this->addRecord('txt', $content, $subdomain, $ttl);
    }

    protected function addRecord($type, $content, $subdomain, $ttl, array $additional = array())
    {
        $method = 'add_' . $type . '_record';
        $data = array_filter($additional);
        if (null !== $content)   $data['content']   = $content;
        if (null !== $subdomain) $data['subdomain'] = $subdomain;
        if (null !== $ttl)       $data['ttl']       = $ttl;
        $response = $this->sendRequest($method, $data);

        return $response->isSuccess();
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