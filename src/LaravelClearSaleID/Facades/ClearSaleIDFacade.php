<?php

namespace RodrigoPedra\LaravelClearSaleID\Facades;

use Illuminate\Support\Facades\Facade;
use RodrigoPedra\LaravelClearSaleID\ClearSaleIDService;

class ClearSaleIDFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ClearSaleIDService::class;
    }
}
