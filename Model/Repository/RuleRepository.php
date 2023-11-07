<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
namespace Amasty\Rewards\Model\Repository;

use Amasty\Rewards\Api\Data;
use Amasty\Rewards\Model;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class RuleRepository implements \Amasty\Rewards\Api\RuleRepositoryInterface
{
    /**
     * @var Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var Model\ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @var Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var array
     */
    protected $rules = [];

    public function __construct(
        Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        Model\ResourceModel\Rule $ruleResource,
        Model\RuleFactory $ruleFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\RuleInterface $rule)
    {
        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getRuleId()) {
                throw new CouldNotSaveException(
                    __('Unable to save rule with ID %1. Error: %2', [$rule->getRuleId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rule. Error: %1', $e->getMessage()));
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            /** @var \Amasty\Rewards\Model\Rule $rule */
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $ruleId);
            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            $this->rules[$ruleId] = $rule;
        }
        return $this->rules[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getRuleId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove rule with ID %1. Error: %2', [$rule->getRuleId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rule rule. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $model = $this->get($ruleId);
        $this->delete($model);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getRulesByAction($action, $websiteId, $customerGroupId)
    {
        if ($websiteId === null && $customerGroupId === null) {
            return $this->getActiveByAction($action);
        }

        /** @var $ruleCollection \Amasty\Rewards\Model\ResourceModel\Rule\Collection */
        $ruleCollection = $this->ruleCollectionFactory->create();

        $ruleCollection->addWebsiteGroupActionFilter(
            $websiteId,
            $customerGroupId,
            $action
        );

        return $ruleCollection->getItems();
    }

    /**
     * @param string $action
     *
     * @return null|\Amasty\Rewards\Api\Data\RuleInterface[]
     */
    private function getActiveByAction($action)
    {
        if (empty($this->rules['by_action'][$action])) {
            /** @var $ruleCollection \Amasty\Rewards\Model\ResourceModel\Rule\Collection */
            $ruleCollection = $this->ruleCollectionFactory->create();

            $ruleCollection->addIsActiveFilter()->loadByAction($action);

            $this->rules['by_action'][$action] = $ruleCollection->getItems();
        }

        return $this->rules['by_action'][$action];
    }

    /**
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function getEmptyModel()
    {
        return $this->ruleFactory->create();
    }
}
