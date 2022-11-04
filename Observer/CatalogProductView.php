<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Rubenromao\DbSecondTest\Model\HandleContentRestriction;

/**
 * Observer to check if the product can be shown.
 */
class CatalogProductView implements ObserverInterface
{
    private const ATTRIBUTE_CODE = 'product_customer_group';

    /**
     * CatalogProductView Constructor.
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
     * @return CatalogProductView
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        if ($product) {

            // this method will check if the content is restricted for the customer's group
            $this->contentRestriction->isRestrictedCatalogContent($product, self::ATTRIBUTE_CODE);
        }

        return $this;
    }
}
