<?php
/**
 * Magendoo Faq Home Block
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Block\Faq;

use Magendoo\Faq\Api\Data\CategoryInterface;
use Magendoo\Faq\Helper\Data as FaqHelper;
use Magendoo\Faq\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * FAQ Home Page Block
 */
class Home extends Template
{
    /**
     * @var CategoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * @var FaqHelper
     */
    private FaqHelper $helper;

    /**
     * @param Context $context
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param FaqHelper $helper
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        FaqHelper $helper,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get enabled categories for the current store
     *
     * @return \Magendoo\Faq\Model\ResourceModel\Category\Collection
     */
    public function getCategories(): \Magendoo\Faq\Model\ResourceModel\Category\Collection
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('status', CategoryInterface::STATUS_ENABLED);

        $storeId = (int) $this->_storeManager->getStore()->getId();
        $collection->addStoreFilter($storeId);

        $sortBy = $this->helper->getSortCategoriesBy();
        if ($sortBy === 'name') {
            $collection->setOrder('name', 'ASC');
        } else {
            $collection->setOrder('position', 'ASC');
        }

        return $collection;
    }

    /**
     * Get search URL
     *
     * @return string
     */
    public function getSearchUrl(): string
    {
        return $this->getUrl('faq/question/search');
    }

    /**
     * Get category URL
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getCategoryUrl(CategoryInterface $category): string
    {
        $urlKey = $category->getUrlKey();
        if ($urlKey) {
            return $this->getBaseUrl() . $this->helper->buildUrlPath($urlKey);
        }

        return $this->getUrl('faq/category/view', ['id' => $category->getCategoryId()]);
    }

    /**
     * Check if search box is enabled
     *
     * @return bool
     */
    public function isSearchBoxEnabled(): bool
    {
        return $this->helper->isSearchBoxEnabled();
    }

    /**
     * Get FAQ title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->helper->getTitle();
    }
}
