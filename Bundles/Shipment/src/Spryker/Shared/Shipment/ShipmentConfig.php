<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Shipment;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class ShipmentConfig extends AbstractSharedConfig
{
    public const SHIPMENT_METHOD_NAME_NO_SHIPMENT = 'NoShipment';

    protected const SHIPMENT_EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';

    /**
     * @return string
     */
    public function getShipmentExpenseType(): string
    {
        return static::SHIPMENT_EXPENSE_TYPE;
    }
}
