<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductPackagingUnit\Business;

use Generated\Shared\DataBuilder\ProductPackagingUnitTypeBuilder;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ProductMeasurementSalesUnitTransfer;
use Generated\Shared\Transfer\ProductPackagingLeadProductTransfer;
use Generated\Shared\Transfer\ProductPackagingUnitTypeTranslationTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SpyProductAbstractEntityTransfer;
use Generated\Shared\Transfer\SpyProductEntityTransfer;
use Generated\Shared\Transfer\SpyProductMeasurementUnitEntityTransfer;
use Generated\Shared\Transfer\SpyProductPackagingLeadProductEntityTransfer;
use Generated\Shared\Transfer\SpyProductPackagingUnitAmountEntityTransfer;
use Generated\Shared\Transfer\SpyProductPackagingUnitEntityTransfer;
use Generated\Shared\Transfer\SpyProductPackagingUnitTypeEntityTransfer;
use Generated\Shared\Transfer\SpySalesOrderItemEntityTransfer;
use Generated\Shared\Transfer\StoreTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductPackagingUnit
 * @group Business
 * @group Facade
 * @group ProductPackagingUnitFacadeTest
 * Add your own group annotations below this line
 */
class ProductPackagingUnitFacadeTest extends ProductPackagingUnitMocks
{
    protected const PACKAGING_TYPE_DEFAULT = 'item';
    protected const PACKAGING_TYPE = 'box';

    protected const ITEM_QUANTITY = 2;
    protected const PACKAGE_AMOUNT = 4;

    /**
     * @var \SprykerTest\Zed\ProductPackagingUnit\ProductPackagingUnitBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testInstallProductPackagingUnitTypesShouldPersistInfrastructuralPackagingUnitTypes(): void
    {
        // Assign
        $productPackagingUnitTypeTransfer = (new ProductPackagingUnitTypeBuilder())->build();
        $config = $this->createProductPackagingUnitConfigMock();
        $config->method('getInfrastructuralPackagingUnitTypes')
            ->willReturn([$productPackagingUnitTypeTransfer]);
        $factory = $this->createProductPackagingUnitBusinessFactoryMock($config);
        $facade = $this->createProductPackagingUnitFacadeMock($factory);

        // Action
        $facade->installProductPackagingUnitTypes();

        // Assert
        $productPackagingUnitTypeTransfer = $this->getFacade()->findProductPackagingUnitTypeByName($productPackagingUnitTypeTransfer);
        $this->assertNotNull($productPackagingUnitTypeTransfer->getIdProductPackagingUnitType());
    }

    /**
     * @dataProvider getProductPackagingUnitTypeData
     *
     * @param string $name
     * @param \Generated\Shared\Transfer\ProductPackagingUnitTypeTranslationTransfer ...$nameTranslations
     *
     * @return void
     */
    public function testCreateProductPackagingUnitTypeShouldPersistPackagingUnitType(string $name, ProductPackagingUnitTypeTranslationTransfer ... $nameTranslations): void
    {
        $productPackagingUnitTypeTransfer = (new ProductPackagingUnitTypeBuilder())
            ->build()
            ->setName($name);

        foreach ($nameTranslations as $nameTranslation) {
            $productPackagingUnitTypeTransfer->addProductPackagingUnitTypeTranslation($nameTranslation);
        }

        // Action
        $this->getFacade()->createProductPackagingUnitType($productPackagingUnitTypeTransfer);
        $productPackagingUnitTypeTransfer = $this->getFacade()->findProductPackagingUnitTypeByName($productPackagingUnitTypeTransfer);
        $this->assertNotNull($productPackagingUnitTypeTransfer->getIdProductPackagingUnitType());
        // Assert translations persisted
        $this->assertCount($productPackagingUnitTypeTransfer->getTranslations()->count(), $nameTranslations);
    }

