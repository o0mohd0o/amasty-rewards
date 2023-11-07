<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Ui\Component\Listing\Column\Order;

use Magento\Ui\Component\Listing\Columns\Column;

class Points extends Column
{
    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $name = $this->getName();

            foreach ($dataSource['data']['items'] as &$item) {
                if ($item[$name]) {
                    $item[$name] = round((float)$item[$name], 2);
                }
            }
        }

        return $dataSource;
    }
}
