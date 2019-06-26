<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Shipment\Business\Shipment;

use ArrayObject;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\ShipmentGroupResponseTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Service\Shipment\ShipmentServiceInterface;
use Spryker\Shared\Shipment\ShipmentConstants;
use Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaverInterface;
use Spryker\Zed\Shipment\Business\Mapper\ShipmentMapperInterface;
use Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface;
use Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpanderInterface;

class ShipmentSaver implements ShipmentSaverInterface
{
    /**
     * @var \Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaverInterface
     */
    protected $shipmentOrderSaver;

    /**
     * @var \Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpanderInterface
     */
    protected $shipmentMethodExpander;

    /**
     * @var \Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface
     */
    protected $expenseSanitizer;

    /**
     * @var \Spryker\Service\Shipment\ShipmentServiceInterface
     */
    protected $shipmentService;

    /**
     * @var \Spryker\Zed\Shipment\Business\Mapper\ShipmentMapperInterface
     */
    protected $shipmentMapper;

    /**
     * @param \Spryker\Zed\Shipment\Business\Checkout\MultiShipmentOrderSaverInterface $shipmentOrderSaver
     * @param \Spryker\Zed\Shipment\Business\ShipmentGroup\ShipmentMethodExpanderInterface $shipmentMethodExpander
     * @param \Spryker\Zed\Shipment\Business\Sanitizer\ExpenseSanitizerInterface $expenseSanitizer
     * @param \Spryker\Service\Shipment\ShipmentServiceInterface $shipmentService
     * @param \Spryker\Zed\Shipment\Business\Mapper\ShipmentMapperInterface $shipmentMapper
     */
    public function __construct(
        MultiShipmentOrderSaverInterface $shipmentOrderSaver,
        ShipmentMethodExpanderInterface $shipmentMethodExpander,
        ExpenseSanitizerInterface $expenseSanitizer,
        ShipmentServiceInterface $shipmentService,
        ShipmentMapperInterface $shipmentMapper
    ) {
        $this->shipmentOrderSaver = $shipmentOrderSaver;
        $this->shipmentMethodExpander = $shipmentMethodExpander;
        $this->expenseSanitizer = $expenseSanitizer;
        $this->shipmentService = $shipmentService;
        $this->shipmentMapper = $shipmentMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupResponseTransfer
     */
    public function saveShipment(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        OrderTransfer $orderTransfer
    ): ShipmentGroupResponseTransfer {
        $shipmentGroupResponseTransfer = (new ShipmentGroupResponseTransfer())->setIsSuccessful(false);
        if (!$this->isOrderShipmentUnique($shipmentGroupTransfer->requireShipment()->getShipment(), $orderTransfer)) {
            return $shipmentGroupResponseTransfer;
        }

        $saveOrderTransfer = $this->buildSaveOrderTransfer($orderTransfer);
        $shipmentGroupTransfer = $this->setShipmentMethod($shipmentGroupTransfer, $orderTransfer);

        $expenseTransfer = $this->createShippingExpenseTransfer($shipmentGroupTransfer->getShipment(), $orderTransfer);
        $orderTransfer = $this->addShippingExpenseToOrderExpenses($orderTransfer, $expenseTransfer);

        $shipmentGroupTransfer = $this->shipmentOrderSaver
            ->saveOrderShipmentByShipmentGroup($orderTransfer, $shipmentGroupTransfer, $saveOrderTransfer);

        return $shipmentGroupResponseTransfer
            ->setIsSuccessful(true)
            ->setShipmentGroup($shipmentGroupTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\SaveOrderTransfer
     */
    protected function buildSaveOrderTransfer(OrderTransfer $orderTransfer): SaveOrderTransfer
    {
        return (new SaveOrderTransfer())
            ->setOrderItems($orderTransfer->getItems())
            ->setIdSalesOrder($orderTransfer->getIdSalesOrder())
            ->setOrderReference($orderTransfer->getOrderReference())
            ->setOrderExpenses($orderTransfer->getExpenses());
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer|null
     */
    protected function getShippingExpenseTransfer(
        ShipmentTransfer $shipmentTransfer,
        OrderTransfer $orderTransfer
    ): ?ExpenseTransfer {
        $shipmentMethodHashKey = $this->shipmentService->getShipmentHashKey($shipmentTransfer);
        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            $expenseShipmentTransfer = $expenseTransfer->getShipment();
            if ($expenseShipmentTransfer === null) {
                continue;
            }

            if (!$this->isShipmentEqualToShipmentHash($expenseShipmentTransfer, $shipmentMethodHashKey)) {
                continue;
            }

            $expenseTransfer->setShipment($shipmentTransfer);

            return $expenseTransfer;
        }

        return $this->createShippingExpenseTransfer($shipmentTransfer, $orderTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function createShippingExpenseTransfer(
        ShipmentTransfer $shipmentTransfer,
        OrderTransfer $orderTransfer
    ): ExpenseTransfer {
        $shipmentMethodTransfer = $shipmentTransfer->requireMethod()->getMethod();

        $expenseTransfer = $this->shipmentMapper
            ->mapShipmentMethodTransferToExpenseTransfer($shipmentMethodTransfer, new ExpenseTransfer());

        $expenseTransfer->setFkSalesOrder($orderTransfer->getIdSalesOrder());
        $expenseTransfer->setType(ShipmentConstants::SHIPMENT_EXPENSE_TYPE);
        $price = $shipmentTransfer->getIdSalesShipment() !== null ? $shipmentMethodTransfer->getStoreCurrencyPrice() : 0;
        $this->setPrice(
            $expenseTransfer,
            $price,
            $orderTransfer->getPriceMode()
        );
        $expenseTransfer->setQuantity(1);
        $expenseTransfer->setShipment($shipmentTransfer);

        return $this->expenseSanitizer->sanitizeExpenseSumValues($expenseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\ExpenseTransfer|null $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function addShippingExpenseToOrderExpenses(
        OrderTransfer $orderTransfer,
        ?ExpenseTransfer $expenseTransfer
    ): OrderTransfer {
        if ($expenseTransfer === null) {
            return $orderTransfer;
        }

        $orderTransfer = $this->removeExistingShippingExpenseFromOrderExpenses($expenseTransfer, $orderTransfer);
        $orderTransfer->addExpense($expenseTransfer);

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function removeExistingShippingExpenseFromOrderExpenses(
        ExpenseTransfer $expenseTransfer,
        OrderTransfer $orderTransfer
    ): OrderTransfer {
        $orderExpensesCollection = new ArrayObject();

        foreach ($orderTransfer->getExpenses() as $orderExpenseTransfer) {
            if ($expenseTransfer->getShipment() === $orderExpenseTransfer->getShipment()) {
                continue;
            }

            $orderExpensesCollection->append($expenseTransfer);
        }

        $orderTransfer->setExpenses($orderExpensesCollection);

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    protected function setShipmentMethod(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        OrderTransfer $orderTransfer
    ): ShipmentGroupTransfer {
        $shipmentTransfer = $shipmentGroupTransfer->requireShipment()->getShipment();
        $shipmentMethodTransfer = $shipmentTransfer->requireMethod()->getMethod();

        $shipmentTransfer->setMethod($this->shipmentMethodExpander->expand($shipmentMethodTransfer, $orderTransfer));

        return $shipmentGroupTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param string $shipmentMethodHashKey
     *
     * @return bool
     */
    protected function isShipmentEqualToShipmentHash(
        ShipmentTransfer $shipmentTransfer,
        string $shipmentMethodHashKey
    ): bool {
        return $this->shipmentService->getShipmentHashKey($shipmentTransfer) === $shipmentMethodHashKey;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return bool
     */
    protected function isOrderShipmentUnique(ShipmentTransfer $shipmentTransfer, OrderTransfer $orderTransfer): bool
    {
        $itemTransfers = $orderTransfer->requireItems()->getItems();
        $orderShipmentGroupTransfers = $this->shipmentService->groupItemsByShipment($itemTransfers);
        if ($orderShipmentGroupTransfers->count() === 0) {
            return true;
        }

        $shipmentHasKey = $this->shipmentService->getShipmentHashKey($shipmentTransfer);
        $originalIdSalesShipment = $shipmentTransfer->getIdSalesShipment();
        foreach ($orderShipmentGroupTransfers as $orderShipmentGroupTransfer) {
            $idSalesShipment = $orderShipmentGroupTransfer->requireShipment()->getShipment()->getIdSalesShipment();
            if ($orderShipmentGroupTransfer->getHash() === $shipmentHasKey && $originalIdSalesShipment !== $idSalesShipment) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $shipmentExpenseTransfer
     * @param int $price
     * @param string $priceMode
     *
     * @return void
     */
    protected function setPrice(ExpenseTransfer $shipmentExpenseTransfer, int $price, string $priceMode): void
    {
        if ($priceMode === ShipmentConstants::PRICE_MODE_NET) {
            $shipmentExpenseTransfer->setUnitGrossPrice(0);
            $shipmentExpenseTransfer->setUnitPriceToPayAggregation(0);
            $shipmentExpenseTransfer->setUnitPrice($price);
            $shipmentExpenseTransfer->setUnitNetPrice($price);

            return;
        }

        $shipmentExpenseTransfer->setUnitPriceToPayAggregation(0);
        $shipmentExpenseTransfer->setUnitNetPrice(0);
        $shipmentExpenseTransfer->setUnitPrice($price);
        $shipmentExpenseTransfer->setUnitGrossPrice($price);
    }

    /**
     * @deprecated For BC reasons the missing sum prices are mirrored from unit prices. Exists for Backward Compatibility reasons only.
     *
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function sanitizeExpenseSumPrices(ExpenseTransfer $expenseTransfer): ExpenseTransfer
    {
        $expenseTransfer->setSumGrossPrice($expenseTransfer->getSumGrossPrice() ?? $expenseTransfer->getUnitGrossPrice());
        $expenseTransfer->setSumNetPrice($expenseTransfer->getSumNetPrice() ?? $expenseTransfer->getUnitNetPrice());
        $expenseTransfer->setSumPrice($expenseTransfer->getSumPrice() ?? $expenseTransfer->getUnitPrice());
        $expenseTransfer->setSumTaxAmount($expenseTransfer->getSumTaxAmount() ?? $expenseTransfer->getUnitTaxAmount());
        $expenseTransfer->setSumDiscountAmountAggregation($expenseTransfer->getSumDiscountAmountAggregation() ?? $expenseTransfer->getUnitDiscountAmountAggregation());
        $expenseTransfer->setSumPriceToPayAggregation($expenseTransfer->getSumPriceToPayAggregation() ?? $expenseTransfer->getUnitPriceToPayAggregation());

        return $expenseTransfer;
    }
}
