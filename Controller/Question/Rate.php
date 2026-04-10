<?php
/**
 * Magendoo Faq Question Rate Controller
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Controller\Question;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magendoo\Faq\Api\QuestionManagementInterface;

/**
 * AJAX question rating controller
 */
class Rate implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var QuestionManagementInterface
     */
    protected QuestionManagementInterface $questionManagement;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var RemoteAddress
     */
    protected RemoteAddress $remoteAddress;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param QuestionManagementInterface $questionManagement
     * @param CustomerSession $customerSession
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        QuestionManagementInterface $questionManagement,
        CustomerSession $customerSession,
        RemoteAddress $remoteAddress
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->questionManagement = $questionManagement;
        $this->customerSession = $customerSession;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $result = $this->jsonFactory->create();

        $questionId = (int) $this->request->getParam('question_id');
        $voteType = (string) $this->request->getParam('vote_type');

        if (!$questionId || !$voteType) {
            return $result->setData([
                'success' => false,
                'message' => __('Invalid request parameters.')
            ]);
        }

        $customerId = $this->customerSession->isLoggedIn()
            ? (int) $this->customerSession->getCustomerId()
            : null;
        $ipAddress = (string) $this->remoteAddress->getRemoteAddress();

        try {
            $this->questionManagement->rateQuestion($questionId, $voteType, $customerId, $ipAddress);
            return $result->setData([
                'success' => true,
                'message' => __('Thank you for your feedback!')
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
