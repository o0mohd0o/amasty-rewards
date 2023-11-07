<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Model\ConstantRegistryInterface as Constant;
use Amasty\Rewards\Model\Repository\StatusHistoryRepository;
use Amasty\Rewards\Model\StatusHistoryFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class CustomerSave implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var StatusHistoryFactory
     */
    private $statusHistoryFactory;

    /**
     * @var StatusHistoryRepository
     */
    private $statusHistoryRepository;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        StatusHistoryFactory $statusHistoryFactory,
        StatusHistoryRepository $statusHistoryRepository,
        Session $session
    ) {
        $this->request = $request;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
        $this->statusHistoryFactory = $statusHistoryFactory;
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->session = $session;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getFullActionName() == 'customer_index_save') {
            $data = $this->request->getPost('customer');
            $customer = $this->customerFactory->create();

            if (isset($data['entity_id'])) {
                $this->customerResource->load($customer, (int)$data['entity_id']);
            }

            if (isset($data[Constant::NOTIFICATION_EARNING]) && !$data[Constant::NOTIFICATION_EARNING]) {
                $customer->setAmrewardsEarningNotification((int)$data[Constant::NOTIFICATION_EARNING]);
                $this->customerResource->saveAttribute($customer, Constant::NOTIFICATION_EARNING);
            }

            if (isset($data[Constant::NOTIFICATION_EXPIRE]) && !$data[Constant::NOTIFICATION_EXPIRE]) {
                $customer->setAmrewardsExpireNotification((int)$data[Constant::NOTIFICATION_EXPIRE]);
                $this->customerResource->saveAttribute($customer, Constant::NOTIFICATION_EXPIRE);
            }

            if (isset($data[Constant::FORBID_EARNING])
                && $data[Constant::FORBID_EARNING] != $customer->getAmrewardsForbidEarning()) {
                try {
                    $customer->setAmrewardsForbidEarning((int)$data[Constant::FORBID_EARNING]);
                    $this->customerResource->saveAttribute($customer, Constant::FORBID_EARNING);

                    $statusHistoryEntity = $this->statusHistoryFactory->create();
                    $statusHistoryEntity->setCustomerId($data['entity_id']);
                    $statusHistoryEntity->setAction((int)$data[Constant::FORBID_EARNING]);
                    $statusHistoryEntity->setAdminName($this->session->getUser()->getUserName());

                    $this->statusHistoryRepository->save($statusHistoryEntity);
                } catch (\Exception $e) {
                    throw new CouldNotSaveException(__($e->getMessage()));
                }
            }
        }
    }
}
