<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Product\Business\Product;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Spryker\Shared\Product\ProductConstants;
use Spryker\Zed\Product\Business\Attribute\AttributeEncoderInterface;
use Spryker\Zed\Product\Business\Exception\MissingProductException;
use Spryker\Zed\Product\Business\Product\Assertion\ProductAbstractAssertionInterface;
use Spryker\Zed\Product\Business\Product\Assertion\ProductConcreteAssertionInterface;
use Spryker\Zed\Product\Business\Product\Plugin\PluginConcreteManagerInterface;
use Spryker\Zed\Product\Business\Transfer\ProductTransferMapperInterface;
use Spryker\Zed\Product\Dependency\Facade\ProductToLocaleInterface;
use Spryker\Zed\Product\Dependency\Facade\ProductToTouchInterface;
use Spryker\Zed\Product\Persistence\ProductQueryContainerInterface;

class ProductConcreteManager implements ProductConcreteManagerInterface
{

    /**
     * @var \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface
     */
    protected $productQueryContainer;

    /**
     * @var \Spryker\Zed\Product\Dependency\Facade\ProductToTouchInterface
     */
    protected $touchFacade;

    /**
     * @var \Spryker\Zed\Product\Dependency\Facade\ProductToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\Product\Business\Product\Assertion\ProductAbstractAssertionInterface
     */
    protected $productAbstractAssertion;

    /**
     * @var \Spryker\Zed\Product\Business\Product\Assertion\ProductConcreteAssertionInterface
     */
    protected $productConcreteAssertion;

    /**
     * @var \Spryker\Zed\Product\Business\Product\Plugin\PluginConcreteManagerInterface
     */
    protected $pluginConcreteManager;

    /**
     * @var \Spryker\Zed\Product\Business\Attribute\AttributeEncoderInterface
     */
    private $attributeEncoder;

    /**
     * @var \Spryker\Zed\Product\Business\Transfer\ProductTransferMapperInterface
     */
    private $productTransferMapper;

    /**
     * @param \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface $productQueryContainer
     * @param \Spryker\Zed\Product\Dependency\Facade\ProductToTouchInterface $touchFacade
     * @param \Spryker\Zed\Product\Dependency\Facade\ProductToLocaleInterface $localeFacade
     * @param \Spryker\Zed\Product\Business\Product\Assertion\ProductAbstractAssertionInterface $productAbstractAssertion
     * @param \Spryker\Zed\Product\Business\Product\Assertion\ProductConcreteAssertionInterface $productConcreteAssertion
     * @param \Spryker\Zed\Product\Business\Product\Plugin\PluginConcreteManagerInterface $pluginConcreteManager
     * @param \Spryker\Zed\Product\Business\Attribute\AttributeEncoderInterface $attributeEncoder
     * @param \Spryker\Zed\Product\Business\Transfer\ProductTransferMapperInterface $productTransferMapper
     */
    public function __construct(
        ProductQueryContainerInterface $productQueryContainer,
        ProductToTouchInterface $touchFacade,
        ProductToLocaleInterface $localeFacade,
        ProductAbstractAssertionInterface $productAbstractAssertion,
        ProductConcreteAssertionInterface $productConcreteAssertion,
        PluginConcreteManagerInterface $pluginConcreteManager,
        AttributeEncoderInterface $attributeEncoder,
        ProductTransferMapperInterface $productTransferMapper
    ) {
        $this->productQueryContainer = $productQueryContainer;
        $this->touchFacade = $touchFacade;
        $this->localeFacade = $localeFacade;
        $this->productAbstractAssertion = $productAbstractAssertion;
        $this->productConcreteAssertion = $productConcreteAssertion;
        $this->pluginConcreteManager = $pluginConcreteManager;
        $this->attributeEncoder = $attributeEncoder;
        $this->productTransferMapper = $productTransferMapper;
    }

