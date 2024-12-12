<?php

namespace Monext\HyvaPayline\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DeferPlaceOrderServiceProvider extends AbstractPlaceOrderService
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    protected $context;

//    protected $orderId = null;

    protected $quote = null;

    protected $oneclick = false;

    /**
     * @param CartManagementInterface $cartManagement
     * @param PaymentHelper $paymentHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        CartManagementInterface  $cartManagement,
        PaymentHelper $paymentHelper,
        OrderRepositoryInterface $orderRepository,
        Context $context,
    ) {
        parent::__construct($cartManagement);
        $this->paymentHelper = $paymentHelper;
        $this->orderRepository = $orderRepository;
        $this->context = $context;
    }

    /**
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $paymentMethod = $this->paymentHelper->getMethodInstance($quote->getPayment()->getMethod());

        $order = $this->orderRepository->get($orderId);

        $checkoutUrl = $order->getPayment()->getAdditionalInformation('payment_url');
        if ($checkoutUrl) {
            return $checkoutUrl;
        } else {
          return parent::REDIRECT_PATH;
        }

    }

    public function canRedirect(): bool
    {
        $orderData = $this->getData();
//        $integrationType = $this->scopeConfig->getValue('payment/' . static::PAYMENT_METHOD . '/integration_type',
//            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        if ($integrationType == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
//
//        }
//      if(!$this->payplugConfig->isIntegrated() || ($this->payplugConfig->isIntegrated() && $this->oneclick) ){
//        return true;
//      }

      return true;
    }

    public function placeOrder(Quote $quote): int
    {

      $payment = $quote->getPayment()->getAdditionalInformation();
      if(!empty($payment["payplug_payments_customer_card_id"])){
        $this->oneclick = true;
      }

      return (int) $this->cartManagement->placeOrder($quote->getId(), $quote->getPayment());
    }
    public function evaluateCompletion(EvaluationResultFactory $resultFactory, ?int $orderId = null): EvaluationResultInterface
    {

      if( /*!$this->payplugConfig->isIntegrated() ||*/ $this->oneclick ){
        return parent::evaluateCompletion($resultFactory, $orderId);
      }

      // The order ID is required and will only be passed in if the order was created.
      if ($orderId) {

        // Best practice is always to create a batch to let others inject more if required.
        return $resultFactory->createBatch()->push(
          $resultFactory->createExecutable('make:pay-plug:payment')
        );

      }

      // Just let the abstraction layer dispatch a success result.
      return parent::evaluateCompletion($resultFactory, $orderId);
    }


}
