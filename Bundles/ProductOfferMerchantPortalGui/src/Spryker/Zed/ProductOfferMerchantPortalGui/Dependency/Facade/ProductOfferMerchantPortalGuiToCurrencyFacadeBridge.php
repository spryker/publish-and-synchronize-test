<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductOfferMerchantPortalGui\Dependency\Facade;

class ProductOfferMerchantPortalGuiToCurrencyFacadeBridge implements ProductOfferMerchantPortalGuiToCurrencyFacadeInterface
{
    /**
     * @var \Spryker\Zed\Currency\Business\CurrencyFacadeInterface
     */
    protected $currencyFacade;

    /**
     * @param \Spryker\Zed\Currency\Business\CurrencyFacadeInterface $currencyFacade
     */
    public function __construct($currencyFacade)
    {
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return \Generated\Shared\Transfer\StoreWithCurrencyTransfer[]
     */
    public function getAllStoresWithCurrencies()
    {
        return $this->currencyFacade->getAllStoresWithCurrencies();
    }
}
