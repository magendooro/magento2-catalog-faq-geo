<?php
/**
 * Magendoo Faq Robots Meta Source Model
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
 * Robots meta source model
 */
class RobotsMeta implements OptionSourceInterface
{
    /**
     * Robots meta values
     */
    public const INDEX_FOLLOW = 'INDEX,FOLLOW';
    public const NOINDEX_FOLLOW = 'NOINDEX,FOLLOW';
    public const INDEX_NOFOLLOW = 'INDEX,NOFOLLOW';
    public const NOINDEX_NOFOLLOW = 'NOINDEX,NOFOLLOW';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::INDEX_FOLLOW,
                'label' => __('INDEX, FOLLOW')
            ],
            [
                'value' => self::NOINDEX_FOLLOW,
                'label' => __('NOINDEX, FOLLOW')
            ],
            [
                'value' => self::INDEX_NOFOLLOW,
                'label' => __('INDEX, NOFOLLOW')
            ],
            [
                'value' => self::NOINDEX_NOFOLLOW,
                'label' => __('NOINDEX, NOFOLLOW')
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
            self::INDEX_FOLLOW => __('INDEX, FOLLOW'),
            self::NOINDEX_FOLLOW => __('NOINDEX, FOLLOW'),
            self::INDEX_NOFOLLOW => __('INDEX, NOFOLLOW'),
            self::NOINDEX_NOFOLLOW => __('NOINDEX, NOFOLLOW')
        ];
    }
}