    /**
     * @param string $sku
     *
     * @return bool
     */
    public function hasProductConcrete($sku)
    {
        return $this->productQueryContainer
            ->queryProductConcreteBySku($sku)
            ->count() > 0;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return int
     */
    public function createProductConcrete(ProductConcreteTransfer $productConcreteTransfer)
    {
        $this->productQueryContainer->getConnection()->beginTransaction();

        $sku = $productConcreteTransfer->getSku();
        $this->productConcreteAssertion->assertSkuIsUnique($sku);

        $productConcreteTransfer = $this->pluginConcreteManager->triggerBeforeCreatePlugins($productConcreteTransfer);

        $productConcreteEntity = $this->persistEntity($productConcreteTransfer);

        $idProductConcrete = $productConcreteEntity->getPrimaryKey();
        $productConcreteTransfer->setIdProductConcrete($idProductConcrete);

        $this->persistProductConcreteLocalizedAttributes($productConcreteTransfer);

        $this->pluginConcreteManager->triggerAfterCreatePlugins($productConcreteTransfer);

        $this->productQueryContainer->getConnection()->commit();

        return $idProductConcrete;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return int
     */
    public function saveProductConcrete(ProductConcreteTransfer $productConcreteTransfer)
    {
        $this->productQueryContainer->getConnection()->beginTransaction();

        $sku = $productConcreteTransfer
            ->requireSku()
            ->getSku();

        $idProduct = (int)$productConcreteTransfer
            ->requireIdProductConcrete()
            ->getIdProductConcrete();

        $idProductAbstract = (int)$productConcreteTransfer
            ->requireFkProductAbstract()
            ->getFkProductAbstract();

        $this->productAbstractAssertion->assertProductExists($idProductAbstract);
        $this->productConcreteAssertion->assertProductExists($idProduct);
        $this->productConcreteAssertion->assertSkuIsUniqueWhenUpdatingProduct($idProduct, $sku);

        $productConcreteTransfer = $this->pluginConcreteManager->triggerBeforeUpdatePlugins($productConcreteTransfer);

        $productConcreteEntity = $this->persistEntity($productConcreteTransfer);

        $idProductConcrete = $productConcreteEntity->getPrimaryKey();
        $productConcreteTransfer->setIdProductConcrete($idProductConcrete);

        $this->persistProductConcreteLocalizedAttributes($productConcreteTransfer);

        $this->pluginConcreteManager->triggerAfterUpdatePlugins($productConcreteTransfer);

        $this->productQueryContainer->getConnection()->commit();

        return $idProductConcrete;
    }

    /**
     * @param int $idProductConcrete
     *
     * @return void
     */
    public function touchProductActive($idProductConcrete)
    {
        $this->touchFacade->touchActive(ProductConstants::RESOURCE_TYPE_PRODUCT_CONCRETE, $idProductConcrete);
    }

    /**
     * @param int $idProductConcrete
     *
     * @return void
     */
    public function touchProductInactive($idProductConcrete)
    {
        $this->touchFacade->touchInactive(ProductConstants::RESOURCE_TYPE_PRODUCT_CONCRETE, $idProductConcrete);
    }

    /**
     * @param int $idProductConcrete
     *
     * @return void
     */
    public function touchProductDeleted($idProductConcrete)
    {
        $this->touchFacade->touchDeleted(ProductConstants::RESOURCE_TYPE_PRODUCT_CONCRETE, $idProductConcrete);
    }

    /**
     * @param int $idProduct
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer|null
     */
    public function getProductConcreteById($idProduct)
    {
        $productEntity = $this->productQueryContainer
            ->queryProduct()
            ->filterByIdProduct($idProduct)
            ->findOne();

        if (!$productEntity) {
            return null;
        }

        $productTransfer = $this->productTransferMapper->convertProduct($productEntity);
        $productTransfer = $this->loadProductData($productTransfer);

        return $productTransfer;
    }

    /**
     * @param string $sku
     *
     * @return int|null
     */
    public function getProductConcreteIdBySku($sku)
    {
        $productEntity = $this->productQueryContainer
            ->queryProduct()
            ->filterBySku($sku)
            ->findOne();

        if (!$productEntity) {
            return null;
        }

        return $productEntity->getIdProduct();
    }

    /**
     * @param string $concreteSku
     *
     * @throws \Spryker\Zed\Product\Business\Exception\MissingProductException
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function getProductConcrete($concreteSku)
    {
        $idProduct = (int)$this->getProductConcreteIdBySku($concreteSku);
        $productConcreteTransfer = $this->getProductConcreteById($idProduct);

        if (!$productConcreteTransfer) {
            throw new MissingProductException(
                sprintf(
                    'Tried to retrieve a product concrete with sku %s, but it does not exist.',
                    $concreteSku
                )
            );
        }

        return $productConcreteTransfer;
    }

    /**
     * @param int $idProductAbstract
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer[]
     */
    public function getConcreteProductsByAbstractProductId($idProductAbstract)
    {
        $entityCollection = $this->productQueryContainer
            ->queryProduct()
            ->filterByFkProductAbstract($idProductAbstract)
            ->joinSpyProductAbstract()
            ->find();

        $transferCollection = $this->productTransferMapper->convertProductCollection($entityCollection);

        $numberOfProducts = count($transferCollection);
        for ($a = 0; $a < $numberOfProducts; $a++) {
            $transferCollection[$a] = $this->loadProductData($transferCollection[$a]);
        }

        return $transferCollection;
    }

    /**
     * @param string $sku
     *
     * @throws \Spryker\Zed\Product\Business\Exception\MissingProductException
     *
     * @return int
     */
    public function getProductAbstractIdByConcreteSku($sku)
    {
        $productConcrete = $this->productQueryContainer
            ->queryProductConcreteBySku($sku)
            ->findOne();

        if (!$productConcrete) {
            throw new MissingProductException(
                sprintf(
                    'Tried to retrieve a product concrete with sku %s, but it does not exist.',
                    $sku
                )
            );
        }

        return $productConcrete->getFkProductAbstract();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractTransfer $productAbstractTransfer
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProduct
     */
    public function findProductEntityByAbstractAndConcrete(ProductAbstractTransfer $productAbstractTransfer, ProductConcreteTransfer $productConcreteTransfer)
    {
        return $this->productQueryContainer
            ->queryProduct()
            ->filterByFkProductAbstract($productAbstractTransfer->getIdProductAbstract())
            ->filterByIdProduct($productConcreteTransfer->getIdProductConcrete())
            ->findOne();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    public function getLocalizedProductConcreteName(ProductConcreteTransfer $productConcreteTransfer, LocaleTransfer $localeTransfer)
    {
        foreach ($productConcreteTransfer->getLocalizedAttributes() as $localizedAttribute) {
            if ($localizedAttribute->getLocale()->getIdLocale() === $localeTransfer->getIdLocale()) {
                return $localizedAttribute->getName();
            }
        }

        return $productConcreteTransfer->getSku();
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Orm\Zed\Product\Persistence\SpyProduct
     */
    protected function persistEntity(ProductConcreteTransfer $productConcreteTransfer)
    {
        $sku = $productConcreteTransfer
            ->requireSku()
            ->getSku();

        $fkProductAbstract = $productConcreteTransfer
            ->requireFkProductAbstract()
            ->getFkProductAbstract();

        $encodedAttributes = $this->attributeEncoder->encodeAttributes(
            $productConcreteTransfer->getAttributes()
        );

        $productConcreteEntity = $this->productQueryContainer
            ->queryProduct()
            ->filterByIdProduct($productConcreteTransfer->getIdProductConcrete())
            ->findOneOrCreate();

        $productConcreteEntity
            ->setSku($sku)
            ->setFkProductAbstract($fkProductAbstract)
            ->setAttributes($encodedAttributes)
            ->setIsActive((bool)$productConcreteTransfer->getIsActive());

        $productConcreteEntity->save();

        return $productConcreteEntity;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected function loadProductData(ProductConcreteTransfer $productTransfer)
    {
        $this->loadLocalizedAttributes($productTransfer);

        $this->pluginConcreteManager->triggerReadPlugins($productTransfer);

        return $productTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected function loadLocalizedAttributes(ProductConcreteTransfer $productTransfer)
    {
        $productAttributeCollection = $this->productQueryContainer
            ->queryProductLocalizedAttributes($productTransfer->getIdProductConcrete())
            ->find();

        foreach ($productAttributeCollection as $attributeEntity) {
            $localeTransfer = $this->localeFacade->getLocaleById($attributeEntity->getFkLocale());

            $localizedAttributesData = $attributeEntity->toArray();
            if (isset($localizedAttributesData[LocalizedAttributesTransfer::ATTRIBUTES])) {
                unset($localizedAttributesData[LocalizedAttributesTransfer::ATTRIBUTES]);
            }

            $localizedAttributesTransfer = (new LocalizedAttributesTransfer())
                ->fromArray($localizedAttributesData, true)
                ->setAttributes($this->attributeEncoder->decodeAttributes($attributeEntity->getAttributes()))
                ->setLocale($localeTransfer);

            $productTransfer->addLocalizedAttributes($localizedAttributesTransfer);
        }

        return $productTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return void
     */
    protected function persistProductConcreteLocalizedAttributes(ProductConcreteTransfer $productConcreteTransfer)
    {
        $idProductConcrete = $productConcreteTransfer
            ->requireIdProductConcrete()
            ->getIdProductConcrete();

        $this->productQueryContainer->getConnection()->beginTransaction();

        foreach ($productConcreteTransfer->getLocalizedAttributes() as $localizedAttributes) {
            $locale = $localizedAttributes->getLocale();
            $jsonAttributes = $this->attributeEncoder->encodeAttributes($localizedAttributes->getAttributes());

            $localizedProductAttributesEntity = $this->productQueryContainer
                ->queryProductConcreteAttributeCollection($idProductConcrete, $locale->getIdLocale())
                ->findOneOrCreate();

            $localizedProductAttributesEntity
                ->setFkProduct($idProductConcrete)
                ->setFkLocale($locale->requireIdLocale()->getIdLocale())
                ->setName($localizedAttributes->requireName()->getName())
                ->setAttributes($jsonAttributes)
                ->setDescription($localizedAttributes->getDescription());

            $localizedProductAttributesEntity->save();
        }

        $this->productQueryContainer->getConnection()->commit();
    }

}
