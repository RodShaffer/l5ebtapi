<?php

namespace rodshaffer\l5ebtapi\Facades;

use Illuminate\Support\Facades\Facade;

class L5ebtapi extends Facade
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