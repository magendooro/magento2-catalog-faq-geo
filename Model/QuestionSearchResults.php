<?php
/**
 * Magendoo Faq Question Search Results
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Model;

use Magento\Framework\Api\SearchResults;
use Magendoo\Faq\Api\Data\QuestionSearchResultsInterface;

/**
 * FAQ Question Search Results
 */
class QuestionSearchResults extends SearchResults implements QuestionSearchResultsInterface
{
    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return parent::getItems();
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items): static
    {
        return parent::setItems($items);
    }
}
