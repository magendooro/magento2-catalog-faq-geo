<?php
/**
 * Magendoo Faq Category Generic Button
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Context;
use Magendoo\Faq\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Generic button base class for category edit form
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->context = $context;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Return category ID
     *
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        $categoryId = $this->context->getRequest()->getParam('category_id');

        // Return null for new entity creation (no ID in request)
        if (!$categoryId) {
            return null;
        }

        try {
            $id = $this->categoryRepository->getById((int) $categoryId)->getCategoryId();
            return $id ? (int) $id : null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
