<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
namespace Amasty\Rewards\Test\Integration\Plugin\Sales\Model\ResourceModel;

use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amasty\Rewards\Model\Repository\StatusHistoryRepository;
use Amasty\Rewards\Model\ResourceModel\StatusHistory;
use Amasty\Rewards\Model\StatusHistoryFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @var string[]
     */
    private $addressData = [
        'region' => 'CA',
        'region_id' => '12',
        'postcode' => '11111',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'street' => 'street',
        'city' => 'Los Angeles',
        'email' => 'customer@rewardsamasty.com',
        'telephone' => '11111111',
        'country_id' => 'US'
    ];

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ResourceCustomer
     */
    private $customerResource;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var RewardsRepository
     */
    private $rewardsRepository;

    /**
     * @var StatusHistoryFactory
     */
    private $statusHistoryFactory;

    /**
     * @var StatusHistoryRepository
     */
    private $statusHistoryRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->product = $productRepository->get('simple');

        $this->customerResource = $objectManager->create(ResourceCustomer::class);
        $this->customer = $objectManager->create(CustomerFactory::class)->create();
        $this->customerResource->load($this->customer, 777);
        $this->rewardsRepository = $objectManager->create(RewardsRepository::class);

        $this->statusHistoryFactory = $objectManager->create(StatusHistoryFactory::class);
        $this->statusHistoryRepository = $objectManager->create(StatusHistoryRepository::class);

        $this->orderRepository = $objectManager->create(OrderRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Amasty_Rewards::Test/Integration/_files/customer.php
     * @magentoDataFixture Amasty_Rewards::Test/Integration/_files/rule_ordercompleted.php
     * @magentoDataFixture Amasty_Rewards::Test/Integration/_files/product.php
     */
    public function testAfterSave()
    {
        $order1 = $this->createOrder(1111111);
        $this->changeCustomerStatus(StatusHistory::EXCLUDE_ACTION);

        //set flag for automatic change order state
        //@see \Magento\Sales\Model\ResourceModel\Order\Handler\State::check()
        $order1->setActionFlag(Order::ACTION_FLAG_INVOICE, false);
        $this->orderRepository->save($order1);

        $this->assertEquals(
            '10.00',
            $this->rewardsRepository->getCustomerRewardBalance($this->customer->getId())
        );

        $order2 = $this->createOrder(222222);
        $this->changeCustomerStatus(StatusHistory::RESTORE_ACTION);

        $order2->setActionFlag(Order::ACTION_FLAG_INVOICE, false);

        $this->assertEquals(
            '10.00',
            $this->rewardsRepository->getCustomerRewardBalance($this->customer->getId())
        );
    }

    /**
     * @param int $id
     * @return Order
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createOrder(int $id)
    {
        $objectManager = Bootstrap::getObjectManager();

        $billingAddress = $objectManager->create(OrderAddress::class, ['data' => $this->addressData]);
        $billingAddress->setAddressType('billing');
        $payment = $objectManager->create(Payment::class);
        $payment->setMethod('checkmo')
            ->setAdditionalInformation('last_trans_id', '11122')
            ->setAdditionalInformation('metadata', ['type' => 'free', 'fraudulent' => false,]);

        $orderItem = $objectManager->create(OrderItem::class);
        $orderItem->setProductId($this->product->getId())
            ->setQtyOrdered(2)
            ->setBasePrice($this->product->getPrice())
            ->setPrice($this->product->getPrice())
            ->setBaseRowTotal($this->product->getPrice())
            ->setRowTotal($this->product->getPrice())
            ->setName($this->product->getName())
            ->setSku($this->product->getSku());

        $order = $objectManager->create(Order::class);
        $order->setIncrementId($id)
            ->setState(Order::STATE_PROCESSING)
            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
            ->setSubtotal(100)
            ->setGrandTotal(100)
            ->setBaseSubtotal(100)
            ->setBaseGrandTotal(100)
            ->setIsVirtual(true)
            ->setBillingAddress($billingAddress)
            ->setCustomerEmail('customer@rewardsamasty.com')
            ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
            ->addItem($orderItem)
            ->setPayment($payment)
            ->setEntityId((int)$id)
            ->isObjectNew(true);
        $order->setCustomerId($this->customer->getId())
            ->setCustomerGroupId(1)
            ->setOrderCurrencyCode('USD')
            ->setBaseCurrencyCode('USD')
            ->setShippingMethod('flatrate_flatrate');

        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * @param int $status
     * @throws CouldNotSaveException
     */
    private function changeCustomerStatus(int $status): void
    {
        $this->customer->setAmrewardsForbidEarning($status);
        $this->customerResource->saveAttribute($this->customer, 'amrewards_forbid_earning');
        $statusHistoryEntity = $this->statusHistoryFactory->create();
        $statusHistoryEntity->setCustomerId((int)$this->customer->getId());
        $statusHistoryEntity->setAction((int)$status);
        $statusHistoryEntity->setAdminName('admin');

        $this->statusHistoryRepository->save($statusHistoryEntity);
    }
}
