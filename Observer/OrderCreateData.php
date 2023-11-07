<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Api\CheckoutRewardsManagementInterface;
use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\Quote\Validator\CompositeValidator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\Quote;

class OrderCreateData implements ObserverInterface
{
    /**
     * @var CheckoutRewardsManagementInterface
     */
    private $rewardsManagement;

    /**
     * @var RewardsRepositoryInterface
     */
    private $rewardsRepository;

    /**
     * @var \Amasty\Rewards\Model\Quote\Validator\CompositeValidator
     */
    private $validator;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    public function __construct(
        CheckoutRewardsManagementInterface $rewardsManagement,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        CompositeValidator $validator,
        MessageManagerInterface $messageManager
    ) {
        $this->rewardsManagement = $rewardsManagement;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->validator = $validator;
        $this->messageManager = $messageManager;
    }

    /**
     * Apply reward point in admin order create
     * event 'adminhtml_sales_order_create_process_data'
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var $quote Quote */
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();

        if (isset($request['amreward_amount']) && $quote->getCustomerId()) {
            $balance = $this->customerBalanceRepository->getBalanceByCustomerId((int)$quote->getCustomerId());
            $amount = min($request['amreward_amount'], $balance);

            $pointsData = [];
            $this->validator->validate($quote, $amount, $pointsData);
            $amount = abs((float)$pointsData['allowed_points']);

            if (isset($pointsData['notice'])) {
                $this->messageManager->addErrorMessage($pointsData['notice']);
            }

            $this->rewardsManagement->collectCurrentTotals($quote, $amount);
        }
    }
}
