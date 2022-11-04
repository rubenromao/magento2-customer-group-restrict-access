<?php
declare(strict_types=1);

namespace Rubenromao\DbSecondTest\Model;

use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;

class Block extends \Magento\Cms\Model\Block
{
    /**
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param ManagerInterface $messageManager
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param WYSIWYGValidatorInterface|null $wysiwygValidator
     * @param CompositeUrlKey|null $compositeUrlValidator
     */
    public function __construct(
        private \Magento\Framework\App\Http\Context $httpContext,
        private ResponseFactory $responseFactory,
        private UrlInterface $url,
        private ManagerInterface $messageManager,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ?WYSIWYGValidatorInterface $wysiwygValidator = null,
        CompositeUrlKey $compositeUrlValidator = null,
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data,
            $wysiwygValidator,
            $compositeUrlValidator
        );
    }

    /**
     * Retrieve block content
     *
     * @return string
     */
    public function getContent(): string
    {
        $customerGroupId = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);

        if (!is_null($this->getNotVisibleCustomerGroup())) {

            $notVisibleGroup = explode(',', $this->getNotVisibleCustomerGroup());

            if (in_array($customerGroupId, $notVisibleGroup)) {

                // restricted access message
                $this->messageManager->addErrorMessage('Access restricted');

                // redirect the customer to a 404 page
                $resultRedirect = $this->responseFactory->create();
                $resultRedirect->setRedirect($this->url->getUrl('/'))->sendResponse('200');
                exit();
            }
        }

        return $this->getData(self::CONTENT);
    }
}
