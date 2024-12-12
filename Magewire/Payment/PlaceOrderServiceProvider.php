<?php


namespace Monext\HyvaPayline\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;

class PlaceOrderServiceProvider extends AbstractPlaceOrderService
{
    public function __construct(
        CartManagementInterface $cartManagement,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($cartManagement);
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();

        return $payment->getAdditionalInformation()["returnURL"];
    }
}
