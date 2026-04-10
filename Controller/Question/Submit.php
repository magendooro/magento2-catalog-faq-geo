<?php
/**
 * Magendoo Faq Question Submit Controller
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Controller\Question;

use Magendoo\Faq\Api\Data\QuestionInterface;
use Magendoo\Faq\Api\Data\QuestionInterfaceFactory;
use Magendoo\Faq\Api\QuestionManagementInterface;
use Magendoo\Faq\Helper\Data as FaqHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Submit a new question from frontend
 */
class Submit implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $resultFactory;

    /**
     * @var FormKeyValidator
     */
    protected FormKeyValidator $formKeyValidator;

    /**
     * @var MessageManagerInterface
     */
    protected MessageManagerInterface $messageManager;

    /**
     * @var QuestionInterfaceFactory
     */
    protected QuestionInterfaceFactory $questionFactory;

    /**
     * @var QuestionManagementInterface
     */
    protected QuestionManagementInterface $questionManagement;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var FaqHelper
     */
    protected FaqHelper $faqHelper;

    /**
     * @var FilterManager
     */
    protected FilterManager $filterManager;

    /**
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param FormKeyValidator $formKeyValidator
     * @param MessageManagerInterface $messageManager
     * @param QuestionInterfaceFactory $questionFactory
     * @param QuestionManagementInterface $questionManagement
     * @param CustomerSession $customerSession
     * @param FaqHelper $faqHelper
     * @param FilterManager $filterManager
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        FormKeyValidator $formKeyValidator,
        MessageManagerInterface $messageManager,
        QuestionInterfaceFactory $questionFactory,
        QuestionManagementInterface $questionManagement,
        CustomerSession $customerSession,
        FaqHelper $faqHelper,
        FilterManager $filterManager
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->questionFactory = $questionFactory;
        $this->questionManagement = $questionManagement;
        $this->customerSession = $customerSession;
        $this->faqHelper = $faqHelper;
        $this->filterManager = $filterManager;
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        // Accept POST only
        if (!$this->request->isPost()) {
            return $resultRedirect->setRefererUrl();
        }

        // Validate form key
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $resultRedirect->setRefererUrl();
        }

        // Check if guest questions are allowed
        if (!$this->customerSession->isLoggedIn() && !$this->faqHelper->isGuestQuestionAllowed()) {
            $this->messageManager->addErrorMessage(__('Please log in to submit a question.'));
            return $resultRedirect->setPath('customer/account/login');
        }

        $senderName = trim((string) $this->request->getParam('sender_name'));
        $senderEmail = trim((string) $this->request->getParam('sender_email'));
        $title = trim((string) $this->request->getParam('title'));
        $productIdParam = $this->request->getParam('product_id');
        $productId = $productIdParam !== null && $productIdParam !== '' ? (int) $productIdParam : null;

        // Validate required fields
        if ($senderName === '' || $senderEmail === '' || $title === '') {
            $this->messageManager->addErrorMessage(__('Please fill in all required fields.'));
            return $resultRedirect->setRefererUrl();
        }

        // Validate email
        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $this->messageManager->addErrorMessage(__('Please enter a valid email address.'));
            return $resultRedirect->setRefererUrl();
        }

        // GDPR consent enforcement
        if ($this->faqHelper->isGdprEnabled()) {
            $consent = $this->request->getParam('gdpr_consent');
            if (!$consent) {
                $this->messageManager->addErrorMessage(
                    __('You must agree to the privacy policy before submitting your question.')
                );
                return $resultRedirect->setRefererUrl();
            }
        }

        try {
            /** @var QuestionInterface $question */
            $question = $this->questionFactory->create();
            $question->setTitle($title);
            $question->setUrlKey($this->generateUrlKey($title));
            $question->setStatus(QuestionInterface::STATUS_PENDING);
            $question->setVisibility(QuestionInterface::VISIBILITY_NONE);
            $question->setSenderName($senderName);
            $question->setSenderEmail($senderEmail);

            if ($this->customerSession->isLoggedIn()) {
                $question->setCustomerId((int) $this->customerSession->getCustomerId());
            }

            // Link product if provided — handled by ResourceModel\Question::saveProductRelation on save
            if ($productId !== null && $productId > 0) {
                $question->setData('product_ids', [$productId]);
            }

            $this->questionManagement->submitQuestion($question);

            $this->messageManager->addSuccessMessage(
                __('Your question has been submitted and will be reviewed by our team.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setRefererUrl();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while submitting your question. Please try again.')
            );
            return $resultRedirect->setRefererUrl();
        }

        // Redirect back to the referring page or FAQ index on success
        $referer = (string) $this->request->getServer('HTTP_REFERER');
        if ($referer !== '') {
            return $resultRedirect->setUrl($referer);
        }

        return $resultRedirect->setPath('faq');
    }

    /**
     * Generate a URL key slug from a title.
     *
     * @param string $title
     * @return string
     */
    private function generateUrlKey(string $title): string
    {
        $slug = $this->filterManager->translitUrl($title);
        $slug = strtolower($slug);
        $slug = preg_replace('#[^a-z0-9]+#', '-', $slug);
        $slug = trim((string) $slug, '-');

        if ($slug === '') {
            $slug = 'question';
        }

        // Append uniqueness suffix to avoid collisions on pending/auto-generated keys.
        $slug .= '-' . substr((string) uniqid('', true), -6);

        // Keep the url_key length sane.
        if (strlen($slug) > 128) {
            $slug = substr($slug, 0, 128);
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }
}
