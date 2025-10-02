<?php

namespace Monext\HyvaPayline\Plugin\PaylineApi;

use Magento\Framework\Module\ModuleListInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Helper\Data as PaylineHelper;
use Payline\PaylineSDK;
use Magento\Framework\App\ProductMetadata;
use Monext\Payline\PaylineApi\Client;

class ClientPlugin
{

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var PaylineHelper
     */
    protected $paylineHelper;

    /**
     * @var ProductMetadata
     */
    protected $productMetadata;

    /**
     * @param ProductMetadata $productMetadata
     * @param PaylineHelper $paylineHelper
     * @param ModuleListInterface $moduleList
     */
    public function __construct(ProductMetadata $productMetadata,
                                PaylineHelper    $paylineHelper,
                                ModuleListInterface $moduleList)
    {
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->paylineHelper = $paylineHelper;
    }

    /**
     * @param Client $subject
     * @param PaylineSDK $paylineSDK
     * @return PaylineSDK
     */
    public function afterPaylineSDK(Client $subject, PaylineSDK $paylineSDK)
    {
        $paylineSDK->usedBy(HelperConstants::PAYLINE_API_USED_BY_PREFIX . '. Hyva ' .
            $this->productMetadata->getVersion() . ' - '
            . ' v' . $this->paylineHelper->getMonextModuleVersion('Monext_HyvaPayline'));

        return $paylineSDK;
    }
}
