<?php
/**
 * Created by PhpStorm.
 * User: Neoson Lam
 * Date: 11/5/2018
 * Time: 9:13 AM
 */

namespace Mocean\Mailchimp\model;


use Mocean\Mailchimp\api\MailChimpApi;
use Mocean\Mailchimp\config\Config;
use Mocean\Mailchimp\exceptions\CampaignNotFoundException;

class Campaigns
{
    private $campaigns;
    private $lists;

    /**
     * Campaigns constructor.
     * @param array $campaigns
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     * @throws \Mocean\Mailchimp\exceptions\ListNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\MemberNotFoundException
     */
    private function __construct(array $campaigns)
    {
        $this->campaigns = $campaigns;
        $this->lists = Lists::loadByCampaigns($this->campaigns);
    }

    /**
     * get the campaign json response
     *
     * @return array
     */
    public function get()
    {
        return $this->campaigns;
    }

    /**
     * get Lists object from this campaign
     *
     * @return Lists
     */
    public function lists()
    {
        return $this->lists;
    }

    /**
     * load campaign
     *
     * @return bool|Campaigns
     * @throws CampaignNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\ConfigKeyNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\InvalidKeyException
     * @throws \Mocean\Mailchimp\exceptions\ListNotFoundException
     * @throws \Mocean\Mailchimp\exceptions\MemberNotFoundException
     */
    public static function load()
    {
        error_log('Loading campaigns from mailchimp api');
        $campaigns = MailChimpApi::getCampaigns();
        if (!$campaigns) {
            error_log('Error while loading campaigns (' . MailChimpApi::$errMsg . ')');
            return false;
        }

        error_log('Total campaign from mailchimp: ' . count($campaigns));
        if (count($campaigns) <= 0) {
            throw new CampaignNotFoundException('this mailchimp account dont have any campaign');
        }
        $selectCampaigns = array();

        if ($configCampaignTitle = Config::get('MAILCHIMP_CAMPAIGNS_SEARCH_TITLE')) {
            $searchNames = explode(',', $configCampaignTitle);

            foreach ($searchNames as $searchName) {
                foreach ($campaigns as $campaign) {
                    if (strtoupper($campaign->settings->title) === strtoupper($searchName)) {
                        $selectCampaigns[] = $campaign;
                        break;
                    }
                }
            }
            error_log('Campaign is define in config, currently using campaign: (' . $configCampaignTitle . ')');
        } else {
            foreach ($campaigns as $campaign) {
                $selectCampaigns[] = $campaign;
            }
            error_log('Campaign is not define in config, currently using all campaigns');
        }

        if (count($selectCampaigns) <= 0) {
            throw new CampaignNotFoundException();
        }
        return new Campaigns($selectCampaigns);
    }
}