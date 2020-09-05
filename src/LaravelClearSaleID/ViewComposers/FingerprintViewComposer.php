<?php

namespace RodrigoPedra\LaravelClearSaleID\ViewComposers;

use Illuminate\Contracts\View\View;
use RodrigoPedra\LaravelClearSaleID\ClearSaleIDService;

class FingerprintViewComposer
{
    /**
     * @var \RodrigoPedra\LaravelClearSaleID\ClearSaleIDService
     */
    private $clearSaleIDService;

    public function __construct(ClearSaleIDService $clearSaleIDService)
    {
        $this->clearSaleIDService = $clearSaleIDService;
    }

    public function compose(View $view): void
    {
        $view->with('sessionId', $this->clearSaleIDService->getSessionId());
        $view->with('appId', $this->clearSaleIDService->getAppId());
    }
}
