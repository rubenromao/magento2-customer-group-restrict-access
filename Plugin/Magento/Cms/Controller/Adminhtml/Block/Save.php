<?php
declare(strict_types=1);

namespace Rubenromao\Magento2CustomerGroupRestrictAccess\Plugin\Magento\Cms\Controller\Adminhtml\Block;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Block\Save as OriginalClass;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * This plugin saves the custom multiselect field
 * in the CMS Block admin panel with the
 * customer groups into the cms_block db table
 */
class Save
{
    /**
     * Save Constructor.
     *
     * @param BlockFactory $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        private BlockFactory $blockFactory,
        private BlockRepositoryInterface $blockRepository,
    ) {
    }

    /**
     * @param OriginalClass $subject
     * @param $result
     * @return mixed
     * @throws LocalizedException
     * @throws \Exception
     */
    public function afterExecute(OriginalClass $subject, $result): mixed
    {
        $data = $subject->getRequest()->getPostValue();
        if ($data['block_restrict_customer_group']) {
            $restrictGroups = $this->checkCustomerGroupValueSet($data);

            /** @var Block $model */
            $model = $this->blockFactory->create();

            $id = $data['block_id'];
            if ($id) {
                $model = $this->blockRepository->getById($id);
            }

            $model->setData('block_restrict_customer_group', $restrictGroups);
            $model->save();
        }
        return $result;
    }

    /**
     * @param $data
     * @return string|null
     */
    private function checkCustomerGroupValueSet($data): ?string
    {
        $data['block_restrict_customer_group'] = is_array($data['block_restrict_customer_group'])
                                                ? implode(',', $data['block_restrict_customer_group'])
                                                : null;

        return $data['block_restrict_customer_group'];
    }
}
