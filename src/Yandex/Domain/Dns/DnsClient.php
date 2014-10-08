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
    const TYPE_A     = 'a';
    const TYPE_AAAA  = 'aaaa';
    const TYPE_CNAME = 'cname';
    const TYPE_MX    = 'mx';
    const TYPE_NS    = 'ns';
    const TYPE_SRV   = 'srv';
    const TYPE_TXT   = 'txt';
    const TYPE_SOA   = 'soa';

    const FIELD_CONTENT        = 'content';
    const FIELD_SUBDOMAIN      = 'subdomain';
    const FIELD_TTL            = 'ttl';
    const FIELD_PRIORITY       = 'priority';
    const FIELD_SRV_WEIGHT     = 'weight';
    const FIELD_SRV_PORT       = 'port';
    const FIELD_SRV_TARGET     = 'target';
    const FIELD_SOA_ADMIN_MAIL = 'admin_mail';
    const FIELD_SOA_REFRESH    = 'refresh';
    const FIELD_SOA_RETRY      = 'retry';
    const FIELD_SOA_EXPIRE     = 'expire';
    const FIELD_SOA_NEG_CACHE  = 'neg_cache';
    const FIELD_RECORD_ID      = 'record_id';
    const FIELD_RECORD_TYPE    = 'type';

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

        if ($response->isSuccess())
            return $response->getData();
        else
            return false;
    }

    /**
     * @param string $type
     * @param array $data
     * @return bool
     */
    public function addRecord($type, array $data)
    {
        $response = $this->manageRecord('add', $type, $data);

        return $response->isSuccess();
    }

    /**
     * @param integer $recordId
     * @param string $content
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @return bool
     */
    public function editARecordById($recordId, $content, $subdomain, $ttl)
    {
        return $this->editRecordByTypeAndId(self::TYPE_A, $recordId, array(
            self::FIELD_CONTENT   => $content,
            self::FIELD_SUBDOMAIN => $subdomain,
            self::FIELD_TTL       => $ttl,
        ));
    }

    /**
     * @param integer $recordId
     * @param integer $weight
     * @param integer $port
     * @param string $target
     * @param string|null $subdomain
     * @param integer|null $ttl
     * @param integer|null $priority
     * @return bool
     */
    public function editSrvRecordById($recordId, $weight, $port, $target, $subdomain = null, $ttl = null, $priority = null)
    {
        return $this->editRecordByTypeAndId(self::TYPE_SRV, $recordId, array(
            self::FIELD_SRV_WEIGHT => $weight,
            self::FIELD_SRV_PORT   => $port,
            self::FIELD_SRV_TARGET => $target,
            self::FIELD_SUBDOMAIN  => $subdomain,
            self::FIELD_TTL        => $ttl,
            self::FIELD_PRIORITY   => $priority,
        ));
    }

    /**
     * @param integer $recordId
     * @param string $adminMail
     * @param integer $refresh
     * @param integer $retry
     * @param integer $expire
     * @param integer $negChache
     * @param null|integer $ttl
     * @return bool
     */
    public function editSoaRecordById($recordId, $adminMail, $refresh, $retry, $expire, $negChache, $ttl = null)
    {
        return $this->editRecordByTypeAndId(self::TYPE_SOA, $recordId, array(
            self::FIELD_SOA_ADMIN_MAIL => $adminMail,
            self::FIELD_SOA_REFRESH    => $refresh,
            self::FIELD_SOA_RETRY      => $retry,
            self::FIELD_SOA_EXPIRE     => $expire,
            self::FIELD_SOA_NEG_CACHE  => $negChache,
            self::FIELD_TTL            => $ttl,
        ));
    }

    /**
     * @param string $type
     * @param integer $recordId
     * @param array $data
     * @return bool
     */
    public function editRecordByTypeAndId($type, $recordId, array $data)
    {
        $data[self::FIELD_RECORD_ID] = $recordId;
        $response = $this->manageRecord('edit', $type, $data);

        return $response->isSuccess();
    }

    protected function manageRecord($action, $type, array $data)
    {
        $method = $action . '_' . strtolower($type) . '_record';
        $response = $this->sendRequest($method, $data);

        return $response;
    }

    /**
     * @param integer $recordId
     * @return bool
     */
    public function deleteRecordById($recordId)
    {
        $response = $this->sendRequest('delete_record', array(self::FIELD_RECORD_ID => $recordId));

        return $response->isSuccess();
    }

    /**
     * @param array $cond
     * @param callback|null $callback
     * @return int
     */
    public function matchRecords(array $cond, $callback = null)
    {
        if (empty($cond)) return 0;
        if (!$records = $this->getDomainRecords()) return 0;
        $match = 0;

        foreach ($records as $record) {
            if ($this->match($record, $cond)) {
                if (null === $callback)
                    ++$match;
                elseif (is_callable($callback) && $callback($record))
                    ++$match;
            }
        }
        return $match;
    }

    /**
     * @param array $cond
     * @param array $data
     * @return int
     */
    public function editRecords(array $cond, array $data)
    {
        /** @TODO Rewrite to php 5.4 */
        $dns = $this;
        $result = $this->matchRecords(
            $cond,
            function($record) use ($data, $dns) {
                return $dns->editRecordByTypeAndId(
                    $record[DnsClient::FIELD_RECORD_TYPE],
                    $record[DnsClient::FIELD_RECORD_ID],
                    array_merge($record, $data)
                );
            }
        );

        return $result;
    }

    /**
     * @param array $cond
     * @return int
     */
    public function deleteRecords(array $cond)
    {
        /** @TODO Rewrite to php 5.4 */
        $dns = $this;
        $result = $this->matchRecords(
            $cond,
            function($record) use ($dns) {
                return $dns->deleteRecordById($record[DnsClient::FIELD_RECORD_ID]);
            }
        );

        return $result;
    }

    protected function match(array $record, array $cond)
    {
        return 0 === count(
            array_udiff_uassoc(
                $cond,
                array_uintersect_uassoc(
                    $record,
                    $cond,
                    'strcasecmp',
                    'strcasecmp'
                ),
                'strcasecmp',
                'strcasecmp'
            )
        );
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