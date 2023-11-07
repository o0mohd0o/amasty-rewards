<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Model\Config\Source\Actions;

class Transport
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Actions
     */
    private $actions;

    /**
     * @var Date
     */
    private $date;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Amasty\Rewards\Model\Date $date,
        \Amasty\Rewards\Model\Config $config,
        \Psr\Log\LoggerInterface $logger,
        Actions $actions
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
        $this->storeManager = $storeManagerInterface;
        $this->date = $date;
        $this->config = $config;
        $this->logger = $logger;
        $this->actions = $actions;
    }

    /**
     * @param int|float $amount
     * @param int $customerId
     * @param string $action
     * @param int|null $storeId
     *
     * @return $this
     */
    public function sendRewardsEarningNotification($amount, $customerId, $action, ?int $storeId = null)
    {
        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, $customerId);

        $isEnableNotification = $customer->getAmrewardsEarningNotification();

        $store = $this->storeManager->getStore($storeId ?? $customer->getData('store_id'));

        if (!$isEnableNotification || !$this->config->getSendEarnNotification($store)
            || !$this->config->isEnabled(
                $store
            )
        ) {
            return $this;
        }

        $template = $this->config->getEarnTemplate($store);

        $tplVars = [
            'store' => $store,
            'customer' => $customer,
            'earned_reward' => sprintf("%.2f", $amount),
            'action' => $this->getAction($action),
            'rewards_balance_change_date' => $this->date->convertDate('now', $store->getCode())
        ];

        try {
            $this->send($template, $customer, $tplVars, $store);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }

    /**
     * @param array $expirationRows
     * @param int $day
     *
     * @return $this
     */
    public function sendExpireNotification($expirationRows, $day)
    {
        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, current($expirationRows)['customer_id']);

        $store = $this->storeManager->getStore($customer->getStoreId());
        if (!$this->config->isEnabled($store)) {
            return $this;
        }

        $total = 0;
        $deadlines = [];

        foreach ($expirationRows as $expirationRow) {
            if ($expirationRow['days_left'] <= $day) {
                $total += $expirationRow['points'];
                $expirationRow['earn_date'] = $this->date->convertDate($expirationRow['earn_date'], $store->getCode());
                $expirationRow['expiration_date'] =
                    $this->date->convertDate($expirationRow['expiration_date'], $store->getCode());

                $deadlines[] = $expirationRow;
            }
        }

        $template = $this->config->getExpireTemplate($store);

        $tplVars = [
            'store' => $store,
            'customer' => $customer,
            'total_rewards' => $total,
            'deadlines' => $deadlines
        ];

        try {
            $this->send($template, $customer, $tplVars, $store);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }

    /**
     * @param mixed $template
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $vars
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send(
        $template,
        \Magento\Customer\Model\Customer $customer,
        $vars,
        \Magento\Store\Api\Data\StoreInterface $store
    ) {
        $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
        )->setTemplateVars(
            $vars
        )->setFromByScope(
            $this->config->getEmailSender($store),
            $store->getId()
        )->addTo(
            $customer->getEmail()
        );

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * @param string $action
     *
     * @return string
     */
    private function getAction($action)
    {
        $actionsList = $this->actions->toOptionArray();

        return isset($actionsList[$action]) ? $actionsList[$action] : $action;
    }
}
