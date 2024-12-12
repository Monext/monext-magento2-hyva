<?php

namespace Monext\HyvaPayline\Setup\Patch\Data;

use Magento\Framework\Module\Manager;
use Magento\Framework\Module\Status;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DisableIfCheckoutModuleIsNotInstalled implements DataPatchInterface
{
    private Manager $moduleManager;
    private Status $moduleStatus;

    public function __construct(
        Manager $moduleManager,
        Status $moduleStatus
    ) {
        $this->moduleStatus = $moduleStatus;
        $this->moduleManager = $moduleManager;
    }

    public function apply()
    {
        // To prevent dependency error, disable this module if Hyva_Checkout is not enabled
        if ($this->moduleManager->isEnabled('Hyva_Checkout')) {
            return;
        }

        $this->moduleStatus->setIsEnabled(false, ['Monext_HyvaPayline']);
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
