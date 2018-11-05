<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/1/2018
 * Time: 12:29 PM
 */

namespace Mocean\Mailchimp\api;

use Mocean\Mailchimp\config\Config;
use Mocean\Mailchimp\exceptions\InvalidKeyException;
use Mocean\Mailchimp\http\Request;

class MailChimpApi
{
    public static $errMsg;

    /**
     * Return the generated Mailchimp API url
     *
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    public static function getApiUrl()
    {
        if (!Config::get('MAILCHIMP_API')) {
            $url = 'api.mailchimp.com';
        } else {
            $url = Config::get('MAILCHIMP_API');
        }

        $apiKey = self::getApiKey();
        if (strpos($apiKey, '-') === false) {
            throw new InvalidKeyException("Invalid MailChimp API key");
        }
        $dc = explode('-', $apiKey);

        return 'https://' . $dc[1] . '.' . $url . '/' . self::getApiVer();
    }

    /**
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    private static function getApiKey()
    {
        if (!Config::get('MAILCHIMP_API_KEY')) {
            throw new InvalidKeyException('MailChimp Api Key Required');
        }

        return Config::get('MAILCHIMP_API_KEY');
    }

    /**
     * get mailchimp config api version
     *
     * @return string
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     */
    public static function getApiVer()
    {
        if (!Config::get('MAILCHIMP_API_VERSION')) {
            return '3.0';
        }

        return Config::get('MAILCHIMP_API_VERSION');
    }

    /**
     * get all mailchimp lists
     *
     * @return array|bool
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    public static function getLists()
    {
        $req = new Request(self::getApiUrl() . '/lists');
        $req->setParams(array(
            'fields' => 'lists.id,lists.name'
        ));
        self::appendApiKey($req);
        $res = $req->send();

        if ($res->isOk()) {
            return $res->data->lists;
        }

        self::$errMsg = $res->isCurlError() ? $res->data : $res->data->detail;
        return false;
    }

    /**
     * get all mailchimp campaigns
     *
     * @return array|bool
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    public static function getCampaigns()
    {
        $req = new Request(self::getApiUrl() . '/campaigns');
        $req->setParams(array(
            'fields' => 'campaigns.id,campaigns.status,campaigns.recipients,campaigns.settings.title'
        ));
        self::appendApiKey($req);
        $res = $req->send();

        if ($res->isOk()) {
            return $res->data->campaigns;
        }

        self::$errMsg = $res->isCurlError() ? $res->data : $res->data->detail;
        return false;
    }

    /**
     * get mailchimp members by list id
     *
     * @param $listId
     * @return array|bool
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    public static function getListMembers($listId)
    {
        $req = new Request(self::getApiUrl() . "/lists/$listId/members");
        $req->setParams(array(
            'fields' => 'members.email_address,members.status,members.merge_fields'
        ));
        self::appendApiKey($req);
        $res = $req->send();

        if ($res->isOk()) {
            return $res->data->members;
        }

        self::$errMsg = $res->isCurlError() ? $res->data : $res->data->detail;
        return false;
    }

    /**
     * @param Request $req
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws InvalidKeyException
     */
    private static function appendApiKey(Request $req)
    {
        $req->setHeaders(array(
            'Authorization: apikey ' . self::getApiKey()
        ));
    }
}