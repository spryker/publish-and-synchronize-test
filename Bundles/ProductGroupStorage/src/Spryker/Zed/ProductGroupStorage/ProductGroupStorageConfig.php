<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductGroupStorage;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class ProductGroupStorageConfig extends AbstractBundleConfig
{
    /**
     * @deprecated Use `\Spryker\Zed\SynchronizationBehavior\SynchronizationBehaviorConfig::isSynchronizationEnabled()` instead.
     *
     * @return bool
     */
    public function isSendingToQueue(): bool
    {
        return true;
    }

    /**
     * @return string|null
     */
    public function getProductGroupSynchronizationPoolName(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getEventQueueName(): ?string
    {
        return null;
    }
}
