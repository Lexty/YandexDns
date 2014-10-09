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

use Yandex\Common\Exception\YandexException;

/**
 * Class Response
 *
 * @category Yandex
 * @package Domain\Dns
 *
 * @author   Alexander Medvedev <alexandr.mdr@gmail.com>
 */
class Response
{
    const STATUS_SUCCESS = 'ok';

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $status = '';

    /**
     * @param string|\SimpleXMLElement $response
     * @return self $this
     * @throws YandexException
     */
    public function __construct($response)
    {
        return $this->parseResponse($response);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string|\SimpleXMLElement $xml
     * @return self $this
     * @throws YandexException
     * @codeCoverageIgnore
     */
    protected function parseResponse($xml)
    {
        if (is_object($xml) && is_a($xml, '\SimpleXMLElement')) {
            $sxe = $xml;
        } else {
            $sxe = $this->stringToSimpleXML($xml);
        }

        /** @TODO Rewrite to php 5.5 */
        // Check exists xml chain to 'error' node
        if (1 === count($error = $sxe->xpath('/page/domains/error'))) {
            $this->status = (string) $error[0];
        } else {
            throw new YandexException('Bad response');
        }

        /** @TODO Rewrite to php 5.5 */
        // Check exists xml chain to 'record' nodes
        if (0 !== count($records = $sxe->xpath('/page/domains/domain/response/record'))) {
            foreach ($records as $record) {
                $content = (string)$record;
                $record = (array)$record;
                $record = $record['@attributes'];
                $record['content'] = $content;
                $record['record_id'] = $record['id'];
                unset($record['id']);
                $this->data[] = $record;
            }
//        /** @TODO Rewrite to php 5.5 */
//        } elseif (0 !== count($records = $sxe->xpath('/page/domains/domain/response'))) {
//            throw new YandexException('Bad response');
        }

        return $this;
    }

    /**
     * @param string $xml
     * @throws YandexException
     * @return \SimpleXMLElement
     * @codeCoverageIgnore
     */
    protected function stringToSimpleXML($xml)
    {
        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($xml);
        if (libxml_get_errors()) {
            throw new YandexException('Bad response');
        }
        return $sxe;
    }
}