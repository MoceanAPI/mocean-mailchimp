<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/2/2018
 * Time: 3:55 PM
 */

namespace Mocean\Mailchimp\config;

use Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException;

class Config
{
    private static $options;

    private function __construct()
    {
        //dont allow user to call new
    }

    public static function make(array $options)
    {
        self::$options = array_merge(self::defaultConfig(), $options);
    }

    private static function defaultConfig()
    {
        return array(
            'MOCEAN_API_VERSION' => '1',
            'MOCEAN_API' => 'rest.moceanapi.com/rest',
            'MOCEAN_API_KEY' => '',
            'MCOEAN_API_SECRET' => '',
            'MOCEAN_API_SENDER_ID' => '', //for mocean-from field (leave empty to use mailchimp list name as sender id)
            'MOCEAN_API_TEXT' => 'Testing Text',

            'MAILCHIMP_API_VERSION' => '3.0',
            'MAILCHIMP_API' => 'api.mailchimp.com',
            'MAILCHIMP_API_KEY' => '',
            'MAILCHIMP_LISTS_SEARCH_NAME' => '', //search for mailchimp list to be used (leave this empty to use all lists)
            'MAILCHIMP_CAMPAIGNS_SEARCH_TITLE' => '', //search for campaign title to be used (leave this empty to use all campaigns)

            'ALLOW_DUPLICATE_MEMBER' => false, //set to true if the you wish to send to the same member in different list multiple time
        );
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return self::$options;
    }

    /**
     * @param array $options
     */
    public static function setConfig(array $options)
    {
        self::$options = array_merge(self::$options, $options);
    }

    /**
     * @param $key
     * @return string
     * @throws ConfigKeyNotFoundException
     */
    public static function get($key)
    {
        if (!self::$options) {
            self::make(array());
        }

        if (!array_key_exists($key, self::$options)) {
            throw new ConfigKeyNotFoundException("config key ($key) not found!");
        }
        return self::$options[$key];
    }
}