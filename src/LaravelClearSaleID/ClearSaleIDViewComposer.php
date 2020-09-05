<?php

namespace RodrigoPedra\LaravelClearSaleID;

use Illuminate\Contracts\View\View;

class ClearSaleIDViewComposer
{
    /**
     * @var \RodrigoPedra\LaravelClearSaleID\ClearSaleIDService
     */
    private $clearSaleIDService;

    public function __construct(ClearSaleIDService $clearSaleIDService)
    {
        $this->clearSaleIDService = $clearSaleIDService;
    }

    public function compose(View $view)
    {
        $view->with('sessionId', $this->clearSaleIDService->getSessionId());
        $view->with('appId', $this->clearSaleIDService->getAppId());
    }
}
