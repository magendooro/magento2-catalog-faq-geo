<?php
/**
 * Magendoo Faq Email Sender
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Model\Email;

use Magendoo\Faq\Api\Data\QuestionInterface;
use Magendoo\Faq\Helper\Data as FaqHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for dispatching FAQ-related email notifications.
 */
class Sender
{
    private const XML_PATH_USER_ENABLED = 'magendoo_faq/user_notifications/enabled';
    private const XML_PATH_USER_SENDER = 'magendoo_faq/user_notifications/email_sender';
    private const XML_PATH_USER_TEMPLATE = 'magendoo_faq/user_notifications/email_template';

    private const XML_PATH_ADMIN_ENABLED = 'magendoo_faq/admin_notifications/enabled';
    private const XML_PATH_ADMIN_SEND_TO = 'magendoo_faq/admin_notifications/send_to';
    private const XML_PATH_ADMIN_TEMPLATE = 'magendoo_faq/admin_notifications/email_template';

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param FaqHelper $faqHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        private TransportBuilder $transportBuilder,
        private StateInterface $inlineTranslation,
        private ScopeConfigInterface $scopeConfig,
        private StoreManagerInterface $storeManager,
        private FaqHelper $faqHelper,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Send admin notification when a new question is submitted.
     *
     * @param QuestionInterface $question
     * @return bool
     */
    public function sendAdminNotification(QuestionInterface $question): bool
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ADMIN_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        $sendTo = (string) $this->scopeConfig->getValue(self::XML_PATH_ADMIN_SEND_TO, ScopeInterface::SCOPE_STORE);
        $template = (string) $this->scopeConfig->getValue(self::XML_PATH_ADMIN_TEMPLATE, ScopeInterface::SCOPE_STORE);

        if ($sendTo === '' || $template === '') {
            return false;
        }

        return $this->dispatch($template, 'general', $sendTo, $question);
    }

    /**
     * Send customer notification when the admin answers a question.
     *
     * @param QuestionInterface $question
     * @return bool
     */
    public function sendAnswerNotification(QuestionInterface $question): bool
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_USER_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        if (!$question->getSenderEmail()) {
            return false;
        }

        $sender = (string) $this->scopeConfig->getValue(self::XML_PATH_USER_SENDER, ScopeInterface::SCOPE_STORE);
        $template = (string) $this->scopeConfig->getValue(self::XML_PATH_USER_TEMPLATE, ScopeInterface::SCOPE_STORE);

        if ($sender === '' || $template === '') {
            return false;
        }

        return $this->dispatch(
            $template,
            $sender,
            $question->getSenderEmail(),
            $question,
            $question->getSenderName()
        );
    }

    /**
     * Build and send the transactional email.
     *
     * @param string $template
     * @param string $sender
     * @param string $to
     * @param QuestionInterface $question
     * @param string|null $toName
     * @return bool
     */
    private function dispatch(
        string $template,
        string $sender,
        string $to,
        QuestionInterface $question,
        ?string $toName = null
    ): bool {
        try {
            $this->inlineTranslation->suspend();
            $store = $this->storeManager->getStore();
            $recipients = array_filter(array_map('trim', explode(',', $to)));

            if (empty($recipients)) {
                return false;
            }

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store->getId(),
                ])
                ->setTemplateVars([
                    'question' => $question,
                    'question_title' => $question->getTitle(),
                    'question_full_answer' => $question->getFullAnswer(),
                    'question_short_answer' => $question->getShortAnswer(),
                    'sender_name' => $question->getSenderName(),
                    'sender_email' => $question->getSenderEmail(),
                    'store' => $store,
                ])
                ->setFromByScope($sender, $store->getId());

            foreach ($recipients as $recipient) {
                $transport->addTo($recipient, $toName ?? '');
            }

            $transport->getTransport()->sendMessage();

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Magendoo FAQ email error: ' . $e->getMessage());
            return false;
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
