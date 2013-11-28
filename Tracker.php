<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package AdvancedCampaignReporting
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;


use Piwik\Common;
use Piwik\Tracker\PageUrl;
use Piwik\UrlHelper;

class Tracker
{
    private $request;

    const CAMPAIGN_NAME_FIELD = 'campaign_name';
    const CAMPAIGN_KEYWORD_FIELD = 'campaign_keyword';
    const CAMPAIGN_SOURCE_FIELD = 'campaign_source';
    const CAMPAIGN_MEDIUM_FIELD = 'campaign_medium';
    const CAMPAIGN_CONTENT_FIELD = 'campaign_content';
    const CAMPAIGN_ID_FIELD = 'campaign_id';

    public function __construct(\Piwik\Tracker\Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    protected function getCampaignParameters()
    {
        return array(
            self::CAMPAIGN_NAME_FIELD => array('pk_campaign', 'utm_campaign'),
            self::CAMPAIGN_KEYWORD_FIELD => array('pk_keyword', 'utm_term'),
            self::CAMPAIGN_SOURCE_FIELD => array('pk_source', 'utm_source'),
            self::CAMPAIGN_MEDIUM_FIELD => array('pk_medium', 'utm_medium'),
            self::CAMPAIGN_CONTENT_FIELD => array('pk_content', 'utm_content'),
            self::CAMPAIGN_ID_FIELD => array('pk_cid', 'utm_id'),
        );
    }

    public function updateNewVisitWithCampaign(&$visitor)
    {
        $campaignDimensions = $this->detectAdvancedCampaignDimensions();

        if(empty($campaignDimensions)) {

            // If for some reason a campaign was detected in Core Tracker
            // but not here, copy that campaign to the Advanced Campaign
            if($visitor['referer_type'] != Common::REFERRER_TYPE_CAMPAIGN) {
                return ;
            }
            $campaignDimensions = array(
                self::CAMPAIGN_NAME_FIELD => $visitor['referer_name']
            );
            if(!empty($visitor['referer_keyword'])) {
                $campaignDimensions[self::CAMPAIGN_KEYWORD_FIELD] = $visitor['referer_keyword'];
            }
        }

        Common::printDebug("Found Advanced Campaign: ");
        Common::printDebug($campaignDimensions);

        // Set the new campaign fields on the visitor
        $visitor = array_merge($visitor, $campaignDimensions);

        // Overwrite core referer_ fields when an advanced campaign was detected
        $visitor['referer_type'] = Common::REFERRER_TYPE_CAMPAIGN;

        if(isset($visitor[self::CAMPAIGN_NAME_FIELD])) {
            $visitor['referer_name'] = $visitor[self::CAMPAIGN_NAME_FIELD];
        }
        if(isset($visitor[self::CAMPAIGN_KEYWORD_FIELD])) {
            $visitor['referer_keyword'] = $visitor[self::CAMPAIGN_KEYWORD_FIELD];
        }
    }

    protected function detectAdvancedCampaignDimensions()
    {
        $landingUrl = $this->request->getParam('url');
        $landingUrl = PageUrl::cleanupUrl($landingUrl);
        $landingUrlParsed = @parse_url($landingUrl);

        if (!isset($landingUrlParsed['query'])
            && !isset($landingUrlParsed['fragment'])
        ) {
            return false;
        }

        $campaignDimensions = array();

        // 1) Detect campaign from query string
        if (isset($landingUrlParsed['query'])) {
            $campaignDimensions = $this->detectCampaignFromString($landingUrlParsed['query']);
        }
        // 2) Detect from fragment #hash
        if (empty($campaignDimensions) && isset($landingUrlParsed['fragment'])) {
            $campaignDimensions = $this->detectCampaignFromString($landingUrlParsed['fragment']);
        }

        return $campaignDimensions;
    }


    /**
     * @param string $queryString
     * @return array of campaign dimensions
     */
    protected function detectCampaignFromString($queryString)
    {
        $parameters = $this->getCampaignParameters();

        $campaignDimensions = array();
        foreach($parameters as $sqlField => $requestParams) {
            foreach($requestParams as $campaignDimensionParam) {
                $value = $this->getValueFromQueryString($campaignDimensionParam, $queryString);
                if(!empty($value)) {
                    $campaignDimensions[$sqlField] = $value;
                    break 1;
                }
            }
        }
        return $campaignDimensions;
    }

    /**
     * @param $param
     * @param $queryString
     * @return bool|null|string
     */
    protected function getValueFromQueryString($param, $queryString)
    {
        $valueFromRequest = UrlHelper::getParameterFromQueryString($queryString, $param);
        $valueFromRequest = trim(urldecode($valueFromRequest));
        $valueFromRequest = Common::mb_strtolower($valueFromRequest);
        $valueFromRequest = substr($valueFromRequest, 0, 250);
        if (!empty($valueFromRequest)) {
            return $valueFromRequest;
        }
        return false;
    }
}