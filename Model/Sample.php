<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amasty\Rewards\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use \Magento\Framework\Serialize\SerializerInterface;

/**
 * Importing sample Data
 */
class Sample
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var ResourceModel\Rule\Collection
     */
    protected $ruleCollection;

    /**
     * @var \Magento\CatalogRule\Model\Rule
     */
    protected $catalogRule;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystem;

    public function __construct(
        SampleDataContext $sampleDataContext,
        \Amasty\Rewards\Model\RuleFactory $ruleFactory,
        \Amasty\Rewards\Model\ResourceModel\Rule\Collection $ruleCollection,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        SerializerInterface $serializer,
        \Magento\Framework\Filesystem\Driver\File $filesystem
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->ruleFactory = $ruleFactory;
        $this->ruleCollection = $ruleCollection;
        $this->eavConfig = $eavConfig;
        $this->groupFactory = $groupFactory;
        $this->websiteFactory = $websiteFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);

            if (!$this->filesystem->isExists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                $row['customer_group_ids'] = $this->getGroupIds();
                $row['website_ids'] = $this->getWebsiteIds();

                $row['conditions_serialized'] = $this->convertSerializedData($row['conditions_serialized']);

                $rule = $this->ruleFactory->create();

                $rule->loadPost($row);
                $rule->save();
            }
        }
    }

    /**
     * @return array
     */
    public function getGroupIds()
    {
        $groupsIds = [];
        $collection = $this->groupFactory->create()->getCollection();
        foreach ($collection as $group) {
            $groupsIds[] = $group->getId();
        }

        return $groupsIds;
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        $websiteIds = [];
        $collection = $this->websiteFactory->create()->getCollection();
        foreach ($collection as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function convertSerializedData($data)
    {
        $regexp = '/\%(.*?)\%/';
        preg_match_all($regexp, $data, $matches);
        $replacement = null;
        foreach ($matches[1] as $matchedId => $matchedItem) {
            $extractedData = array_filter(explode(",", $matchedItem));
            foreach ($extractedData as $extractedItem) {
                $separatedData = array_filter(explode('=', $extractedItem));
                if ($separatedData[0] == 'url_key') {
                    if (!$replacement) {
                        $replacement = $this->getCategoryReplacement($separatedData[1]);
                    } else {
                        $replacement .= ',' . $this->getCategoryReplacement($separatedData[1]);
                    }
                }
            }
            if (!empty($replacement)) {
                $data = preg_replace(
                    '/' . $matches[0][$matchedId] . '/',
                    $this->serializer->serialize($replacement),
                    $data
                );
            }
        }

        return $data;
    }

    /**
     * @param string $urlKey
     *
     * @return mixed|null
     */
    protected function getCategoryReplacement($urlKey)
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $category = $categoryCollection->addAttributeToFilter('url_key', $urlKey)->getFirstItem();
        $categoryId = null;
        if (!empty($category)) {
            $categoryId = $category->getId();
        }

        return $categoryId;
    }
}
