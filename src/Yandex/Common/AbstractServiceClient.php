<?php
/**
 * Yandex PHP Library
 *
 * @copyright NIX Solutions Ltd.
 * @link https://github.com/nixsolutions/yandex-php-library
 */

/**
 * @namespace
 */
namespace Yandex\Common;

use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Yandex\Common\Exception\MissedArgumentException;
use Yandex\Common\Exception\ProfileNotFoundException;
use Yandex\Common\Exception\YandexException;

/**
 * Class AbstractServiceClient
 *
 * @package Yandex\Common
 *
 * @author   Eugene Zabolotniy <realbaziak@gmail.com>
 * @created  13.09.13 12:09
 */
abstract class AbstractServiceClient extends AbstractPackage
{
    /**
     * Request schemes constants
     */
    const HTTPS_SCHEME = 'https';
    const HTTP_SCHEME = 'http';

    /**
     * @var string
     */
    protected $serviceScheme = self::HTTPS_SCHEME;

    /**
     * Can be HTTP 1.0 or HTTP 1.1
     * @var string
     */
    protected $serviceProtocolVersion = '1.1';

    /**
     * @var string
     */
    protected $serviceDomain = '';

    /**
     * @var string
     */
    protected $servicePort = '';

    /**
     * @var string
     */
    protected $accessToken = '';

    /**
     * @var string
     */
    protected $libraryName = 'YandexDns';

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->libraryName . '/' . Version::$version;
    }

    /**
     * @param string $accessToken
     *
     * @return self
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $serviceDomain
     *
     * @return self
     */
    public function setServiceDomain($serviceDomain)
    {
        $this->serviceDomain = $serviceDomain;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceDomain()
    {
        return $this->serviceDomain;
    }

    /**
     * @param string $servicePort
     *
     * @return self
     */
    public function setServicePort($servicePort)
    {
        $this->servicePort = $servicePort;

        return $this;
    }

    /**
     * @return string
     */
    public function getServicePort()
    {
        return $this->servicePort;
    }

    /**
     * @param string $serviceScheme
     *
     * @return self
     */
    public function setServiceScheme($serviceScheme = self::HTTPS_SCHEME)
    {
        $this->serviceScheme = $serviceScheme;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceScheme()
    {
        return $this->serviceScheme;
    }

    /**
     * @param string $resource
     * @return string
     */
    public function getServiceUrl($resource = '')
    {
        return $this->serviceScheme . '://' . $this->serviceDomain . '/' . rawurlencode($resource);
    }

    /**
     * Check package configuration
     *
     * @return boolean
     */
    protected function doCheckSettings()
    {
        return true;
    }

    /**
     * prepareRequest
     *
     * @param \Guzzle\Http\Message\RequestInterface $request
     * @return RequestInterface
     */
    protected function prepareRequest(RequestInterface $request)
    {
        $request->setProtocolVersion($this->serviceProtocolVersion);
        $request->setHeader('Authorization', 'OAuth ' . $this->getAccessToken());
        $request->setHeader('Host', $this->getServiceDomain());
        $request->setHeader('User-Agent', $this->getUserAgent());
        $request->setHeader('Accept', '*/*');
        return $request;
    }

    /**
     * Sends a request
     *
     * @param \Guzzle\Http\Message\Request|\Guzzle\Http\Message\RequestInterface $request
     *
     * @throws Exception\MissedArgumentException
     * @throws Exception\ProfileNotFoundException
     * @throws Exception\YandexException
     * @throws ClientErrorResponseException
     * @return Response
     */
    protected function sendRequest(RequestInterface $request)
    {
        try {
            $request = $this->prepareRequest($request);
            $response = $request->send();
        } catch (ClientErrorResponseException $ex) {
            // get error from response
            $result = $request->getResponse()->json();

            // handle a service error message
            if (is_array($result) && isset($result['error'], $result['message'])) {
                switch ($result['error']) {
                    case 'MissedRequiredArguments':
                        throw new MissedArgumentException($result['message']);
                    case 'AssistantProfileNotFound':
                        throw new ProfileNotFoundException($result['message']);
                    default:
                        throw new YandexException($result['message']);
                }
            }

            // unknown error
            throw $ex;
        }

        return $response;
    }
}
