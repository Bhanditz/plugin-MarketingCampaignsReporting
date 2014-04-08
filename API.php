<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */
namespace Piwik\Plugins\AdvancedCampaignReporting;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * API for plugin AdvancedCampaignReporting
 *
 * @package AdvancedCampaignReporting
 * @method static \Piwik\Plugins\AdvancedCampaignReporting\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded = false, $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = Archive::getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS));
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        return $dataTable;
    }

    public function getName($idSite, $period, $date, $segment = false, $expanded = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
        return $dataTable;
    }

    public function getKeywordContentFromNameId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_NAME_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
        return $dataTable;
    }

    public function getKeyword($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_KEYWORD_RECORD_NAME, $idSite, $period, $date, $segment);
        return $dataTable;
    }

    public function getSource($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_SOURCE_RECORD_NAME, $idSite, $period, $date, $segment);
        return $dataTable;
    }

    public function getMedium($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_MEDIUM_RECORD_NAME, $idSite, $period, $date, $segment);
        return $dataTable;
    }

    public function getContent($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGN_CONTENT_RECORD_NAME, $idSite, $period, $date, $segment);
        return $dataTable;
    }

    public function getSourceMedium($idSite, $period, $date, $segment = false, $expanded = false)
    {
        $dataTable = $this->getDataTable(Archiver::HIERARCHICAL_SOURCE_MEDIUM_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);
        return $dataTable;
    }

    public function getNameFromSourceMediumId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::HIERARCHICAL_SOURCE_MEDIUM_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
        return $dataTable;
    }

}