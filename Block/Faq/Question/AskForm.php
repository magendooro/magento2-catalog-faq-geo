<?php
/**
 * Magendoo Faq Ask Question Form Block
 *
 * @category  Magendoo
 * @package   Magendoo_Faq
 * @copyright Copyright (c) Magendoo (https://magendoo.com)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Magendoo\Faq\Block\Faq\Question;

use Magendoo\Faq\Helper\Data as FaqHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Ask a Question form block
 */
class AskForm extends Template
{
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var FaqHelper
     */
    private FaqHelper $helper;

    /**
     * @var ProductMetadataInterface
     */
    private ProductMetadataInterface $productMetadata;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var FormKey
     */
    private FormKey $formKey;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param FaqHelper $helper
     * @param ProductMetadataInterface $productMetadata
     * @param Registry $registry
     * @param FormKey $formKey
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        FaqHelper $helper,
        ProductMetadataInterface $productMetadata,
        Registry $registry,
        FormKey $formKey,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
        $this->registry = $registry;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Check if unregistered visitors may submit questions.
     *
     * @return bool
     */
    public function isGuestAllowed(): bool
    {
        return $this->helper->isGuestQuestionAllowed();
    }

    /**
     * Check if the current customer is logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Whether the form should be rendered for the current visitor.
     *
     * @return bool
     */
    public function canShowForm(): bool
    {
        return $this->isLoggedIn() || $this->isGuestAllowed();
    }

    /**
     * Get the full name of the logged in customer, empty string otherwise.
     *
     * @return string
     */
    public function getLoggedInCustomerName(): string
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        $customer = $this->customerSession->getCustomer();
        $firstName = (string) $customer->getFirstname();
        $lastName = (string) $customer->getLastname();

        return trim($firstName . ' ' . $lastName);
    }

    /**
     * Get the email of the logged in customer, empty string otherwise.
     *
     * @return string
     */
    public function getLoggedInCustomerEmail(): string
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        return (string) $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * Get the current product id when on a product page.
     *
     * @return int|null
     */
    public function getCurrentProductId(): ?int
    {
        $product = $this->registry->registry('current_product');
        if ($product && $product->getId()) {
            return (int) $product->getId();
        }

        return null;
    }

    /**
     * Get the URL to submit the form to.
     *
     * @return string
     */
    public function getSubmitUrl(): string
    {
        return $this->getUrl('faq/question/submit');
    }

    /**
     * Get the current form key.
     *
     * @return string
     */
    public function getFormKey(): string
    {
        return (string) $this->formKey->getFormKey();
    }

    /**
     * Check if GDPR consent is enabled.
     *
     * @return bool
     */
    public function isGdprEnabled(): bool
    {
        return $this->helper->isGdprEnabled();
    }

    /**
     * Get the configured GDPR text.
     *
     * @return string
     */
    public function getGdprText(): string
    {
        return $this->helper->getGdprText();
    }

    /**
     * Get the customer account login URL.
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->getUrl('customer/account/login');
    }

    /**
     * Get the Magento product version (useful for cache-busting / diagnostics).
     *
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }
}
