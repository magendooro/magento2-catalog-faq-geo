<?php
/**
 * Magendoo Faq Sort Order Source Model
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Sort order source model
 */
class SortOrder implements OptionSourceInterface
{
    /**
     * Sort order values
     */
    public const POSITION = 'position';
    public const NAME = 'name';
    public const MOST_VIEWED = 'most_viewed';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::POSITION,
                'label' => __('Position')
            ],
            [
                'value' => self::NAME,
                'label' => __('Name')
            ],
            [
                'value' => self::MOST_VIEWED,
                'label' => __('Most Viewed')
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
            self::POSITION => __('Position'),
            self::NAME => __('Name'),
            self::MOST_VIEWED => __('Most Viewed')
        ];
    }
}
