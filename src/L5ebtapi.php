<?php

namespace l5ebtapi\l5ebtapi;

use l5ebtapi\l5ebtapi\Http\Controllers\L5ebtapiController;

class L5ebtapi
{
    private $tradingService;

    /**
     * Create a new L5ebtapi Instance.
     */
    public function __construct()
    {
        $this->tradingService = new L5ebtapiController();
    }

    public function init($attributes)
    {

        $this->tradingService->init($attributes);

    }

    public function getEbayOfficialTime()
    {

        return $this->tradingService->getEbayOfficialTime();

    }

    public function getEbayDetails($detailName)
    {

        return $this->tradingService->getEbayDetails($detailName);

    }

    public function uploadSiteHostedPictures($multiPartImageData, $imageName)
    {

        return $this->tradingService->uploadSiteHostedPictures($multiPartImageData, $imageName);

    }

    public function addFixedPriceItem(array $attributes)
    {

        return $this->tradingService->addFixedPriceItem($attributes);

    }

}
