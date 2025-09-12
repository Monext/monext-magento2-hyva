<?php

namespace Monext\HyvaPayline\Magewire;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Monext\HyvaPayline\Helper\Hyva;
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
    private ManagerInterface $messageManager;

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
        ManagerInterface $messageManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->sessionCheckout = $sessionCheckout;
        $this->dataHelper = $dataHelper;
        $this->paymentManagement = $paymentManagement;
        $this->hyvaHelper = $hyvaHelper;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
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

        $canGetToken = $this->canGetToken();
        if(!$canGetToken['success']) {
            $errorMessageEvent = $resultFactory->createErrorMessageEvent();
            $errorMessageEvent->withCustomEvent('shipping:method:error');

            $this->dispatchWarningMessage(implode(", ", $canGetToken['errors']));
//            return $resultFactory->createErrorMessageEvent()
//                ->withCustomEvent('payment:monext:error')
//                ->withMessage(implode("\n", $canGetToken['errors']));
        } else {
            $payment->setAdditionalInformation('payment_mode', $this->method);
            $this->quoteRepository->save($quote);
            $this->clearFlashMessages();
            $this->messageManager->getMessages(true);
        }


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

                $canGetToken = $this->canGetToken();
                if(!$canGetToken['success']) {
                    throw new \Exception(implode(", ", $canGetToken['errors']));
                }

                $result = $this->paymentManagement->saveCheckoutPaymentInformationFacade($quote->getId(), $quote->getPayment());
                $this->token = $result['token'];
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->token = null;
        }
    }

    protected function canGetToken()
    {
        $errorMessage = [];
        if (empty($this->method)) {
            $errorMessage[] = 'The payment method is missing from Payline. Select the payment method and try again.';
        }

        $quote = $this->sessionCheckout->getQuote();
        //Basic test for one page
        if (!$quote->getShippingAddress()) {
            $errorMessage[] = 'Shipping address is not defined.';
        } elseif (!$quote->getShippingAddress()->getShippingMethod()) {
            $errorMessage[] = 'Shipping method is not set.';
        } else {
            foreach (Hyva::ADDRESS_MADATORY_FIELDS as $field) {
                if (!$quote->getShippingAddress()->getData($field)) {
                    $errorMessage[] = __('Shipping address field %1s is missing ',  $field);
                }
            }


        }

        return empty($errorMessage) ? ['success' => true] : ['success' => false, 'errors' => $errorMessage];
    }
}
