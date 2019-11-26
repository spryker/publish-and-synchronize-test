<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductOptionsRestApi\Processor\RestResponseBuilder;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestLinkInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\ProductOptionsRestApi\Processor\Mapper\ProductOptionMapperInterface;
use Spryker\Glue\ProductOptionsRestApi\Processor\Sorter\ProductOptionSorterInterface;
use Spryker\Glue\ProductOptionsRestApi\ProductOptionsRestApiConfig;

class ProductOptionRestResponseBuilder implements ProductOptionRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\ProductOptionsRestApi\Processor\Mapper\ProductOptionMapperInterface
     */
    protected $productOptionMapper;

    /**
     * @var \Spryker\Glue\ProductOptionsRestApi\Processor\Sorter\ProductOptionSorterInterface
     */
    protected $productOptionSorter;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\ProductOptionsRestApi\Processor\Mapper\ProductOptionMapperInterface $productOptionMapper
     * @param \Spryker\Glue\ProductOptionsRestApi\Processor\Sorter\ProductOptionSorterInterface $productOptionSorter
     */
    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        ProductOptionMapperInterface $productOptionMapper,
        ProductOptionSorterInterface $productOptionSorter
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->productOptionMapper = $productOptionMapper;
        $this->productOptionSorter = $productOptionSorter;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer[] $productAbstractOptionStorageTransfers
     * @param array $resourceMapping
     * @param string $parentResourceType
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\SortInterface[] $sorts
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface[][]
     */
    public function createProductOptionRestResources(
        array $productAbstractOptionStorageTransfers,
        array $resourceMapping,
        string $parentResourceType,
        array $sorts
    ): array {
        $restProductOptionsAttributesTransfers = $this->getSortedRestProductOptionsAttributesTransfers(
            $productAbstractOptionStorageTransfers,
            $sorts
        );
        $productOptionRestResources = [];
        foreach ($resourceMapping as $parentResourceId => $idProductAbstract) {
            if (!isset($restProductOptionsAttributesTransfers[$idProductAbstract])) {
                continue;
            }

            $productOptionRestResources[$parentResourceId] = $this->prepareRestResources(
                $restProductOptionsAttributesTransfers[$idProductAbstract],
                $parentResourceType,
                $parentResourceId
            );
        }

        return $productOptionRestResources;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractOptionStorageTransfer[] $productAbstractOptionStorageTransfers
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\SortInterface[] $sorts
     *
     * @return \Generated\Shared\Transfer\RestProductOptionsAttributesTransfer[][]
     */
    protected function getSortedRestProductOptionsAttributesTransfers(
        array $productAbstractOptionStorageTransfers,
        array $sorts
    ): array {
        $restProductOptionsAttributesTransfers = [];
        foreach ($productAbstractOptionStorageTransfers as $idProductAbstract => $productAbstractOptionStorageTransfer) {
            $restProductOptionsAttributesTransfers[$idProductAbstract] = $this->productOptionMapper
                ->mapProductAbstractOptionStorageTransferToRestProductOptionsAttributesTransfers(
                    $productAbstractOptionStorageTransfer
                );

            $restProductOptionsAttributesTransfers[$idProductAbstract] = $this->productOptionSorter
                ->sortRestProductOptionsAttributesTransfers(
                    $restProductOptionsAttributesTransfers[$idProductAbstract],
                    $sorts
                );
        }

        return $restProductOptionsAttributesTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\RestProductOptionsAttributesTransfer[] $restProductOptionsAttributesTransfers
     * @param string $parentResourceType
     * @param string $parentResourceId
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface[]
     */
    protected function prepareRestResources(
        array $restProductOptionsAttributesTransfers,
        string $parentResourceType,
        string $parentResourceId
    ): array {
        $restResources = [];
        foreach ($restProductOptionsAttributesTransfers as $restProductOptionsAttributesTransfer) {
            $restResource = $this->restResourceBuilder->createRestResource(
                ProductOptionsRestApiConfig::RESOURCE_PRODUCT_OPTIONS,
                $restProductOptionsAttributesTransfer->getSku(),
                $restProductOptionsAttributesTransfer
            );
            $restResource->addLink(
                RestLinkInterface::LINK_SELF,
                $this->generateSelfLink($parentResourceType, $parentResourceId, $restProductOptionsAttributesTransfer->getSku())
            );
            $restResources[] = $restResource;
        }

        return $restResources;
    }

    /**
     * @param string $parentResourceType
     * @param string $parentResourceId
     * @param string $productOptionSku
     *
     * @return string
     */
    protected function generateSelfLink(string $parentResourceType, string $parentResourceId, string $productOptionSku): string
    {
        return sprintf(
            '%s/%s/%s/%s',
            $parentResourceType,
            $parentResourceId,
            ProductOptionsRestApiConfig::RESOURCE_PRODUCT_OPTIONS,
            $productOptionSku
        );
    }
}
