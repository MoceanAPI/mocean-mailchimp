<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 4:58 PM
 */

namespace Mocean\Mailchimp\api;

use Mocean\Mailchimp\config\Config;
use Mocean\Mailchimp\exceptions\InvalidKeyException;
use Mocean\Mailchimp\http\Request;

class MoceanApi
{
    public static $errMsg;

    /**
     * Return the generated Mocean API url
     *
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    public static function getApiUrl()
    {
        if (!Config::get('MOCEAN_API')) {
            $url = 'rest.moceanapi.com/rest';
        } else {
            $url = Config::get('MOCEAN_API');
        }

        self::getApiKey();
        self::getApiSecret();

        return 'https://' . $url . '/' . self::getApiVer();
    }

    /**
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    private static function getApiKey()
    {
        if (!Config::get('MOCEAN_API_KEY')) {
            throw new InvalidKeyException('Mocean Api Key Required');
        }

        return Config::get('MOCEAN_API_KEY');
    }

    /**
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    private static function getApiSecret()
    {
        if (!Config::get('MCOEAN_API_SECRET')) {
            throw new InvalidKeyException('Mocean Api Secret Required');
        }

        return Config::get('MCOEAN_API_SECRET');
    }

    /**
     * get mocean api config api version
     *
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     */
    public static function getApiVer()
    {
        if (!Config::get('MOCEAN_API_VERSION')) {
            return '1';
        }

        return Config::get('MOCEAN_API_VERSION');
    }

    /**
     * send sms
     *
     * @param array $params
     * @return bool|array
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    public static function sendSms(array $params)
    {
        if (!array_key_exists('mocean-from', $params) || !array_key_exists('mocean-to', $params) || !array_key_exists('mocean-text', $params)) {
            self::$errMsg = 'some params missing';
            return false;
        }

        $req = new Request(self::getApiUrl() . '/sms');
        if (!array_key_exists('mocean-api-key', $params) || !array_key_exists('mocean-api-secret', $params)) {
            unset($params['mocean-api-key'], $params['mocean-api-secret']);
            self::appendApiKey($req);
        }
        self::setExtra($req);
        $res = $req->setMethod(Request::$METHOD_POST)
            ->setParams(array(
                'mocean-from' => $params['mocean-from'],
                'mocean-to' => $params['mocean-to'],
                'mocean-text' => $params['mocean-text']
            ))
            ->send();

        if ($res->isOk()) {
            return $res->data;
        }

        self::$errMsg = $res->isCurlError() ? $res->data : $res->data->err_msg;
        return false;
    }

    /**
     * @param Request $req
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    private static function appendApiKey(Request $req)
    {
        $req->setParams(array(
            'mocean-api-key' => self::getApiKey(),
            'mocean-api-secret' => self::getApiSecret(),
        ));
    }

    private static function setExtra(Request $req)
    {
        $req->setParams(array(
            'mocean-resp-format' => 'JSON',
            'mocean-medium' => 'MAILCHIMP'
        ));
    }
}
