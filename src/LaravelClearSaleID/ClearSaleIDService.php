<?php

namespace RodrigoPedra\LaravelClearSaleID;

use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use RodrigoPedra\ClearSaleID\Service\Analysis;
use RodrigoPedra\ClearSaleID\Service\Connector;
use RodrigoPedra\ClearSaleID\Environment\Sandbox;
use RodrigoPedra\ClearSaleID\Service\Integration;
use RodrigoPedra\ClearSaleID\Entity\Request\Order;
use RodrigoPedra\ClearSaleID\Environment\Production;
use RodrigoPedra\ClearSaleID\Entity\Response\PackageStatus;
use RodrigoPedra\ClearSaleID\Entity\Response\TransactionStatus;

class ClearSaleIDService
{
    /** @var Request */
    private $request;

    /** @var string */
    private $appId;

    /** @var Analysis */
    private $analysisService;

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

    public function getSessionId()
    {
        // web
        if ($this->request->hasSession()) {
            return $this->request->session()->getId();
        }

        // api
        return md5( uniqid( rand(), true ) );
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getOrigin()
    {
        if ($this->request->hasSession()) {
            return 'WEB';
        }

        return 'API';
    }

    public function getIp()
    {
        return $this->request->getClientIp();
    }

    /**
     * Método para envio de pedidos e retorno do status
     *
     * @param  Order $order
     *
     * @return string
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
     * @return boolean
     */
    public function updateOrderStatus( $orderId, $newStatusCode, $notes = '' )
    {
        return $this->analysisService->updateOrderStatus( $orderId, $newStatusCode, $notes );
    }

    /**
     * Retorna os detalhes do pedido após o pedido de análise
     *
     * @return PackageStatus
     */
    public function getPackageStatus()
    {
        return $this->analysisService->getPackageStatus();
    }

    /**
     * Retorna os detalhes do pedido após o pedido de análise
     *
     * @return TransactionStatus
     */
    public function getTransactionStatus()
    {
        return $this->analysisService->getTransactionStatus();
    }

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
