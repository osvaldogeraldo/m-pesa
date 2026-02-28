<?php

namespace BrilliantMind\MPesa;

use Illuminate\Support\Facades\Facade;

class MPesaFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mpesa';
    }
}
