<?php
/**
 * Magendoo Faq Tag Search Results Interface
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
 * Interface for FAQ tag search results
 *
 * @api
 */
interface TagSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get tags list
     *
     * @return \Magendoo\Faq\Api\Data\TagInterface[]
     */
    public function getItems(): array;

    /**
     * Set tags list
     *
     * @param \Magendoo\Faq\Api\Data\TagInterface[] $items
     * @return $this
     */
    public function setItems(array $items): static;
}
