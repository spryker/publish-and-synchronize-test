<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductList;

use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\ProductList\Dependency\Facade\ProductListToMessengerFacadeBridge;
use Spryker\Zed\ProductList\Dependency\Service\ProductListToUtilTextServiceBridge;

/**
 * @method \Spryker\Zed\ProductList\ProductListConfig getConfig()
 */
class ProductListDependencyProvider extends AbstractBundleDependencyProvider
{
    public const PROPEL_PRODUCT_CATEGORY_QUERY = 'PROPEL_PRODUCT_CATEGORY_QUERY';
    public const SERVICE_UTIL_TEXT = 'SERVICE_UTIL_TEXT';

    public const FACADE_MESSENGER = 'FACADE_MESSENGER';

    public const PLUGINS_PRODUCT_LIST_PRE_CREATE = 'PLUGINS_PRODUCT_LIST_PRE_CREATE';
    public const PLUGINS_PRODUCT_LIST_PRE_UPDATE = 'PLUGINS_PRODUCT_LIST_PRE_UPDATE';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);
        $container = $this->addProductCategoryPropelQuery($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addUtilTextService($container);
        $container = $this->addMessengerFacade($container);
        $container = $this->addProductListPreCreatePlugins($container);
        $container = $this->addProductListPreUpdatePlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductCategoryPropelQuery(Container $container): Container
    {
        $container[static::PROPEL_PRODUCT_CATEGORY_QUERY] = function (Container $container) {
            return SpyProductCategoryQuery::create();
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilTextService(Container $container): Container
    {
        $container[static::SERVICE_UTIL_TEXT] = function (Container $container) {
            return new ProductListToUtilTextServiceBridge($container->getLocator()->utilText()->service());
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMessengerFacade(Container $container): Container
    {
        $container[static::FACADE_MESSENGER] = function (Container $container) {
            return new ProductListToMessengerFacadeBridge($container->getLocator()->messenger()->facade());
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductListPreCreatePlugins(Container $container): Container
    {
        $container[static::PLUGINS_PRODUCT_LIST_PRE_CREATE] = function (Container $container) {
            return $this->getProductListPreCreatePlugins();
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductListPreUpdatePlugins(Container $container): Container
    {
        $container[static::PLUGINS_PRODUCT_LIST_PRE_UPDATE] = function (Container $container) {
            return $this->getProductListPreUpdatePlugins();
        };

        return $container;
    }

    /**
     * @return \Spryker\Zed\ProductListExtension\Dependency\Plugin\ProductListPreCreatePluginInterface[]
     */
    protected function getProductListPreCreatePlugins(): array
    {
        return [];
    }

    /**
     * @return \Spryker\Zed\ProductListExtension\Dependency\Plugin\ProductListPreUpdatePluginInterface[]
     */
    protected function getProductListPreUpdatePlugins(): array
    {
        return [];
    }
}
