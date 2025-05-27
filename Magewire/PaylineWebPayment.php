<?php

namespace Monext\HyvaPayline\Magewire;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magewirephp\Magewire\Component;
use Monext\HyvaPayline\Helper\Hyva as HyvaHelper;
use Monext\Payline\Helper\Data as DataHelper;
use Monext\Payline\Model\PaymentManagement;


class PaylineWebPayment extends Component implements EvaluationInterface
{
    public string $method = '';
    public array $methods = [];
    public ?string $token = null;

    private CartRepositoryInterface $quoteRepository;
    private SessionCheckout $sessionCheckout;
    private DataHelper $dataHelper;
    private PaymentManagement $paymentManagement;
    private HyvaHelper $hyvaHelper;
    private LoggerInterface $logger;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param SessionCheckout $sessionCheckout
     * @param LoggerInterface $logger
     * @param DataHelper $dataHelper
     * @param PaymentManagement $paymentManagement
     * @param HyvaHelper $hyvaHelper
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        SessionCheckout $sessionCheckout,
        LoggerInterface $logger,
        DataHelper $dataHelper,
        PaymentManagement $paymentManagement,
        HyvaHelper $hyvaHelper,
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->sessionCheckout = $sessionCheckout;
        $this->dataHelper = $dataHelper;
        $this->paymentManagement = $paymentManagement;
        $this->hyvaHelper = $hyvaHelper;
        $this->logger = $logger;
    }

    /**
     * Magewire call beforeHydate
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount()
    {
        $payment = $this->sessionCheckout->getQuote()->getPayment();
        $this->method = $this->dataHelper->getPaymentMode($payment->getMethod());
    }

    /**
     *
     * @param string $contractNumber
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setAdditionalData(string $contractNumber): void
    {
        $quote = $this->sessionCheckout->getQuote();
        $quote->getPayment()->setAdditionalInformation('contract_number', $contractNumber);
        $this->quoteRepository->save($quote);
    }

    /**
     * Magewire dehydate (evaluateComponent)
     *
     * @param EvaluationResultFactory $resultFactory
     * @return EvaluationResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        $quote = $this->sessionCheckout->getQuote();
        $payment = $quote->getPayment();

        $paymentMethod = $payment->getMethod();
        if (!in_array($paymentMethod, $this->hyvaHelper->getHandledPaymentMethods())) {
            return $resultFactory->createSuccess();
        }

        if (empty($this->method)) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage('The payment method is missing from Payline. Select the payment method and try again.');
        }

        //Basic test for one page
        if (!$quote->getShippingAddress() || !$quote->getShippingAddress()->getEmail()) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage('Invalid user data');
        }

        $payment->setAdditionalInformation('payment_mode', $this->method);

        $this->quoteRepository->save($quote);

        return $resultFactory->createSuccess();
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function generateToken(): void
    {
        $this->mount();
        $this->getToken();
    }

    /**
     * @return void
     */
    protected function getToken(): void
    {
        try {
            $quote = $this->sessionCheckout->getQuote();
            if($this->method && $quote->getPayment()->getAdditionalInformation('payment_mode') == $this->method) {
                $result = $this->paymentManagement->saveCheckoutPaymentInformationFacade($quote->getId(), $quote->getPayment());
                $this->token = $result['token'];
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->token = null;
        }
    }
}
