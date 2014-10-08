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
     */
    public function __construct($response)
    {
        return $this->parseResponse($response);
    }

    public function isSuccess()
    {
        return self::STATUS_SUCCESS === $this->status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    protected function parseResponse($xml)
    {
        if (is_object($xml) && is_a($xml, '\SimpleXMLElement')) {
            $sxe = $xml;
        } else {
            $sxe = $this->stringToSimpleXMLElement($xml);
        }

        $this->status = (string) $sxe->domains->error;

        if (null !== $sxe->domains->domain[0]->response->record) {
            foreach ($sxe->domains->domain[0]->response->record as $record) {
                $content = (string)$record;
                $record = (array)$record;
                $record = $record['@attributes'];
                $record['content'] = $content;
                $record['record_id'] = $record['id'];
                unset($record['id']);
                $this->data[] = $record;
            }
        }

        return $this;
    }

    /**
     * @param string $xml
     * @throws \Exception
     * @return \SimpleXMLElement
     */
    protected function stringToSimpleXMLElement($xml)
    {
        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($xml);
        if (libxml_get_errors()) {
            throw new \Exception('Bad response');
        }
        return $sxe;
    }
}