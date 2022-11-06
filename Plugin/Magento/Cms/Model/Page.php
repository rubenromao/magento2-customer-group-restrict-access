<?php
declare(strict_types=1);

namespace Rubenromao\Magento2CustomerGroupRestrictAccess\Plugin\Magento\Cms\Model;

use Magento\Cms\Model\Page as OriginalClass;
use Rubenromao\Magento2CustomerGroupRestrictAccess\Model\HandleContentRestriction;

/**
 * This plugin redirects the customer to the cms page "restricted-content"
 * if the customer isn't authorised to see the requested CMS Page
 */
class Page
{
    public const ATTRIBUTE_CODE = 'page_restrict_customer_group';

    /**
     * Page Constructor.
     *
     * @param HandleContentRestriction $contentRestriction
     */
    public function __construct(
        private HandleContentRestriction $contentRestriction,
    ) {
    }

    /**
     * @param OriginalClass $subject
     * @return void
     */
    public function beforeGetContent(OriginalClass $subject): void
    {
        // this method will check if the content is restricted for the customer's group
        $this->contentRestriction->isRestrictedCmsContent($subject, self::ATTRIBUTE_CODE);
    }
}
