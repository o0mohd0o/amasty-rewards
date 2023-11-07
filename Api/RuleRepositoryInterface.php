<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
namespace Amasty\Rewards\Api;

/**
 * Interface RuleRepositoryInterface
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * @param \Amasty\Rewards\Api\Data\RuleInterface $rule
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Rewards\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($ruleId);

    /**
     * @param \Amasty\Rewards\Api\Data\RuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Rewards\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);

    /**
     * @param string $action
     * @param int $websiteId
     * @param int $customerGroupId
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface[]
     */
    public function getRulesByAction($action, $websiteId, $customerGroupId);
}
