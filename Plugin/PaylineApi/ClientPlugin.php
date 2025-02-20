<?php

namespace Monext\HyvaPayline\Plugin\PaylineApi;

use Magento\Framework\Module\ModuleListInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Payline\PaylineSDK;
use Magento\Framework\App\ProductMetadata;
use Monext\Payline\PaylineApi\Client;

class ClientPlugin
{

    /**
     * @var ProductMetadata
     */
    protected $productMetadata;
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    public function __construct(ProductMetadata $productMetadata,
                                ModuleListInterface $moduleList)
    {
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
    }

    public function afterPaylineSDK(Client $subject, PaylineSDK $paylineSDK)
    {
        $currentModule = $this->moduleList->getOne(HelperConstants::MODULE_NAME);
        $paylineSDK->usedBy(HelperConstants::PAYLINE_API_USED_BY_PREFIX . '. Hyva ' .
            $this->productMetadata->getVersion() . ' - '
            . ' v' . $currentModule['setup_version']);

        return $paylineSDK;
    }
}
