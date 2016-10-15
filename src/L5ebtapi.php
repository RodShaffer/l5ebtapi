<?php

namespace rodshaffer\l5ebtapi;

use rodshaffer\l5ebtapi\Http\Controllers\L5ebtapiController;

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

    public function getEbayOfficialTime($attributes)
    {

        return $this->tradingService->getEbayOfficialTime($attributes);

    }

    public function getEbayDetails($attributes)
    {

        return $this->tradingService->getEbayDetails($attributes);

    }

    public function getCategories($attributes)
    {

        return $this->tradingService->getCategories($attributes);

    }

    public function getCategoryFeatures($attributes)
    {

        return $this->tradingService->getCategoryFeatures($attributes);
        
    }

    public function getItem($attributes)
    {

        return $this->tradingService->getItem($attributes);

    }

    public function uploadSiteHostedPictures($attributes, $image)
    {

        return $this->tradingService->uploadSiteHostedPictures($attributes, $image);

    }

    public function addFixedPriceItem($attributes)
    {

        return $this->tradingService->addFixedPriceItem($attributes);

    }

}
