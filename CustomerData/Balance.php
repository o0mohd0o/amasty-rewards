<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\CustomerData;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\DataObject;

class Balance extends DataObject implements SectionSourceInterface
{
    /**
     * @var CustomerSessionFactory
     */
    private $sessionFactory;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    public function __construct(
        CustomerSessionFactory $sessionFactory,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        array $data = []
    ) {
        parent::__construct($data);

        $this->sessionFactory = $sessionFactory;
        $this->customerBalanceRepository = $customerBalanceRepository;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        $result = [
            'balance' => 0
        ];
        $customerSession = $this->sessionFactory->create();
        $customerId = $customerSession->getCustomerId();

        if ($customerId && $customerSession->isLoggedIn()) {
            $result['balance'] = $this->customerBalanceRepository->getBalanceByCustomerId((int)$customerId);
        }

        return $result;
    }
}
