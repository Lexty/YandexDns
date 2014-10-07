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

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

/**
 * Class Request
 *
 * @category Yandex
 * @package Domain\Dns
 *
 * @author   Alexander Medvedev <alexandr.mdr@gmail.com>
 */
class Request
{
    /**
     * Sends a request
     *
     * @param string $serviceDomain
     * @param string $method
     * @param array $data
     * @throws ClientErrorResponseException
     * @return Response
     */
    public function send($serviceDomain, $method, array $data)
    {
        try {
            $client = new Client($serviceDomain);
            $request = $client->get($method . '.xml', null, array('query' => $data));
            $response = $request->send();
        } catch (ClientErrorResponseException $e) {
            throw $e;
        }
        return new Response($response->xml());
    }
}