<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Ui\Component\Listing\Column;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class PointsColumn extends \Magento\Customer\Ui\Component\Listing\Columns
{
    public const FIELD_NAME = 'amount';

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        AttributeRepository $attributeRepository,
        InlineEditUpdater $inlineEditor,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $columnFactory, $attributeRepository, $inlineEditor, $components, $data);
        $this->customerBalanceRepository = $customerBalanceRepository;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $rewardBalance = $this->customerBalanceRepository->getBalanceByCustomerId((int)$item["entity_id"]);
                $item[self::FIELD_NAME] = $rewardBalance;
            }
        }

        return $dataSource;
    }
}
