<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Customer;

class NewCustomerRegistry
{
    /**
     * @var string
     */
    private $newCustomerEmail = '';

    public function isNewCustomer($email)
    {
        return $this->newCustomerEmail === $email;
    }

    public function setNewCustomerEmail($email)
    {
        $this->newCustomerEmail = $email;
    }
}
