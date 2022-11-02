<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Cms\Controller\Adminhtml\Page\Save
{
    //public PageRepository $pageRepository;
//    /**
//     * @param PageFactory $pageFactory
//     * @param PageRepository $pageRepository
//     * @param Context $context
//     */
    public function __construct(
//        private PageFactory $pageFactory,
        public PageRepository $pageRepository,
//        Context $context,
    ) {
//        parent::__construct(
//            $context,
//            $this->dataProcessor,
//            $this->dataPersistor,
//        );
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Page::STATUS_ENABLED;
            }
            if (empty($data['page_id'])) {
                $data['page_id'] = null;
            }

            /** @var Page $model */
            $model = $this->pageFactory->create();

            $id = $this->getRequest()->getParam('page_id');
            if ($id) {
                try {
                    $model = $this->pageRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $data = $this->checkCustomerGroupValueSet($data);

            $data['layout_update_xml'] = $model->getLayoutUpdateXml();
            $data['custom_layout_update_xml'] = $model->getCustomLayoutUpdateXml();
            $model->setData($data);

            try {
                $this->_eventManager->dispatch(
                    'cms_page_prepare_save',
                    ['page' => $model, 'request' => $this->getRequest()]
                );

                $this->pageRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the page.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the page.'));
            }

            $this->dataPersistor->set('cms_page', $data);
            return $resultRedirect->setPath('*/*/edit', ['page_id' => $this->getRequest()->getParam('page_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process result redirect
     *
     * @param PageInterface $model
     * @param Redirect $resultRedirect
     * @param array $data
     * @return Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newPage = $this->pageFactory->create(['data' => $data]);
            $newPage->setId(null);
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newPage->setIdentifier($identifier);
            $newPage->setIsActive(false);
            $this->pageRepository->save($newPage);
            $this->messageManager->addSuccessMessage(__('You duplicated the page.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'page_id' => $newPage->getId(),
                    '_current' => true,
                ]
            );
        }
        $this->dataPersistor->clear('cms_page');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['page_id' => $model->getId(), '_current' => true]);
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
