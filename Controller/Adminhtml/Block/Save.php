<?php

namespace Rubenromao\DbSecondTest\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Save extends \Magento\Cms\Controller\Adminhtml\Block
{
    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        private BlockFactory $blockFactory,
        private DataPersistorInterface $dataPersistor,
        Context $context,
        Registry $coreRegistry,
    ) {
        parent::__construct(
            $context,
            $coreRegistry
        );
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Block::STATUS_ENABLED;
            }
            if (empty($data['block_id'])) {
                $data['block_id'] = null;
            }

            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->blockFactory->create();

            $id = $this->getRequest()->getParam('block_id');
            if ($id) {
                try {
                    $model = $this->blockRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This block no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $data = $this->checkCustomerGroupValueSet($data);

            $model->setData($data);

            try {
                $this->blockRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the block.'));
                $this->dataPersistor->clear('cms_block');
                return $this->processBlockReturn($model, $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('cms_block', $data);
            return $resultRedirect->setPath('*/*/edit', ['block_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $data
     * @return mixed
     */
    private function checkCustomerGroupValueSet($data)
    {
        $data['restricted_customer_groups'] =
            !empty($data['restricted_customer_groups']) ? implode(',', $data['restricted_customer_groups']) : null;
        return $data;
    }
}
