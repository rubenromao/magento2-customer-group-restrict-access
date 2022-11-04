<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class CatalogCategoryInitAfter implements ObserverInterface
{
    private const ATTRIBUTE_CODE = 'category_customer_group';

    /**
     * CatalogCategoryInitAfter Constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Session $customerSession
     */
    public function __construct(
        private ResponseFactory $responseFactory,
        private UrlInterface $url,
        private Session $customerSession,
        private ManagerInterface $messageManager,
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

            // customer by session
            $customer = $this->customerSession->getCustomer();
            $customerGroupId = 0;

            // if not Guest get group id
            if (!empty($customer->getId())) {
                $customerGroupId = $customer->getGroupId();
            }

            // get customer group ids for current product
            if (null !== $category->getCustomAttribute(self::ATTRIBUTE_CODE)) {

                // get ids
                $restrictCustomerGroup = $category->getCustomAttribute(self::ATTRIBUTE_CODE)->getValue();

                // convert the string to array of
                $customerGroupIds = explode(',', $restrictCustomerGroup);

                // if the customer group id is set, redirect to a 404 page
                if (in_array($customerGroupId, $customerGroupIds)) {

                    // restricted access message
                    $this->messageManager->addErrorMessage('Access restricted');

                    // redirect the customer to a 404 page
                    $resultRedirect = $this->responseFactory->create();
                    $resultRedirect->setRedirect($this->url->getUrl('/'))->sendResponse('200');
                }
            }
        }

        return $this;
    }
}
