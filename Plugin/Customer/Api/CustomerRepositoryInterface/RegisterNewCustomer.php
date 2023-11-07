<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Customer\Api\CustomerRepositoryInterface;

use Amasty\Rewards\Model\Customer\NewCustomerRegistry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class RegisterNewCustomer
{
    /**
     * @var NewCustomerRegistry
     */
    private $newCustomerFlag;

    public function __construct(
        NewCustomerRegistry $newCustomerFlag
    ) {
        $this->newCustomerFlag = $newCustomerFlag;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @param $passwordHash
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer,
        $passwordHash = null
    ): array {
        if (!$customer->getId()) {
            $this->newCustomerFlag->setNewCustomerEmail($customer->getEmail());
        }

        return [$customer, $passwordHash];
    }
}
