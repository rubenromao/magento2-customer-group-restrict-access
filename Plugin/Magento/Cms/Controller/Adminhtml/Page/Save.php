<?php
declare(strict_types=1);

namespace Rubenromao\Magento2CustomerGroupRestrictAccess\Plugin\Magento\Cms\Controller\Adminhtml\Page;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Controller\Adminhtml\Page\Save as OriginalClass;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * This plugin saves the custom multiselect field
 * in the CMS Page admin panel with the
 * customer groups into the cms_page db table
 */
class Save
{
    /**
     * Save Constructor.
     *
     * @param PageFactory $pageFactory
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        private PageFactory $pageFactory,
        private PageRepositoryInterface $pageRepository,
    ) {
    }

    /**
     * @param OriginalClass $subject
     * @param $result
     * @return mixed
     * @throws LocalizedException
     * @throws \Exception
     */
    public function afterExecute(OriginalClass $subject, $result)
    {
        $data = $subject->getRequest()->getPostValue();

        // make sure that the field was sent.
        if ($data['page_restrict_customer_group']) {
            $restrictGroups = $this->checkCustomerGroupValueSet($data);

            /** @var Page $model */
            $model = $this->pageFactory->create();

            $id = $data['page_id'];
            if ($id) {
                $model = $this->pageRepository->getById($id);
            }

            $model->setData('page_restrict_customer_group', $restrictGroups);
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
        $data['page_restrict_customer_group'] = is_array($data['page_restrict_customer_group'])
                                                ? implode(',', $data['page_restrict_customer_group'])
                                                : null;

        return $data['page_restrict_customer_group'];
    }
}
