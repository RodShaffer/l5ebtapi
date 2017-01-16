<?php

namespace rodshaffer\l5ebtapi\facades;

use Illuminate\Support\Facades\Facade;

class L5ebtapiFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'L5ebtapi';
    }

}