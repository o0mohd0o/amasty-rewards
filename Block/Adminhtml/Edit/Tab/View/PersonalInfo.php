<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Edit\Tab\View;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Model\ConstantRegistryInterface;
use Amasty\Rewards\Model\Date;
use Amasty\Rewards\Model\ResourceModel\Rewards;
use Magento\Backend\Block\Template;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class PersonalInfo extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var Rewards
     */
    private $rewardsResource;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    public function __construct(
        Template\Context $context,
        Rewards $rewardsResource,
        Registry $registry,
        Date $date,
        CustomerRegistry $customerRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->rewardsResource = $rewardsResource;
        $this->registry = $registry;
        $this->date = $date;
        $this->customerRegistry = $customerRegistry;
    }

    public function getStatistic()
    {
        return $this->registry->registry(ConstantRegistryInterface::CUSTOMER_STATISTICS);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEnableEarning(): bool
    {
        $customer = $this->customerRegistry->retrieve(
            $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        return !$customer->getAmrewardsForbidEarning();
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    public function getDeadlineComment()
    {
        $customerId = (int)$this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $expirations = $this->rewardsResource->getCustomerExpirationData($customerId);
        $expirationRow = array_shift($expirations);

        if (empty($expirationRow[RewardsInterface::EXPIRATION_DATE])) {
            return null;
        }

        $storeCode = $this->_storeManager->getStore()->getCode();

        return __(
            '<b>%1</b> points will be deducted from the balance <b>%2</b> because of expiration.',
            $expirationRow[RewardsInterface::AMOUNT],
            $this->date
                ->convertDate($expirationRow[RewardsInterface::EXPIRATION_DATE], $storeCode, \IntlDateFormatter::FULL)
        );
    }
}
