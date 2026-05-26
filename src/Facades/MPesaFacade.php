<?php

namespace BrilliantMind\MPesa\Facades;

use Illuminate\Support\Facades\Facade;

class MPesaFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mpesa';
    }
}
