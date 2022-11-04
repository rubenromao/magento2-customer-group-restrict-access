<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Rubenromao\DbSecondTest\Model\Attribute\Source\CustomerGroup;

class ProductCustomerGroupAttribute implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'product_customer_group';
    private const ATTRIBUTE_LABEL = 'Restrict Visibility Of Product To Customer Group';

    /**
     * ProductCustomerGroupAttribute constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => __(self::ATTRIBUTE_LABEL),
                'input' => 'multiselect',
                'source' => CustomerGroup::class,
                'backend' => ArrayBackend::class,
                'required' => false,
                'sort_order' => 80,
                'global' => ScopedAttributeInterface::SCOPE_STORE
            ]
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
