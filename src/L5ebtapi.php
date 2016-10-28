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

    public function init(array $attributes)
    {

        $this->tradingService->init($attributes);

    }

    public function getEbayOfficialTime(array $attributes)
    {

        return $this->tradingService->getEbayOfficialTime($attributes);

    }

    public function getEbayDetails(array $attributes)
    {

        return $this->tradingService->getEbayDetails($attributes);

    }

    public function getCategories(array $attributes)
    {

        return $this->tradingService->getCategories($attributes);

    }

    public function getCategoryFeatures(array $attributes)
    {

        return $this->tradingService->getCategoryFeatures($attributes);
        
    }

    public function getCategorySpecifics(array $attributes) {

        return $this->tradingService->getCategorySpecifics($attributes);

    }

    public function getItem(array $attributes)
    {

        return $this->tradingService->getItem($attributes);

    }

    public function uploadSiteHostedPictures(array $attributes, $image)
    {

        return $this->tradingService->uploadSiteHostedPictures($attributes, $image);

    }

    public function addFixedPriceItem(array $attributes)
    {

        return $this->tradingService->addFixedPriceItem($attributes);

    }

}
