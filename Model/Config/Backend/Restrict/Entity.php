<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Config\Backend\Restrict;

use Amasty\Rewards\Model\Config\Utils;
use Amasty\Rewards\Model\ResourceModel\FilterExistingEntityInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Entity extends ConfigValue
{
    /**
     * @return Entity
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        if ($filter = $this->getFilter()) {
            $this->getDataPersistor()->set($this->getPersistentName(), $this->getValue());

            $idsToValidate = $this->getUtils()->convertToArray((string)$this->getValue());
            $availableIds = $filter->execute($idsToValidate);
            $invalidIds = array_diff($idsToValidate, $availableIds);

            if ($invalidIds) {
                throw new LocalizedException(__($this->getErrorMessage(), implode(', ', $invalidIds)));
            }
            $this->getDataPersistor()->clear($this->getPersistentName());
        }

        return parent::beforeSave();
    }

    /**
     * @return Entity
     */
    protected function _afterLoad()
    {
        $configValue = parent::_afterLoad();
        if ($this->getDataPersistor() &&
            $savedValue = $this->getDataPersistor()->get($this->getPersistentName())
        ) {
            $configValue->setValue($savedValue);
            $this->getDataPersistor()->clear($this->getPersistentName());
        }

        return $configValue;
    }

    private function getFilter(): ?FilterExistingEntityInterface
    {
        return $this->getData('filterModel');
    }

    private function getDataPersistor(): ?DataPersistorInterface
    {
        return $this->getData('dataPersistor');
    }

    private function getPersistentName(): ?string
    {
        return $this->getData('persistentName');
    }

    private function getErrorMessage(): ?string
    {
        return $this->getData('errorMessage');
    }

    private function getUtils(): ?Utils
    {
        return $this->getData('utils');
    }
}
