<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Plugin\Magento\Cms\Model;

use Magento\Cms\Model\Page as OriginalClass;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * This plugin redirects the customer to the cms page "restricted-content"
 * if the customer isn't authorised to see the requested CMS Page
 */
class Page
{
    /**
     * Page Constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Session $customerSession
     * @param Context $httpContext
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private ResponseFactory  $responseFactory,
        private UrlInterface     $url,
        private Session          $customerSession,
        private Context          $httpContext,
        private ManagerInterface $messageManager,
    ) {
    }

    /**
     * @param OriginalClass $subject
     * @return void
     */
    public function beforeGetContent(OriginalClass $subject): void
    {
        // customer by session
        $customer = $this->customerSession->getCustomer();
        $customerGroupId = null;

        // if not Guest get group id
        if (!empty($customer->getGroupId())) {
            $customerGroupId = $customer->getGroupId();
        }

        // get customer group ids for current product
        if (null !== $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)) {

            // get ids
            $customerGroupIds = $subject->getData('page_restrict_customer_group');

            if (!is_array($customerGroupIds) && null !== $customerGroupIds) {
                // convert the string to array of
                $customerGroupIds = explode(',', $customerGroupIds);
            }

            // if the customer group id is set, redirect to a restrict-content page
            if (null !== $customerGroupIds) {

                if (in_array($customerGroupId, $customerGroupIds)) {
                    // restricted access message
                    $this->messageManager->addErrorMessage('Access restricted');

                    // redirect the restricted content page
                    $resultRedirect = $this->responseFactory->create();

                    $resultRedirect->setRedirect($this->url->getUrl('restricted-content'))->sendResponse('200');
                    exit();
                }
            }
        }
    }
}
