<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantStock\Business\Writer;

use Generated\Shared\Transfer\MerchantErrorTransfer;
use Generated\Shared\Transfer\MerchantResponseTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\StockTransfer;
use Spryker\Zed\MerchantStock\Dependency\Facade\MerchantStockToStockFacadeInterface;
use Spryker\Zed\MerchantStock\Persistence\MerchantStockEntityManagerInterface;

class MerchantStockWriter implements MerchantStockWriterInterface
{
    protected const ERROR_MERCHANT_STOCK_CREATE = 'Merchant stock can not be created.';

    /**
     * @var \Spryker\Zed\MerchantStock\Dependency\Facade\MerchantStockToStockFacadeInterface
     */
    protected $stockFacade;

    /**
     * @var \Spryker\Zed\MerchantStock\Persistence\MerchantStockEntityManagerInterface
     */
    protected $merchantStockEntityManager;

    /**
     * @param \Spryker\Zed\MerchantStock\Dependency\Facade\MerchantStockToStockFacadeInterface $stockFacade
     * @param \Spryker\Zed\MerchantStock\Persistence\MerchantStockEntityManagerInterface $merchantStockEntityManager
     */
    public function __construct(
        MerchantStockToStockFacadeInterface $stockFacade,
        MerchantStockEntityManagerInterface $merchantStockEntityManager
    ) {
        $this->stockFacade = $stockFacade;
        $this->merchantStockEntityManager = $merchantStockEntityManager;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantResponseTransfer
     */
    public function createDefaultMerchantStock(MerchantTransfer $merchantTransfer): MerchantResponseTransfer
    {
        $stockTransfer = (new StockTransfer())
            ->setName($this->generateStockNameByMerchant($merchantTransfer))
            ->setIsActive(true);

        $stockTransfer = $this->stockFacade->createStock($stockTransfer)->getStock();
        $merchantStockTransfer = $this->merchantStockEntityManager->createMerchantStock($merchantTransfer, $stockTransfer);

        if (!$merchantStockTransfer->getIdMerchantStock()) {
            return $this->createMerchantResponseTransfer($merchantTransfer, static::ERROR_MERCHANT_STOCK_CREATE);
        }

        $merchantTransfer->addStock($stockTransfer);

        return $this->createMerchantResponseTransfer($merchantTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return string
     */
    protected function generateStockNameByMerchant(MerchantTransfer $merchantTransfer): string
    {
        return sprintf(
            '%s %s %s %d',
            $merchantTransfer->requireName()->getName(),
            $merchantTransfer->requireMerchantReference()->getMerchantReference(),
            'Warehouse',
            $merchantTransfer->getStocks()->count() + 1
        );
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     * @param string $errorMessage
     *
     * @return \Generated\Shared\Transfer\MerchantResponseTransfer
     */
    protected function createMerchantResponseTransfer(
        MerchantTransfer $merchantTransfer,
        string $errorMessage = ''
    ): MerchantResponseTransfer {
        $merchantResponseTransfer = (new MerchantResponseTransfer())
            ->setIsSuccess(true)
            ->setMerchant($merchantTransfer);

        if (!$errorMessage) {
            return $merchantResponseTransfer;
        }

        $merchantErrorTransfer = (new MerchantErrorTransfer())->setMessage($errorMessage);

        return $merchantResponseTransfer->setIsSuccess(false)->addError($merchantErrorTransfer);
    }
}
