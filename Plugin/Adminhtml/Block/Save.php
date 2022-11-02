<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Model\Block;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Save extends \Magento\Cms\Controller\Adminhtml\Block
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('block_id');

            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Block::STATUS_ENABLED;
            }
            if (empty($data['block_id'])) {
                $data['block_id'] = null;
            }

            /** @var Block $model */
            $model = $this->_objectManager->create(Block::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This block no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $data = $this->checkCustomerGroupValueSet($data);

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the block.'));
                $this->dataPersistor->clear('cms_block');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['block_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the block.'));
            }

            $this->dataPersistor->set('cms_block', $data);
            return $resultRedirect->setPath('*/*/edit', ['block_id' => $this->getRequest()->getParam('block_id')]);
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
                !empty($data['restricted_customer_groups']) ?
                implode(',', $data['restricted_customer_groups']) :
                null;
        return $data;
    }
}
