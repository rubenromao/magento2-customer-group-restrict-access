<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Model\Attribute\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Source model with array with customer groups.
 */
class CustomerGroup extends AbstractSource
{
    /**
     * CustomerGroup Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * Get all options of customer group
     * @return array
     */
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options = $this->collectionFactory->create()->toOptionArray();
        }
        return $this->_options;
    }
}
