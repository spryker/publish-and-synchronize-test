<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\ProductQuantityStorage\Validator;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\ProductQuantityStorageTransfer;
use Generated\Shared\Transfer\ProductQuantityValidationResponseTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\ProductQuantityStorage\Storage\ProductQuantityStorageReaderInterface;

class ProductQuantityValidator implements ProductQuantityValidatorInterface
{
    protected const ERROR_QUANTITY_MIN_NOT_FULFILLED = 'product-quantity.errors.quantity.min.failed';
    protected const ERROR_QUANTITY_MAX_NOT_FULFILLED = 'product-quantity.errors.quantity.max.failed';
    protected const ERROR_QUANTITY_INTERVAL_NOT_FULFILLED = 'product-quantity.errors.quantity.interval.failed';

    /**
     * @var \Spryker\Client\ProductQuantityStorage\Storage\ProductQuantityStorageReaderInterface
     */
    protected $productQuantityStorageReader;

    /**
     * @param \Spryker\Client\ProductQuantityStorage\Storage\ProductQuantityStorageReaderInterface $productQuantityStorageReader
     */
    public function __construct(ProductQuantityStorageReaderInterface $productQuantityStorageReader)
    {
        $this->productQuantityStorageReader = $productQuantityStorageReader;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return \Generated\Shared\Transfer\ProductQuantityValidationResponseTransfer
     */
    public function validate(ProductViewTransfer $productViewTransfer): ProductQuantityValidationResponseTransfer
    {
        $productViewTransfer->requireIdProductConcrete();

        $response = (new ProductQuantityValidationResponseTransfer())
            ->setIsValid(true);

        if (!$this->isApplicable($productViewTransfer)) {
            return $response;
        }

        $productQuantityStorageTransfer = $this->findProductQuantityStorageTransfer($productViewTransfer);
        if ($productQuantityStorageTransfer === null) {
            return $response;
        }

        $quantity = $productViewTransfer->getQuantity();
        $quantityMin = $productQuantityStorageTransfer->getQuantityMin();
        $quantityMax = $productQuantityStorageTransfer->getQuantityMax();
        $quantityInterval = $productQuantityStorageTransfer->getQuantityInterval();

        $this->validateQuantityMin($quantity, $quantityMin, $response);
        $this->validateQuantityInterval($quantity, $quantityMin, $quantityInterval, $response);
        $this->validateQuantityMax($quantity, $quantityMax, $response);

        return $response;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return bool
     */
    protected function isApplicable(ProductViewTransfer $productViewTransfer): bool
    {
        return $productViewTransfer->getQuantity() !== null;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return \Generated\Shared\Transfer\ProductQuantityStorageTransfer|null
     */
    protected function findProductQuantityStorageTransfer(ProductViewTransfer $productViewTransfer): ?ProductQuantityStorageTransfer
    {
        return $this->productQuantityStorageReader->findProductQuantityStorage($productViewTransfer->getIdProductConcrete());
    }

    /**
     * @param int $quantity
     * @param int $quantityMin
     * @param \Generated\Shared\Transfer\ProductQuantityValidationResponseTransfer $response
     *
     * @return void
     */
    protected function validateQuantityMin(int $quantity, int $quantityMin, ProductQuantityValidationResponseTransfer $response): void
    {
        if ($quantity >= $quantityMin) {
            return;
        }

        $this->addViolationMessage(
            $response,
            static::ERROR_QUANTITY_MIN_NOT_FULFILLED,
            [$quantity, $quantityMin]
        );
    }

    /**
     * @param int $quantity
     * @param int $quantityMin
     * @param int $quantityInterval
     * @param \Generated\Shared\Transfer\ProductQuantityValidationResponseTransfer $response
     *
     * @return void
     */
    protected function validateQuantityInterval(int $quantity, int $quantityMin, int $quantityInterval, ProductQuantityValidationResponseTransfer $response): void
    {
        // Shifted intervals: min = 5; interval = 3; Valid quantities: 5, 8, 11, ...
        if (($quantity - $quantityMin) % $quantityInterval === 0) {
            return;
        }

        $this->addViolationMessage(
            $response,
            static::ERROR_QUANTITY_INTERVAL_NOT_FULFILLED,
            [$quantity, $quantityInterval]
        );
    }

    /**
     * @param int $quantity
     * @param int|null $quantityMax
     * @param \Generated\Shared\Transfer\ProductQuantityValidationResponseTransfer $response
     *
     * @return void
     */
    protected function validateQuantityMax(int $quantity, ?int $quantityMax, ProductQuantityValidationResponseTransfer $response): void
    {
        if ($quantityMax === null) {
            return;
        }

        if ($quantity <= $quantityMax) {
            return;
        }

        $this->addViolationMessage(
            $response,
            static::ERROR_QUANTITY_MAX_NOT_FULFILLED,
            [$quantity, $quantityMax]
        );
    }

    /**
     * @param \Generated\Shared\Transfer\ProductQuantityValidationResponseTransfer $response
     * @param string $glossaryKey
     * @param string[] $replacements
     *
     * @return void
     */
    protected function addViolationMessage(ProductQuantityValidationResponseTransfer $response, string $glossaryKey, array $replacements): void
    {
        $response
            ->setIsValid(false)
            ->addMessage(
                (new MessageTransfer())
                    ->setValue($glossaryKey)
                    ->setParameters($replacements)
            );
    }
}
