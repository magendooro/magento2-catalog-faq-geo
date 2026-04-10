<?php
/**
 * Magendoo Faq Tag Model
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magendoo\Faq\Api\Data\TagInterface;
use Magendoo\Faq\Model\ResourceModel\Tag as ResourceTag;

/**
 * FAQ Tag Model
 */
class Tag extends AbstractExtensibleModel implements TagInterface, IdentityInterface
{
    /**
     * Tag cache tag
     */
    public const CACHE_TAG = 'magendoo_faq_tag';

    /**
     * @var string
     */
    protected $_eventPrefix = 'magendoo_faq_tag';

    /**
     * @var string
     */
    protected $_eventObject = 'faq_tag';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(ResourceTag::class);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getTagId(): ?int
    {
        $id = $this->getData(self::TAG_ID);
        return $id ? (int) $id : null;
    }

    /**
     * @inheritdoc
     */
    public function setTagId(int $tagId): static
    {
        return $this->setData(self::TAG_ID, $tagId);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): static
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setUrlKey(?string $urlKey): static
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(?string $createdAt): static
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
