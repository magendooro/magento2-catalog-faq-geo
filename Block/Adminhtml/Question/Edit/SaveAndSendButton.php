<?php
/**
 * Magendoo Faq "Save and Send Email" admin button
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Block\Adminhtml\Question\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Secondary save button that also dispatches the "answer ready" customer
 * email template. Only visible on existing questions that have a sender
 * email address (otherwise there's nothing to send to).
 */
class SaveAndSendButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $questionId = $this->getQuestionId();
        if (!$questionId) {
            return [];
        }

        return [
            'label' => __('Save and Send Email'),
            'class' => 'save secondary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'faq_question_form.faq_question_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    ['send_email' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 85,
        ];
    }
}
