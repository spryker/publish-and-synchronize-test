<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable;

use Generated\Shared\Transfer\GuiTableColumnConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableConfigurationTransfer;
use Generated\Shared\Transfer\GuiTableDataTransfer;
use Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer;
use Spryker\Zed\ProductOfferGuiPage\Communication\Table\AbstractTable;
use Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable\CriteriaBuilder\ProductOfferTableCriteriaBuilderInterface;
use Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable\DataProvider\ProductOfferTableDataProviderInterface;
use Spryker\Zed\ProductOfferGuiPage\Dependency\Facade\ProductOfferGuiPageToTranslatorFacadeInterface;
use Spryker\Zed\ProductOfferGuiPage\Dependency\Service\ProductOfferGuiPageToUtilEncodingServiceInterface;

class ProductOfferTable extends AbstractTable
{
    public const COL_KEY_OFFER_REFERENCE = 'name';
    public const COL_KEY_MERCHANT_SKU = 'merchantSku';
    public const COL_KEY_IMAGE = 'image';
    public const COL_KEY_STORES = 'stores';
    public const COL_KEY_STOCK = 'stock';
    public const COL_KEY_APPROVAL_STATUS = 'approvalStatus';
    public const COL_KEY_PRODUCT_NAME = 'productName';
    public const COL_KEY_VALID_FROM = 'validFrom';
    public const COL_KEY_VALID_TO = 'validTo';
    public const COL_KEY_CREATED_AT = 'createdAt';
    public const COL_KEY_UPDATED_AT = 'updatedAt';

    protected const PATTERN_DATE_FORMAT = 'DD/MM/YYYY';

    protected const SEARCH_PLACEHOLDER = 'TBA';

    /**
     * @uses \Spryker\Zed\ProductOfferGuiPage\Communication\Controller\ProductTableController::getDataAction()
     */
    protected const DATA_URL = 'TBA';

    /**
     * @var \Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable\DataProvider\ProductOfferTableDataProviderInterface
     */
    protected $productOfferTableDataProvider;

    /**
     * @var \Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable\CriteriaBuilder\ProductOfferTableCriteriaBuilderInterface
     */
    protected $productOfferTableCriteriaBuilder;

    /**
     * @param \Spryker\Zed\ProductOfferGuiPage\Dependency\Service\ProductOfferGuiPageToUtilEncodingServiceInterface $utilEncodingService
     * @param \Spryker\Zed\ProductOfferGuiPage\Dependency\Facade\ProductOfferGuiPageToTranslatorFacadeInterface $translatorFacade
     * @param \Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable\DataProvider\ProductOfferTableDataProviderInterface $productTableDataProvider
     * @param \Spryker\Zed\ProductOfferGuiPage\Communication\Table\ProductOfferTable\CriteriaBuilder\ProductOfferTableCriteriaBuilderInterface $productOfferTableCriteriaBuilder
     */
    public function __construct(
        ProductOfferGuiPageToUtilEncodingServiceInterface $utilEncodingService,
        ProductOfferGuiPageToTranslatorFacadeInterface $translatorFacade,
        ProductOfferTableDataProviderInterface $productTableDataProvider,
        ProductOfferTableCriteriaBuilderInterface $productOfferTableCriteriaBuilder
    ) {
        parent::__construct($utilEncodingService, $translatorFacade);
        $this->productOfferTableDataProvider = $productTableDataProvider;
        $this->productOfferTableCriteriaBuilder = $productOfferTableCriteriaBuilder;
    }

    /**
     * @return \Generated\Shared\Transfer\GuiTableDataTransfer
     */
    protected function provideTableData(): GuiTableDataTransfer
    {
        $productTableCriteriaTransfer = $this->buildProductOfferTableCriteriaTransfer();

        return $this->productOfferTableDataProvider->getProductOfferTableData($productTableCriteriaTransfer);
    }

    /**
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function buildTableConfiguration(): GuiTableConfigurationTransfer
    {
        $guiTableConfigurationTransfer = new GuiTableConfigurationTransfer();
        $guiTableConfigurationTransfer = $this->addColumnsToConfiguration($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->addRowActionsToConfiguration($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer = $this->addSearchOptionsToConfiguration($guiTableConfigurationTransfer);
        $guiTableConfigurationTransfer->setDefaultSortColumn($this->getDefaultSortColumnKey());
        $guiTableConfigurationTransfer->setDataUrl(static::DATA_URL);

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function addColumnsToConfiguration(GuiTableConfigurationTransfer $guiTableConfigurationTransfer): GuiTableConfigurationTransfer
    {
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_MERCHANT_SKU)
                ->setTitle('Merchant SKU')
                ->setType('text')
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_IMAGE)
                ->setTitle('Image')
                ->setType('image')
                ->setSortable(false)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_PRODUCT_NAME)
                ->setTitle('Name')
                ->setType('text')
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_OFFER_REFERENCE)
                ->setTitle('Name')
                ->setType('text')
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_STORES)
                ->setTitle('Stores')
                ->setType('text')
                ->setSortable(false)
                ->setHideable(true)
                ->setMultiRenderMode(true)
                ->setMultiRenderModeLimit(2)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_STOCK)
                ->setTitle('Stock')
                ->setType('chip')
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_APPROVAL_STATUS)
                ->setTitle('Status')
                ->setType('text')
                ->setSortable(false)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_VALID_FROM)
                ->setTitle('Valid From')
                ->setType('date')
                ->addTypeOption('format', static::PATTERN_DATE_FORMAT)
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_VALID_TO)
                ->setTitle('Valid To')
                ->setType('date')
                ->addTypeOption('format', static::PATTERN_DATE_FORMAT)
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_CREATED_AT)
                ->setTitle('Valid From')
                ->setType('date')
                ->addTypeOption('format', static::PATTERN_DATE_FORMAT)
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );
        $guiTableConfigurationTransfer->addColumn(
            (new GuiTableColumnConfigurationTransfer())
                ->setId(static::COL_KEY_UPDATED_AT)
                ->setTitle('Valid To')
                ->setType('date')
                ->addTypeOption('format', static::PATTERN_DATE_FORMAT)
                ->setSortable(true)
                ->setHideable(false)
                ->setMultiRenderMode(false)
        );

        return $guiTableConfigurationTransfer;
    }

    /**
     * @return string
     */
    protected function getDefaultSortColumnKey(): string
    {
        return static::COL_KEY_MERCHANT_SKU;
    }

    /**
     * @return \Generated\Shared\Transfer\ProductOfferTableCriteriaTransfer
     */
    protected function buildProductOfferTableCriteriaTransfer(): ProductOfferTableCriteriaTransfer
    {
        return $this->productOfferTableCriteriaBuilder
            ->setSearchTerm($this->searchTerm)
            ->setPage($this->page)
            ->setPageSize($this->pageSize)
            ->setSorting($this->sorting)
            ->setFilters($this->filters)
            ->build();
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function addRowActionsToConfiguration(GuiTableConfigurationTransfer $guiTableConfigurationTransfer): GuiTableConfigurationTransfer
    {
        // TODO: TBA

        return $guiTableConfigurationTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\GuiTableConfigurationTransfer $guiTableConfigurationTransfer
     *
     * @return \Generated\Shared\Transfer\GuiTableConfigurationTransfer
     */
    protected function addSearchOptionsToConfiguration(GuiTableConfigurationTransfer $guiTableConfigurationTransfer): GuiTableConfigurationTransfer
    {
        $guiTableConfigurationTransfer->addSearchOption('placeholder', static::SEARCH_PLACEHOLDER);

        return $guiTableConfigurationTransfer;
    }
}
