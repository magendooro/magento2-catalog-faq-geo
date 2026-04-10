<?php
/**
 * Magendoo Faq Question Save Controller
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Controller\Adminhtml\Question;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magendoo\Faq\Api\Data\QuestionInterface;
use Magendoo\Faq\Api\QuestionManagementInterface;
use Magendoo\Faq\Api\QuestionRepositoryInterface;
use Magendoo\Faq\Model\QuestionFactory;

/**
 * Save question controller
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization resource
     */
    public const ADMIN_RESOURCE = 'Magendoo_Faq::question_edit';

    /**
     * @var QuestionRepositoryInterface
     */
    protected QuestionRepositoryInterface $questionRepository;

    /**
     * @var QuestionManagementInterface
     */
    protected QuestionManagementInterface $questionManagement;

    /**
     * @var QuestionFactory
     */
    protected QuestionFactory $questionFactory;

    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * @param Context $context
     * @param QuestionRepositoryInterface $questionRepository
     * @param QuestionManagementInterface $questionManagement
     * @param QuestionFactory $questionFactory
     * @param FilterManager $filterManager
     */
    public function __construct(
        Context $context,
        QuestionRepositoryInterface $questionRepository,
        QuestionManagementInterface $questionManagement,
        QuestionFactory $questionFactory,
        FilterManager $filterManager
    ) {
        $this->questionRepository = $questionRepository;
        $this->questionManagement = $questionManagement;
        $this->questionFactory = $questionFactory;
        $this->filterManager = $filterManager;
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

        $questionId = isset($data['question_id']) ? (int) $data['question_id'] : null;

        try {
            if ($questionId) {
                $question = $this->questionRepository->getById($questionId);
            } else {
                $question = $this->questionFactory->create();
            }

            // Populate question data
            $question->setTitle($data['title'] ?? '');
            $question->setShortAnswer($data['short_answer'] ?? null);
            $question->setFullAnswer($data['full_answer'] ?? null);
            $question->setStatus($data['status'] ?? QuestionInterface::STATUS_PENDING);
            $question->setVisibility($data['visibility'] ?? QuestionInterface::VISIBILITY_NONE);
            $question->setPosition((int) ($data['position'] ?? 0));
            $question->setIsShowFullAnswer(!empty($data['is_show_full_answer']));
            $question->setSenderName($data['sender_name'] ?? null);
            $question->setSenderEmail($data['sender_email'] ?? null);
            $question->setMetaTitle($data['meta_title'] ?? null);
            $question->setMetaDescription($data['meta_description'] ?? null);
            $question->setNoindex(!empty($data['noindex']));
            $question->setNofollow(!empty($data['nofollow']));
            $question->setCanonicalUrl($data['canonical_url'] ?? null);
            $question->setExcludeSitemap(!empty($data['exclude_sitemap']));
            $question->setHideDirectUrl(!empty($data['hide_direct_url']));

            // Auto-generate url_key from title if empty
            $urlKey = $data['url_key'] ?? '';
            if (empty($urlKey)) {
                $urlKey = $this->filterManager->translitUrl($data['title'] ?? '');
            }
            $question->setUrlKey($urlKey);

            // Handle store_ids from form data
            if (isset($data['store_ids'])) {
                $question->setData('store_ids', $data['store_ids']);
            }

            // Handle category_ids from form data (multiselect => array of strings).
            if (isset($data['category_ids'])) {
                $categoryIds = is_array($data['category_ids'])
                    ? $data['category_ids']
                    : array_filter(array_map('trim', explode(',', (string) $data['category_ids'])));
                $question->setData('category_ids', array_map('intval', $categoryIds));
            }

            // Handle product_ids from form data. The admin form uses a comma-
            // separated text input ("1,24,57"); the REST API may pass an array.
            if (isset($data['product_ids'])) {
                $productIds = is_array($data['product_ids'])
                    ? $data['product_ids']
                    : array_filter(array_map('trim', explode(',', (string) $data['product_ids'])));
                $question->setData('product_ids', array_map('intval', $productIds));
            }

            // Handle tags
            if (isset($data['tags'])) {
                $question->setData('tags', $data['tags']);
            }

            if (isset($data['customer_id'])) {
                $question->setCustomerId($data['customer_id'] ? (int) $data['customer_id'] : null);
            }

            $question = $this->questionRepository->save($question);

            // Send answer notification email if requested
            if (!empty($data['send_email'])) {
                try {
                    $this->questionManagement->sendAnswerNotification($question->getQuestionId());
                    $this->messageManager->addSuccessMessage(__('Answer notification email has been sent.'));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('The question was saved but the notification email failed: %1', $e->getMessage())
                    );
                }
            }

            $this->messageManager->addSuccessMessage(__('The question has been saved.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['question_id' => $question->getQuestionId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the question.'));
        }

        // Redirect back with data
        $redirectParams = ['_current' => true, '_use_forward' => false];
        if ($questionId) {
            $redirectParams['question_id'] = $questionId;
        }

        return $resultRedirect->setPath('*/*/edit', $redirectParams);
    }
}
