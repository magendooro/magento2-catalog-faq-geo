<?php
/**
 * Magendoo Faq Question Status Source Model
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Model\Source\Question;

use Magendoo\Faq\Api\Data\QuestionInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Question status source model
 */
class Status implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => QuestionInterface::STATUS_PENDING,
                'label' => __('Pending')
            ],
            [
                'value' => QuestionInterface::STATUS_ANSWERED,
                'label' => __('Answered')
            ],
            [
                'value' => QuestionInterface::STATUS_REJECTED,
                'label' => __('Rejected')
            ]
        ];
    }

    /**
     * Get options as array (value => label)
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            QuestionInterface::STATUS_PENDING => __('Pending'),
            QuestionInterface::STATUS_ANSWERED => __('Answered'),
            QuestionInterface::STATUS_REJECTED => __('Rejected')
        ];
    }
}
