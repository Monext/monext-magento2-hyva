<?php

namespace Monext\HyvaPayline\Magewire\Checkout\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as SessionCheckout;
use Monext\Payline\Helper\Data as DataHelper;

class PaylineWebPaymentCpt extends Component implements EvaluationInterface
{
    public string $method = '';

    public bool $acceptTos = true;

    public array $methods = [];

    public function __construct(
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly SessionCheckout $sessionCheckout,
        private readonly DataHelper $dataHelper,
    ) {
    }

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
    public function setAditionalData($contractNumber)
    {
        $quote = $this->sessionCheckout->getQuote();
        $quote->getPayment()->setAdditionalInformation('contract_number', $contractNumber);
        $this->quoteRepository->save($quote);
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        if ($this->sessionCheckout->getQuote()->getPayment()->getMethod() != 'payline_web_payment_cpt') {
            return $resultFactory->createSuccess();
        }

        if (empty($this->method)) {
            return $resultFactory->createBlocking(__('Payment method not selected'));
        }

        if (!$this->acceptTos) {
            return $resultFactory->createBlocking(__('TOS not accepted'));
        }

        $quote = $this->sessionCheckout->getQuote();
        $quote->getPayment()->setAdditionalInformation('payment_mode', $this->method);
        $this->quoteRepository->save($quote);

        return $resultFactory->createSuccess();
    }
}
