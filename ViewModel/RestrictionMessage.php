<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\ViewModel;

use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Quote\EarningChecker;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RestrictionMessage implements ArgumentInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var EarningChecker
     */
    private $earningChecker;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Session $customerSession,
        EarningChecker $earningChecker,
        Config $configProvider
    ) {
        $this->customerSession = $customerSession;
        $this->earningChecker = $earningChecker;
        $this->configProvider = $configProvider;
    }

    public function canShowMessage(): bool
    {
        $customerId = (int) $this->customerSession->getCustomerId();

        return $this->earningChecker->isForbiddenEarningByCustomerStatus($customerId)
            && $this->configProvider->isRestrictionMessageEnabled()
            && !empty($this->getMessageText());
    }

    public function getMessageText(): ?string
    {
        return $this->configProvider->getRestrictionMessageText();
    }
}
