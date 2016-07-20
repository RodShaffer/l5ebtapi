<?php

namespace l5ebtapi\l5ebtapi;

use l5ebtapi\l5ebtapi\Http\Controllers\L5ebtapiController;

class L5ebtapiClass
{
    private $tradingService = null;

    /**
     * Create a new L5ebtapi Instance
     */
    public function __construct()
    {
        $this->tradingService = new L5ebtapiController('');
    }

}
