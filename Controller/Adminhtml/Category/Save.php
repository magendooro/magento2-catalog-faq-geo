<?php
/**
 * Magendoo Faq Category Save Controller
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magendoo\Faq\Api\CategoryRepositoryInterface;
use Magendoo\Faq\Api\Data\CategoryInterface;
use Magendoo\Faq\Model\CategoryFactory;

/**
 * Save category controller
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization resource
     */
    public const ADMIN_RESOURCE = 'Magendoo_Faq::category_edit';

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var CategoryFactory
     */
    protected CategoryFactory $categoryFactory;

    /**
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        CategoryFactory $categoryFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : null;

        try {
            if ($categoryId) {
                $category = $this->categoryRepository->getById($categoryId);
            } else {
                $category = $this->categoryFactory->create();
            }

            $category->setName($data['name'] ?? '');
            $category->setPageTitle($data['page_title'] ?? null);
            $category->setUrlKey($data['url_key'] ?? null);
            $category->setDescription($data['description'] ?? null);
            $category->setIcon($data['icon'] ?? null);
            $category->setPosition((int) ($data['position'] ?? 0));
            $category->setStatus((int) ($data['status'] ?? CategoryInterface::STATUS_ENABLED));
            $category->setMetaTitle($data['meta_title'] ?? null);
            $category->setMetaDescription($data['meta_description'] ?? null);
            $category->setNoindex(!empty($data['noindex']));
            $category->setNofollow(!empty($data['nofollow']));
            $category->setCanonicalUrl($data['canonical_url'] ?? null);
            $category->setExcludeSitemap(!empty($data['exclude_sitemap']));

            // Handle store_ids from form data
            if (isset($data['store_ids'])) {
                $category->setData('store_ids', $data['store_ids']);
            }

            $category = $this->categoryRepository->save($category);

            $this->messageManager->addSuccessMessage(__('The category has been saved.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $category->getCategoryId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
        }

        // Redirect back with data
        $redirectParams = ['_current' => true, '_use_forward' => false];
        if ($categoryId) {
            $redirectParams['category_id'] = $categoryId;
        }

        return $resultRedirect->setPath('*/*/edit', $redirectParams);
    }
}
