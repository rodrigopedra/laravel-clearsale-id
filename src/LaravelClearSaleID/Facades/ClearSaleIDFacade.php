<?php

namespace RodrigoPedra\LaravelClearSaleID\Facades;

use Illuminate\Support\Facades\Facade;

class ClearSaleIDFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'clearsale-id';
    }
}
