<?php
declare(strict_types=1);

namespace Rubenromao\Magento2CustomerGroupRestrictAccess\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Create CMS Page to display restricted content message.
 */
class RestrictedContentCmsPage implements DataPatchInterface
{
    private const IDENTIFIER = 'restricted-content';

    /**
     * RestrictedContentCmsPage constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $pageFactory
     * @param PageRepository $pageRepository
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private PageFactory $pageFactory,
        private PageRepository $pageRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $page = $this->pageFactory->create();
        $page->setTitle('Restricted Content')
            ->setIdentifier(self::IDENTIFIER)
            ->setIsActive(true)
            ->setPageLayout('1column')
            ->setContent(__('Your Customer Group isn\'t authorised to access the requested content.'));
        $this->pageRepository->save($page);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
