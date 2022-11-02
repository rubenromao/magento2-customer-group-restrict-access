<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class HandleContentRestriction
{
    /**
     * HandleContentRestriction Constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private ResponseFactory $responseFactory,
        private UrlInterface $url,
        private Session $customerSession,
        private ManagerInterface $messageManager,
    ) {
    }

    /**
     * @param $contentObj
     * @param null $attributeCode
     * @return bool
     */
    public function isRestrictedContent($contentObj, $attributeCode = null): bool
    {
        // customer by session
        $customer = $this->customerSession->getCustomer();
        $customerGroupId = 0;

        // if not Guest get group id
        if (!empty($customer->getId())) {
            $customerGroupId = $customer->getGroupId();
        }

        // get customer group ids for current product
        if (null !== $contentObj->getCustomAttribute($attributeCode)) {

            // get ids
            $restrictCustomerGroup = $contentObj->getCustomAttribute($attributeCode)->getValue();

            // convert the string to array of
            $customerGroupIds = explode(',', $restrictCustomerGroup);

            // if the customer group id is set, redirect to a restrict-content page
            if (in_array($customerGroupId, $customerGroupIds)) {
                // restricted access message
                $this->messageManager->addErrorMessage('Access restricted');

                // redirect the customer to a 404 page
                $resultRedirect = $this->responseFactory->create();
                $resultRedirect->setRedirect($this->url->getUrl('restricted-content'))->sendResponse('200');

                return true;
            }
        }
        return false;
    }
}
