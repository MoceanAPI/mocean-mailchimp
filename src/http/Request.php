<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 2:41 PM
 */

namespace Mocean\Mailchimp\http;

class Request
{
    public static $METHOD_GET = 0;
    public static $METHOD_POST = 1;

    private $curl;
    private $method = 0;
    private $params = array();
    private $headers = array();
    private $url;

    public function __construct($url)
    {
        $this->curl = curl_init();
        $this->url = $url;
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
    }

    public function send()
    {
        error_log('url: ' . $this->method === self::$METHOD_GET ? 'GET' : 'POST' . ' ' . $this->url);
        error_log('parameter: ' . $this->buildParams());
        if ($this->method === self::$METHOD_POST) {
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->buildParams());

        } else {
            curl_setopt($this->curl, CURLOPT_POST, 0);
            $this->url .= '?' . $this->buildParams();
        }

        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_merge($this->headers, array('Accept: application/json')));

        $response = curl_exec($this->curl);
        $statusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if ($statusCode === 0) {
            $response = curl_error($this->curl);
        }
        curl_close($this->curl);

        return new Response($statusCode, $response);
    }

    public function setMethod($method = 0)
    {
        $this->method = $method;
        return $this;
    }

    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function clearParams()
    {
        $this->params = array();
        return $this;
    }

    private function buildParams()
    {
        return http_build_query($this->params);
    }
}