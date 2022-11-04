<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class CatalogProductView implements ObserverInterface
{
    private const ATTRIBUTE_CODE = 'product_customer_group';

    /**
     * CatalogProductView Constructor.
     *
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Session $customerSession
     */
    public function __construct(
        private ResponseFactory $responseFactory,
        private UrlInterface $url,
        private Session $customerSession,
    ) {
    }

    /**
     * Execute observer.
     *
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        if ($product) {

            // customer by session
            $customer = $this->customerSession->getCustomer();
            $customerGroupId = 0;

            // if not Guest get group id
            if (!empty($customer->getId())) {
                $customerGroupId = $customer->getGroupId();
            }

            // get customer group ids for current product
            if (null !== $product->getCustomAttribute(self::ATTRIBUTE_CODE)) {

                // get attr ids
                $restrictCustomerGroup = $product->getCustomAttribute(self::ATTRIBUTE_CODE)->getValue();

                // convert the string to array of
                $customerGroupIds = explode(',', $restrictCustomerGroup);

                // if the customer group id is set, redirect to a 404 page
                if (in_array($customerGroupId, $customerGroupIds)) {

                    // redirect the customer to a 404 page
                    $resultRedirect = $this->responseFactory->create();
                    $resultRedirect->setRedirect($this->url->getUrl('noroute'))->sendResponse('200');
                }
            }
        }

        return $this;
    }
}
