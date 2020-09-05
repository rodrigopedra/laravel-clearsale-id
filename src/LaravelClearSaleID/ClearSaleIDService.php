<?php

namespace RodrigoPedra\LaravelClearSaleID;

use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use RodrigoPedra\ClearSaleID\Entity\Request\Order;
use RodrigoPedra\ClearSaleID\Entity\Response\PackageStatus;
use RodrigoPedra\ClearSaleID\Entity\Response\UpdateOrderStatus;
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

    public function __construct(
        Request $request,
        LoggerInterface $logger,
        string $environment,
        string $entityCode,
        string $appId,
        bool $isDebug
    ) {
        $this->request = $request;
        $this->appId = $appId;
        $this->analysisService = $this->makeAnalysisService($environment, $logger, $entityCode, $isDebug);
    }

    public function getSessionId(): string
    {
        if ($this->request->hasSession()) {
            return $this->request->session()->getId();
        }

        return md5(uniqid(rand(), true));
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getOrigin(): string
    {
        if ($this->request->hasSession()) {
            return 'WEB';
        }

        return 'API';
    }

    public function getIp(): ?string
    {
        return $this->request->getClientIp();
    }

    /**
     * Método para envio de pedidos e retorno do status
     *
     * @param  \RodrigoPedra\ClearSaleID\Entity\Request\Order  $order
     * @return string
     * @throws \SoapFault
     */
    public function analysis(Order $order)
    {
        return $this->analysisService->analysis($order);
    }

    /**
     * Retorna o status de aprovação de um pedido
     *
     * @param  string  $orderId
     * @return string
     * @throws \SoapFault
     */
    public function checkOrderStatus(string $orderId)
    {
        return $this->analysisService->checkOrderStatus($orderId);
    }

    /**
     * Método para atualizar o pedido com o status do pagamento
     *
     * @param  string  $orderId
     * @param  string  $newStatusCode
     * @param  string  $notes
     * @return bool
     * @throws \SoapFault
     */
    public function updateOrderStatus(string $orderId, string $newStatusCode, string $notes = ''): bool
    {
        return $this->analysisService->updateOrderStatus($orderId, $newStatusCode, $notes);
    }

    /**
     * Retorna os detalhes do pedido após o pedido de análise
     *
     * @return \RodrigoPedra\ClearSaleID\Entity\Response\PackageStatus
     */
    public function getPackageStatus(): PackageStatus
    {
        return $this->analysisService->getPackageStatus();
    }

    /**
     * Retorna os detalhes do pedido após o pedido de análise
     *
     * @return \RodrigoPedra\ClearSaleID\Entity\Response\UpdateOrderStatus
     */
    public function getUpdateOrderStatus(): UpdateOrderStatus
    {
        return $this->analysisService->getUpdateOrderStatus();
    }

    private function makeAnalysisService(
        string $environment,
        LoggerInterface $logger,
        string $entityCode,
        bool $isDebug
    ): Analysis {
        $environment = $environment === 'production'
            ? new Production($entityCode, $logger)
            : new Sandbox($entityCode, $logger);

        $environment->setDebug(boolval($isDebug));

        $connector = new Connector($environment);
        $integration = new Integration($connector);

        return new Analysis($integration);
    }
}
