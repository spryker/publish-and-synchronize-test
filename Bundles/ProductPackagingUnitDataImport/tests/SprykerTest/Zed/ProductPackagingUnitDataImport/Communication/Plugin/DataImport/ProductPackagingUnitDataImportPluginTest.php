<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductPackagingUnitDataImport\Communication\Plugin\DataImport;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DataImporterConfigurationTransfer;
use Generated\Shared\Transfer\DataImporterReaderConfigurationTransfer;
use Generated\Shared\Transfer\DataImporterReportTransfer;
use Generated\Shared\Transfer\SpyProductEntityTransfer;
use Generated\Shared\Transfer\SpyProductPackagingUnitTypeEntityTransfer;
use Spryker\Zed\ProductPackagingUnitDataImport\Communication\Plugin\DataImport\ProductPackagingUnitDataImportPlugin;
use Spryker\Zed\ProductPackagingUnitDataImport\ProductPackagingUnitDataImportConfig;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group ProductPackagingUnitDataImport
 * @group Communication
 * @group Plugin
 * @group DataImport
 * @group ProductPackagingUnitDataImportPluginTest
 * Add your own group annotations below this line
 */
class ProductPackagingUnitDataImportPluginTest extends Unit
{
    protected const PRODUCT_SKU_1 = 'concrete_sku_example_1';
    protected const PRODUCT_SKU_2 = 'concrete_sku_example_2';
    protected const PACKAGING_TYPE_DEFAULT = 'item';
    protected const PACKAGING_TYPE = 'box';

    /**
     * @var \SprykerTest\Zed\ProductPackagingUnitDataImport\ProductPackagingUnitDataImportCommunicationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testImportImportsData(): void
    {
        $this->tester->truncateProductPackagingUnits();
        $this->tester->truncateProductPackagingUnitTypes();
        $this->tester->truncateProductPackagingLeadProducts();
        $this->tester->assertProductPackagingUnitTableIsEmtpy();
        $this->tester->assertProductPackagingUnitTypeTableIsEmtpy();
        $this->tester->assertProductPackagingLeadProductTableIsEmtpy();

        $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE_DEFAULT]);
        $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE]);
        $productConcreteTransfer1 = $this->tester->haveProduct([SpyProductEntityTransfer::SKU => static::PRODUCT_SKU_1]);
        $productConcreteTransfer2 = $this->tester->haveProduct([SpyProductEntityTransfer::SKU => static::PRODUCT_SKU_2]);

        $dataImporterReaderConfigurationTransfer = new DataImporterReaderConfigurationTransfer();
        $dataImporterReaderConfigurationTransfer->setFileName(codecept_data_dir() . 'import/product_packaging_unit.csv');

        $dataImportConfigurationTransfer = new DataImporterConfigurationTransfer();
        $dataImportConfigurationTransfer->setReaderConfiguration($dataImporterReaderConfigurationTransfer);

        $dataImportPlugin = new ProductPackagingUnitDataImportPlugin();
        $dataImporterReportTransfer = $dataImportPlugin->import($dataImportConfigurationTransfer);

        $this->assertInstanceOf(DataImporterReportTransfer::class, $dataImporterReportTransfer);
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());

        $this->tester->assertProductPackagingUnitTableHasRecords();
        $this->tester->assertProductPackagingLeadProductTableHasRecords();
        $this->tester->cleanupProductPackagingLeadProduct($productConcreteTransfer1->getFkProductAbstract());
        $this->tester->cleanupProductPackagingLeadProduct($productConcreteTransfer2->getFkProductAbstract());
    }

    /**
     * @return void
     */
    public function testGetImportTypeReturnsTypeOfImporter(): void
    {
        $dataImportPlugin = new ProductPackagingUnitDataImportPlugin();
        $this->assertSame(ProductPackagingUnitDataImportConfig::IMPORT_TYPE_PRODUCT_PACKAGING_UNIT, $dataImportPlugin->getImportType());
    }
}