    /**
     * @dataProvider getProductPackagingUnitTypeData
     *
     * @expectedException \Spryker\Zed\ProductPackagingUnit\Business\Exception\ProductPackagingUnitTypeNotFoundException
     *
     * @param string $name
     *
     * @return void
     */
    public function testDeleteProductPackagingUnitTypeShouldDeletePackagingUnitType(string $name): void
    {
        $productPackagingUnitTypeTransfer = (new ProductPackagingUnitTypeBuilder())
            ->build()
            ->setName($name);

        $this->getFacade()->createProductPackagingUnitType($productPackagingUnitTypeTransfer);

        // Action
        $productPackagingUnitTypeDeleted = $this->getFacade()->deleteProductPackagingUnitType($productPackagingUnitTypeTransfer);
        $this->assertTrue($productPackagingUnitTypeDeleted);
        // Assert exception thrown
        $this->getFacade()->getProductPackagingUnitTypeById($productPackagingUnitTypeTransfer);
    }

    /**
     * @return array
     */
    public function getProductPackagingUnitTypeData(): array
    {
        return [
            [
                'packaging_unit_type.test1.name',
                (new ProductPackagingUnitTypeTranslationTransfer())
                    ->setLocaleCode('en_US')
                    ->setName('name1'),
                (new ProductPackagingUnitTypeTranslationTransfer())
                    ->setLocaleCode('de_DE')
                    ->setName('Name1'),
            ],
        ];
    }

    /**
     * @dataProvider getProductPackagingUnitTypeDataForNameChange
     *
     * @param string $name
     * @param string $newName
     *
     * @return void
     */
    public function testUpdateProductPackagingUnitTypeShouldUpdatePackagingUnitType(string $name, string $newName): void
    {
        $productPackagingUnitTypeTransfer = (new ProductPackagingUnitTypeBuilder())
            ->build()
            ->setName($name);

        $productPackagingUnitTypeTransfer = $this->getFacade()->createProductPackagingUnitType($productPackagingUnitTypeTransfer);

        // Action
        $productPackagingUnitTypeTransfer->setName($newName);
        $productPackagingUnitTypeTransfer = $this->getFacade()->updateProductPackagingUnitType($productPackagingUnitTypeTransfer);
        $this->assertEquals($productPackagingUnitTypeTransfer->getName(), $newName);
    }

    /**
     * @return array
     */
    public function getProductPackagingUnitTypeDataForNameChange(): array
    {
        return [
            [
                'packaging_unit_type.test1.name',
                'packaging_unit_type.test2.name',
            ],
        ];
    }

