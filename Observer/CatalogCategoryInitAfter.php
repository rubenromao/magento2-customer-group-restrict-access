<?php
declare(strict_types=1);

namespace Rubenromao\Magento2CustomerGroupRestrictAccess\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Rubenromao\Magento2CustomerGroupRestrictAccess\Model\HandleContentRestriction;

/**
 * Observer to check if the category can be shown.
 */
class CatalogCategoryInitAfter implements ObserverInterface
{
    private const ATTRIBUTE_CODE = 'category_customer_group';

    /**
     * CatalogCategoryInitAfter Constructor.
     *
     * @param HandleContentRestriction $contentRestriction
     */
    public function __construct(
        private HandleContentRestriction $contentRestriction,
    ) {
    }

    /**
     * Execute observer.
     *
     * @param Observer $observer
     * @return CatalogCategoryInitAfter
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getCategory();
        if ($category) {

            // this method will check if the content is restricted for the customer's group
            $this->contentRestriction->isRestrictedCatalogContent($category, self::ATTRIBUTE_CODE);
        }

        return $this;
    }
}
