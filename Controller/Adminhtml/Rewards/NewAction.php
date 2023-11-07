<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Rewards;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\Date;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutFactory;

class NewAction extends \Amasty\Rewards\Controller\Adminhtml\Rewards
{
    public const IS_EXPIRE = 'is_expire';
    public const DAYS = 'days';
    public const ADMIN_ACTION = 'admin';

    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var RewardsRepositoryInterface
     */
    private $rewardsRepository;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Context $context,
        Date $date,
        RewardsProviderInterface $rewardsProvider,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        RewardsRepositoryInterface $rewardsRepository,
        Session $session
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->rewardsProvider = $rewardsProvider;
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->rewardsRepository = $rewardsRepository;
        $this->session = $session;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        /** @var RewardsInterface $modelRewards */
        $modelRewards = $this->rewardsRepository->getEmptyModel();
        $hasError = false;
        $amount = (float)$this->getRequest()->getParam('amount');
        $modelRewards->setAmount($amount);
        $modelRewards->setCustomerId((int)$this->getRequest()->getParam('customer_id'));
        $modelRewards->setComment($this->getRequest()->getParam('comment'));
        $modelRewards->setAction($this->getRequest()->getParam('action'));
        $visibleForCustomer = filter_var(
            $this->getRequest()->getParam(RewardsInterface::VISIBLE_FOR_CUSTOMER),
            FILTER_VALIDATE_BOOLEAN
        );

        $modelRewards->setVisibleForCustomer($visibleForCustomer);

        $adminName = $this->session->getUser()->getUserName();
        $modelRewards->setAdminName($adminName);

        try {
            switch ($modelRewards->getAction()) {
                case 'add':
                    $expiration = $this->getRequest()->getParam('expiration');
                    if (!empty($expiration[self::IS_EXPIRE]) && isset($expiration[self::DAYS])) {
                        $expirationDate = $this->date->getDateWithOffsetByDays($expiration[self::DAYS]);
                        $modelRewards->setExpirationDate($expirationDate);
                        $modelRewards->setExpiringAmount($amount);
                    }
                    $modelRewards->setAction(self::ADMIN_ACTION);
                    $this->rewardsProvider->addRewardPoints($modelRewards);
                    break;
                case 'deduct':
                    $modelRewards->setAction(self::ADMIN_ACTION);
                    $this->rewardsProvider->deductRewardPoints($modelRewards);
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $hasError = true;
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong. Please review the error log.')
            );

            $hasError = true;
        }

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));
            $body = [
                'messages' => $block->getGroupedHtml(),
                'error' => $hasError
            ];

            return $this->resultJsonFactory->create()->setData($body);
        }
    }
}
