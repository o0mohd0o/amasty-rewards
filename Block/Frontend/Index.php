<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\ConstantRegistryInterface;
use Amasty\Rewards\Model\Date;
use Amasty\Rewards\Model\ResourceModel\Rewards;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Rewards
     */
    private $rewardsResource;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        Rewards $rewardsResource,
        Registry $registry,
        Date $date,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreRegistry = $registry;
        $this->rewardsResource = $rewardsResource;
        $this->date = $date;
        $this->config = $config;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Rewards'));
    }

    /**
     * @return bool|array
     */
    public function getRewardsExpiration()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }

        try {
            $expirationData = $this->rewardsResource->getCustomerExpirationData($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return false;
        }

        return $expirationData;
    }

    /**
     * @return ?int
     */
    public function getCustomerId(): ?int
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return mixed
     */
    public function getStatistic()
    {
        return $this->coreRegistry->registry(ConstantRegistryInterface::CUSTOMER_STATISTICS);
    }

    /**
     * @param array $rewardExpiration
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getDate(array $rewardExpiration)
    {
        if (empty($rewardExpiration[RewardsInterface::EXPIRATION_DATE])) {
            return __('Not Expiring');
        }

        $storeCode = $this->_storeManager->getStore()->getCode();

        return $this->date->convertDate($rewardExpiration[RewardsInterface::EXPIRATION_DATE], $storeCode);
    }

    /**
     * @param array $expirationRow
     *
     * @return \Magento\Framework\Phrase|null
     */
    public function getDeadlineComment($expirationRow)
    {
        if (empty($expirationRow[RewardsInterface::EXPIRATION_DATE])) {
            return null;
        }

        $storeCode = $this->_storeManager->getStore()->getCode();

        return __(
            '<b>%1</b> points will be deducted from your balance on <b>%2</b> because of expiration.',
            $expirationRow[RewardsInterface::AMOUNT],
            $this->date->convertDate(
                $expirationRow[RewardsInterface::EXPIRATION_DATE],
                $storeCode,
                \IntlDateFormatter::FULL
            )
        );
    }

    /**
     * @return string
     */
    public function getDescriptionMessage()
    {
        if (!$this->getData('description_message')) {
            $this->setData(
                'description_message',
                $this->config->getRewardsPointsDescription($this->_storeManager->getStore()->getId())
            );
        }

        return $this->getData('description_message');
    }
}
