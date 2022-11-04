<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Plugin\Magento\Cms\Model;

use Magento\Cms\Model\Block as OriginalClass;
use Rubenromao\DbSecondTest\Model\HandleContentRestriction;

/**
 * This plugin redirects the customer to the cms block "restricted-content"
 * if the customer isn't authorised to see the requested CMS Block
 */
class Block
{
    public const ATTRIBUTE_CODE = 'block_restrict_customer_group';

    /**
     * Block Constructor.
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
