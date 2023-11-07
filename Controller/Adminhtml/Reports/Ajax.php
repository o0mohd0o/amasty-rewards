<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Reports;

use Amasty\Rewards\Block\Adminhtml\Reports\Configurator;
use Amasty\Rewards\Model\Date;
use Amasty\Rewards\Model\ResourceModel\Rewards;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Ajax extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Rewards
     */
    private $rewardsResource;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        Rewards $rewardsResource,
        Date $date,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->rewardsResource = $rewardsResource;
        $this->date = $date;
        $this->logger = $logger;
    }

    public function execute()
    {
        $response['type'] = 'success';

        try {
            $response['data'] = $this->loadDataByParams();
        } catch (LocalizedException $exception) {
            $response['type'] = 'warning';
            $response['message'] = $exception->getMessage();
        } catch (\Exception $exception) {
            $response['type'] = 'error';
            $response['message'] = __('Something went wrong. Please try again or check your Magento log file.');

            $this->logger->error($exception->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * @return array
     *
     * @throws LocalizedException
     */
    private function loadDataByParams()
    {
        $data = [];
        $website = $this->getRequest()->getParam('website');
        $customerGroup = $this->getRequest()->getParam('customer_group');
        $dateRange = $this->getRequest()->getParam('date_range');
        $dateFrom = $this->getRequest()->getParam('date_from');
        $dateTo = $this->getRequest()->getParam('date_to');

        if ($website == Configurator::ALL) {
            $website = null;
        }

        if ($customerGroup == Configurator::ALL) {
            $customerGroup = null;
        }

        if ($dateRange == Configurator::OVERALL) {
            $dateTo = null;
            $dateFrom = null;
        } elseif (!$dateRange == Configurator::CUSTOM) {
            $dateTo = $this->date->getDateWithOffsetByDays(1);
            $dateFrom = $this->date->getDateWithOffsetByDays((-1) * ($dateRange - 1));
        } else {
            $dateFrom = $this->date->date(null, $dateFrom);
            $dateTo = $this->date->date(null, $dateTo . '+1 day');
        }

        /** @var \Magento\Framework\DB\Select $conditions */
        $conditions = $this->rewardsResource->addParamsFilter($website, $customerGroup, $dateFrom, $dateTo);

        $data['total'] = $this->rewardsResource->getTotalDataByParams(clone $conditions);

        if (!isset($data['total'][Rewards::REWARDED])) {
            throw new LocalizedException(__('No data to display.'));
        }

        $customersCount = $this->rewardsResource->getCustomersCount(clone $conditions);
        $data['average'][Rewards::REWARDED] = $customersCount != 0 ? round($data['total'][Rewards::REWARDED] / $customersCount, 2) : 0;
        $data['average'][Rewards::REDEEMED] = $this->rewardsResource->getAverageOrderAmount(clone $conditions);
        $data['graph'] = $this->rewardsResource->getGraphData(
            clone $conditions,
            $dateRange == Configurator::LAST_DAY ? Rewards::HOUR : Rewards::DAY
        );

        foreach ($data['graph'] as &$graph) {
            $graph[Rewards::PERIOD] = $this->date->convertDate(
                $graph[Rewards::PERIOD],
                'default',
                \IntlDateFormatter::SHORT,
                $dateRange == Configurator::LAST_DAY ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE
            );
        }

        return $data;
    }

    /**
     * Determine if authorized to perform group action.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Rewards::reports');
    }
}
