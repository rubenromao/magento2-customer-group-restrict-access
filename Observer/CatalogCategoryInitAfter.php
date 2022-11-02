<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Rubenromao\DbSecondTest\Model\HandleContentRestriction;

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
            $this->contentRestriction->isRestrictedContent($category, self::ATTRIBUTE_CODE);
        }

        return $this;
    }
}
