<?php


namespace Monext\HyvaPayline\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
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

    /**
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CartManagementInterface  $cartManagement,
        OrderRepositoryInterface $orderRepository,
    ) {
        parent::__construct($cartManagement);
        $this->orderRepository = $orderRepository;
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
     * @param Quote $quote
     * @return int
     * @throws CouldNotSaveException
     */
    public function placeOrder(Quote $quote): int
    {
        return parent::placeOrder($quote);
    }

    /**
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