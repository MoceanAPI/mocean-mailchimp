<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/2/2018
 * Time: 3:51 PM
 */

namespace Mocean\Mailchimp;

use Exception;
use Mocean\Mailchimp\config\Config;
use Mocean\Mailchimp\model\Campaigns;
use Mocean\Mailchimp\model\Lists;

class Mailchimp
{
    /**
     * Mailchimp constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new Exception("cURL support is required, but can't be found.");
        }

        //boot up config
        Config::make($config);
    }

    /**
     * get the lists object
     *
     * @return bool|Lists
     * @throws exceptions\ConfigKeyNotFoundException
     * @throws exceptions\InvalidKeyException
     * @throws exceptions\ListNotFoundException
     * @throws exceptions\MemberNotFoundException
     */
    public function lists()
    {
        return Lists::load();
    }

    /**
     * get the campaigns object
     *
     * @return bool|Campaigns
     * @throws exceptions\CampaignNotFoundException
     * @throws exceptions\ConfigKeyNotFoundException
     * @throws exceptions\InvalidKeyException
     * @throws exceptions\ListNotFoundException
     * @throws exceptions\MemberNotFoundException
     */
    public function campaigns()
    {
        return Campaigns::load();
    }

    /**
     * set config
     *
     * @param array $options
     */
    public function setConfig(array $options)
    {
        Config::setConfig($options);
    }

    /**
     * get the config array
     *
     * @return array
     */
    public function getConfigs()
    {
        return Config::getConfig();
    }
}