    /**
     * @return void
     */
    public function testExpandCartChangeTransferWithAmountLeadProduct(): void
    {
        $itemProductConcreteTransfer = $this->tester->haveProduct();
        $boxProductConcreteTransfer = $this->tester->haveProduct([
            SpyProductEntityTransfer::FK_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ], [
            SpyProductAbstractEntityTransfer::ID_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ]);

        $this->tester->haveProductPackagingLeadProduct([
            SpyProductPackagingLeadProductEntityTransfer::FK_PRODUCT => $itemProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingLeadProductEntityTransfer::FK_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ]);

        $boxProductPackagingUnitType = $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE]);
        $itemProductPackagingUnitType = $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE_DEFAULT]);

        $this->tester->haveProductPackagingUnit([
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT => $itemProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT_PACKAGING_UNIT_TYPE => $itemProductPackagingUnitType->getIdProductPackagingUnitType(),
        ]);

        $this->tester->haveProductPackagingUnit([
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT => $boxProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT_PACKAGING_UNIT_TYPE => $boxProductPackagingUnitType->getIdProductPackagingUnitType(),
        ], [
            SpyProductPackagingUnitAmountEntityTransfer::DEFAULT_AMOUNT => static::PACKAGE_AMOUNT,
        ]);

        $cartChange = (new CartChangeTransfer())
            ->addItem(
                (new ItemTransfer())
                    ->setSku($boxProductConcreteTransfer->getSku())
                    ->setQuantity(static::ITEM_QUANTITY)
                    ->setAmount(static::PACKAGE_AMOUNT)
            );

        $this->getFacade()->expandCartChangeWithAmountLeadProduct($cartChange);
        foreach ($cartChange->getItems() as $itemTransfer) {
            $this->assertNotNull($itemTransfer->getAmountLeadProduct());
            $this->assertNotNull($itemTransfer->getAmountLeadProduct()->getSku());
            $this->assertEquals($itemProductConcreteTransfer->getSku(), $itemTransfer->getAmountLeadProduct()->getSku());
        }
    }

    /**
     * @return void
     */
    public function testPreCheckCartAvailability(): void
    {
        $cartChange = (new CartChangeTransfer())
            ->setQuote($this->createTestQuoteTransfer())
            ->addItem($this->createTestPackagingUnitItemTransfer());

        $this->getFacade()->expandCartChangeWithQuantityPackagingUnit($cartChange);

        // Action
        $cartPreCheckResponseTransfer = $this->getFacade()->preCheckCartAvailability($cartChange);
        $this->assertFalse($cartPreCheckResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testCheckoutAvailabilityPreCheck(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $quoteTransfer = $this->createTestQuoteTransfer()
            ->addItem($this->createTestPackagingUnitItemTransfer());

        // Action
        $this->getFacade()
            ->checkoutAvailabilityPreCheck($quoteTransfer, $checkoutResponseTransfer);

        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testUpdateProductPackagingUnitLeadProductAvailability(): void
    {
        $itemProductConcreteTransfer = $this->tester->haveProduct();
        $boxProductConcreteTransfer = $this->tester->haveProduct([
            SpyProductEntityTransfer::FK_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ], [
            SpyProductAbstractEntityTransfer::ID_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ]);

        $this->tester->haveProductPackagingLeadProduct([
            SpyProductPackagingLeadProductEntityTransfer::FK_PRODUCT => $itemProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingLeadProductEntityTransfer::FK_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ]);

        $boxProductPackagingUnitType = $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE]);
        $itemProductPackagingUnitType = $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE_DEFAULT]);

        $this->tester->haveProductPackagingUnit([
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT => $itemProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT_PACKAGING_UNIT_TYPE => $itemProductPackagingUnitType->getIdProductPackagingUnitType(),
        ]);

        $this->tester->haveProductPackagingUnit([
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT => $boxProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT_PACKAGING_UNIT_TYPE => $boxProductPackagingUnitType->getIdProductPackagingUnitType(),
            SpyProductPackagingUnitEntityTransfer::HAS_LEAD_PRODUCT => true,
        ], [
            SpyProductPackagingUnitAmountEntityTransfer::DEFAULT_AMOUNT => static::PACKAGE_AMOUNT,
        ]);

        $this->getFacade()->updateProductPackagingUnitLeadProductAvailability($boxProductConcreteTransfer->getSku());
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createTestQuoteTransfer(): QuoteTransfer
    {
        return (new QuoteTransfer())
            ->setStore(
                (new StoreTransfer())
                    ->setIdStore(1)
                    ->setName('DE')
            );
    }

    /**
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function createTestPackagingUnitItemTransfer(): ItemTransfer
    {
        $itemProductConcreteTransfer = $this->tester->haveProduct();
        $boxProductConcreteTransfer = $this->tester->haveProduct([
            SpyProductEntityTransfer::FK_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ], [
            SpyProductAbstractEntityTransfer::ID_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ]);

        $this->tester->haveProductPackagingLeadProduct([
            SpyProductPackagingLeadProductEntityTransfer::FK_PRODUCT => $itemProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingLeadProductEntityTransfer::FK_PRODUCT_ABSTRACT => $itemProductConcreteTransfer->getFkProductAbstract(),
        ]);

        $boxProductPackagingUnitType = $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE]);
        $itemProductPackagingUnitType = $this->tester->haveProductPackagingUnitType([SpyProductPackagingUnitTypeEntityTransfer::NAME => static::PACKAGING_TYPE_DEFAULT]);

        $itemProductPackagingUnit = $this->tester->haveProductPackagingUnit([
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT => $itemProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT_PACKAGING_UNIT_TYPE => $itemProductPackagingUnitType->getIdProductPackagingUnitType(),
        ]);

        $boxProductPackagingUnit = $this->tester->haveProductPackagingUnit([
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT => $boxProductConcreteTransfer->getIdProductConcrete(),
            SpyProductPackagingUnitEntityTransfer::FK_PRODUCT_PACKAGING_UNIT_TYPE => $boxProductPackagingUnitType->getIdProductPackagingUnitType(),
            SpyProductPackagingUnitEntityTransfer::HAS_LEAD_PRODUCT => true,
        ], [
            SpyProductPackagingUnitAmountEntityTransfer::DEFAULT_AMOUNT => static::PACKAGE_AMOUNT,
        ]);

        return (new ItemTransfer())
            ->setQuantity(static::ITEM_QUANTITY)
            ->setSku($boxProductConcreteTransfer->getSku())
            ->setAmount(static::PACKAGE_AMOUNT)
            ->setAmountLeadProduct(
                (new ProductPackagingLeadProductTransfer())
                    ->setSku($boxProductConcreteTransfer->getSku())
            );
    }

    /**
     * @return \Spryker\Zed\ProductPackagingUnit\Business\ProductPackagingUnitFacadeInterface|\Spryker\Zed\Kernel\Business\AbstractFacade
     */
    protected function getFacade()
    {
        return $this->tester->getFacade();
    }

    /**
     * @return void
     */
    public function testExpandOrderItemWithAmountSalesUnit(): void
    {
        // Assign
        $code = 'MYCODE' . random_int(1, 100);
        $productTransfer = $this->tester->haveProduct();
        $productMeasurementUnitTransfer = $this->tester->haveProductMeasurementUnit([
            SpyProductMeasurementUnitEntityTransfer::CODE => $code,
        ]);
        $productMeasurementBaseUnitTransfer = $this->tester->haveProductMeasurementBaseUnit(
            $productTransfer->getFkProductAbstract(),
            $productMeasurementUnitTransfer->getIdProductMeasurementUnit()
        );
        $productMeasurementBaseUnitTransfer->setProductMeasurementUnit($productMeasurementUnitTransfer);

        $productMeasurementSalesUnitTransfer = $this->tester->haveProductMeasurementSalesUnit(
            $productTransfer->getIdProductConcrete(),
            $productMeasurementUnitTransfer->getIdProductMeasurementUnit(),
            $productMeasurementBaseUnitTransfer->getIdProductMeasurementBaseUnit()
        );

        $productMeasurementSalesUnitTransfer->setProductMeasurementUnit($productMeasurementUnitTransfer);
        $productMeasurementSalesUnitTransfer->setProductMeasurementBaseUnit($productMeasurementBaseUnitTransfer);

        $amountSalesUnitTransfer = (new ProductMeasurementSalesUnitTransfer())->fromArray($productMeasurementSalesUnitTransfer->toArray(), true);
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setAmountSalesUnit($amountSalesUnitTransfer);

        //Act
        $salesOrderItemEntity = $this->getFacade()->expandOrderItemWithAmountSalesUnit(
            $itemTransfer,
            new SpySalesOrderItemEntityTransfer()
        );

        //Assert
        $this->assertSame($productMeasurementUnitTransfer->getName(), $salesOrderItemEntity->getAmountMeasurementUnitName());
        $this->assertSame($productMeasurementUnitTransfer->getName(), $salesOrderItemEntity->getAmountBaseMeasurementUnitName());
        $this->assertSame($amountSalesUnitTransfer->getPrecision(), $salesOrderItemEntity->getAmountMeasurementUnitPrecision());
        $this->assertSame($amountSalesUnitTransfer->getConversion(), $salesOrderItemEntity->getAmountMeasurementUnitConversion());
    }

    /**
     * @return void
     */
    public function testExpandOrderItemWithAmountAndAmountSku(): void
    {
        // Assign
        $itemTransfer = $this->createTestPackagingUnitItemTransfer();

        //Act
        $salesOrderItemEntity = $this->getFacade()->exportOrderItemWithAmountAndAmountSku(
            $itemTransfer,
            new SpySalesOrderItemEntityTransfer()
        );

        //Assert
        $this->assertSame($itemTransfer->getAmount(), $salesOrderItemEntity->getAmount());
        $this->assertSame($itemTransfer->getAmountLeadProduct()->getSku(), $salesOrderItemEntity->getAmountSku());
    }

    /**
     * @return void
     */
    public function testHydrateAmountSalesUnit()
    {
        // Assign
        $salesOrderEntity = $this->tester->create();
        $orderTransfer = (new OrderTransfer())->fromArray($salesOrderEntity->toArray(), true);

        $salesOrderItemEntities = $salesOrderEntity->getItems();
        foreach ($salesOrderItemEntities as $salesOrderItem) {
            $itemTransfer = (new ItemTransfer())->fromArray($salesOrderItem->toArray(), true);
            $orderTransfer->addItem($itemTransfer);
        }

        //Act
        $orderTransfer = $this->getFacade()->hydrateAmountSalesUnit($orderTransfer);

        //Assert
        $this->assertInstanceOf(OrderTransfer::class, $orderTransfer);
    }
}
