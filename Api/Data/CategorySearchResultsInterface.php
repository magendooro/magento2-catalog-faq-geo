<?php
/**
 * Magendoo Faq Category Search Results Interface
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for FAQ category search results
 *
 * @api
 */
interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get categories list
     *
     * @return \Magendoo\Faq\Api\Data\CategoryInterface[]
     */
    public function getItems(): array;

    /**
     * Set categories list
     *
     * @param \Magendoo\Faq\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items): static;
}
