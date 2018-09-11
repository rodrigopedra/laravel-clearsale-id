<?php

namespace RodrigoPedra\LaravelClearSaleID;

use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use RodrigoPedra\ClearSaleID\Entity\Request\Order;
use RodrigoPedra\ClearSaleID\Environment\Production;
use RodrigoPedra\ClearSaleID\Environment\Sandbox;
use RodrigoPedra\ClearSaleID\Service\Analysis;
use RodrigoPedra\ClearSaleID\Service\Connector;
use RodrigoPedra\ClearSaleID\Service\Integration;

class ClearSaleIDService
{
    /** @var  \Illuminate\Http\Request */
    private $request;

    /** @var  string */
    private $appId;

    /** @var  \RodrigoPedra\ClearSaleID\Service\Analysis */
    private $analysisService;

    /**
     * ClearSaleIDService constructor.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Psr\Log\LoggerInterface $logger
     * @param  string                   $environment
     * @param  string                   $entityCode
     * @param  string                   $appId
     * @param  bool                     $isDebug
     */
    public function __construct(
        Request $request,
        LoggerInterface $logger,
        $environment,
        $entityCode,
        $appId,
        $isDebug
    ) {
        $this->request = $request;

        $this->appId = $appId;

        $this->analysisService = $this->buildAnalysisService( $environment, $logger, $entityCode, $isDebug );
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        // web
        if ($this->request->hasSession()) {
            return $this->request->session()->getId();
        }

        // api
        return md5( uniqid( rand(), true ) );
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        if ($this->request->hasSession()) {
            return 'WEB';
        }

        return 'API';
    }

    /**
     * @return string|null
     */
    public function getIp()
    {
        return $this->request->getClientIp();
    }

    /**
     * Método para envio de pedidos e retorno do status
     *
     * @param  \RodrigoPedra\ClearSaleID\Entity\Request\Order $order
     *
     * @return string
     * @throws \Exception
     */
    public function analysis( Order $order )
    {
        return $this->analysisService->analysis( $order );
    }

    /**
     * Retorna o status de aprovação de um pedido
     *
     * @param  string $orderId
     *
     * @return string
     */
    public function checkOrderStatus( $orderId )
    {
        return $this->analysisService->checkOrderStatus( $orderId );
    }

    /**
     * Método para atualizar o pedido com o status do pagamento
     *
     * @param  string $orderId
     * @param  string $newStatusCode
     * @param  string $notes
     *
     * @return bool
     */
    public function updateOrderStatus( $orderId, $newStatusCode, $notes = '' )
    {
        return $this->analysisService->updateOrderStatus( $orderId, $newStatusCode, $notes );
    }

    /**
     * Retorna os detalhes do pedido após o pedido de análise
     *
     * @return \RodrigoPedra\ClearSaleID\Entity\Response\PackageStatus
     */
    public function getPackageStatus()
    {
        return $this->analysisService->getPackageStatus();
    }

    /**
     * Retorna os detalhes do pedido após o pedido de análise
     *
     * @return \RodrigoPedra\ClearSaleID\Entity\Response\UpdateOrderStatus
     */
    public function getUpdateOrderStatus()
    {
        return $this->analysisService->getUpdateOrderStatus();
    }

    /**
     * @param  string                   $environment
     * @param  \Psr\Log\LoggerInterface $logger
     * @param  string                   $entityCode
     * @param  bool                     $isDebug
     *
     * @return \RodrigoPedra\ClearSaleID\Service\Analysis
     */
    private function buildAnalysisService( $environment, LoggerInterface $logger, $entityCode, $isDebug )
    {
        $environment = $environment === 'production'
            ? new Production( $entityCode, $logger )
            : new Sandbox( $entityCode, $logger );

        $environment->setDebug( boolval( $isDebug ) );

        $connector   = new Connector( $environment );
        $integration = new Integration( $connector );

        return new Analysis( $integration );
    }
}
