<?php


namespace Monext\HyvaPayline\Model\Magewire\Payment;

use Exception;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class PlaceOrderServiceProvider extends AbstractPlaceOrderService
{
    protected OrderRepositoryInterface $orderRepository;
    protected ResultFactory $resultFactory;

    /**
     * @var null
     */
    protected $orderId = null;

    /**
     * @var null
     */
    protected $order = null;

    protected $sessionCheckout;

    /**
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CartManagementInterface  $cartManagement,
        OrderRepositoryInterface $orderRepository,
        SessionCheckout $sessionCheckout,
    ) {
        parent::__construct($cartManagement);
        $this->orderRepository = $orderRepository;
        $this->sessionCheckout = $sessionCheckout;
    }

    /**
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        if($order = $this->getOrder($orderId)) {
            $result = $order->getPayment()->getAdditionalInformation('do_web_payment_response_data');
        }

        if (!empty($result['redirect_url'])) {
            return $result['redirect_url'];
        }

        return parent::REDIRECT_PATH;
    }

    /**
     * @return bool
     */
    public function canRedirect(): bool
    {
        if($this->orderId && $order = $this->getOrder($this->orderId))  {
            $result = $order->getPayment()->getAdditionalInformation('do_web_payment_response_data');
        }

        return !empty($result['redirect_url']);
    }

    /**
     * Redirection
     *
     * @param Quote $quote
     * @return int
     * @throws CouldNotSaveException
     */
    public function placeOrder(Quote $quote): int
    {
        return parent::placeOrder($quote);
    }

    /**
     * Redirection
     *
     * @return bool
     */
    public function canPlaceOrder(): bool
    {
        if (!$this->sessionCheckout->getQuoteId()) {
            throw new Exception('Cannot find quote_id to place Payline order');
        }
        /** @var   \Magento\Quote\Api\Data\PaymentInterface $payment */
        $payment = $this->sessionCheckout->getQuote()->getPayment();
        if(strpos($payment->getMethod(), 'payline_web_payment_') === false ||
            ! $payment->getAdditionalInformation('contract_number')
        ) {
            throw new Exception('Payment method or contract_number is are not valid to place Payline order');
        }

        return parent::canPlaceOrder();
    }

    /**
     * Redirection
     *
     * @param EvaluationResultFactory $resultFactory
     * @param int|null $orderId
     * @return EvaluationResultInterface
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory, ?int $orderId = null): EvaluationResultInterface
    {
        $this->orderId = $orderId;

        // Just let the abstraction layer dispatch a success result.
        return parent::evaluateCompletion($resultFactory, $orderId);
    }


    /**
     * @param int|null $orderId
     * @return OrderInterface|null
     */
    protected function getOrder(?int $orderId = null): ?OrderInterface
    {
        if(is_null($this->order) && $orderId) {
            $this->orderId = $orderId;
            $this->order = $this->orderRepository->get($orderId);
        }

        return $this->order;
    }
}
