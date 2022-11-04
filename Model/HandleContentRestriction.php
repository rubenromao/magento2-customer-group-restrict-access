<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Model;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;

/**
 * This class handles the customer access by his group.
 * It can be by catalog or CMS.
 */
class HandleContentRestriction
{
    public const ADMINISTRATOR_ROLE_NAME = 'Administrators';
    public const RESTRICTED_CONTENT_PAGE = 'restricted-content';

    /**
     * HandleContentRestriction Constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Session $customerSession
     * @param Context $httpContext
     * @param AdminSession $adminSession
     */
    public function __construct(
        private ResponseFactory $responseFactory,
        private UrlInterface $url,
        private Session $customerSession,
        private Context $httpContext,
        private AdminSession $adminSession,
    ) {
    }

    /**
     * @param $contentObj
     * @param null $attributeCode
     * @return bool
     */
    public function isRestrictedCatalogContent($contentObj, $attributeCode = null): bool
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

                // redirect the customer to a 404 page
                $resultRedirect = $this->responseFactory->create();
                $resultRedirect->setRedirect($this->url->getUrl(
                    self::RESTRICTED_CONTENT_PAGE
                ))->sendResponse('200');

                return true;
            }
        }
        return false;
    }

    /**
     * @param $subject
     * @param $attributeCode
     * @return void
     */
    public function isRestrictedCmsContent($subject, $attributeCode = null): void
    {
        // customer by session
        $customer = $this->customerSession->getCustomer();
        $customerGroupId = null;

        // if not Guest get group id
        if (!empty($customer->getGroupId())) {
            $customerGroupId = $customer->getGroupId();
        }

        // if current session user is administrator set group id to null
        if ($this->adminSession->getUser() !== null
            && $this->adminSession->getUser()->getRole()->getRoleName() === self::ADMINISTRATOR_ROLE_NAME) {
            $customerGroupId = null;
        }

        // get customer group ids for current product
        if (null !== $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)) {

            // get ids
            $customerGroupIds = $subject->getData($attributeCode);

            if (!is_array($customerGroupIds) && null !== $customerGroupIds) {

                // convert the string to array of
                $customerGroupIds = explode(',', $customerGroupIds);
            }

            // if the customer group id is set, redirect to a restrict-content page
            if (null !== $customerGroupIds) {
                if (in_array($customerGroupId, $customerGroupIds)) {

                    // redirect the customer to a restricted
                    // content message page
                    $resultRedirect = $this->responseFactory->create();
                    $resultRedirect->setRedirect($this->url->getUrl(
                        self::RESTRICTED_CONTENT_PAGE
                    ))->sendResponse('200');
                }
            }
        }
    }
}
