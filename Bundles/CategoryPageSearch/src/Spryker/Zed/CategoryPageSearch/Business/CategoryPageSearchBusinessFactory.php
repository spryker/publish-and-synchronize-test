<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CategoryPageSearch\Business;

use Spryker\Zed\CategoryPageSearch\Business\Search\CategoryNodePageSearch;
use Spryker\Zed\CategoryPageSearch\Business\Search\CategoryNodePageSearchInterface;
use Spryker\Zed\CategoryPageSearch\Business\Search\DataMapper\CategoryNodePageSearchDataMapper;
use Spryker\Zed\CategoryPageSearch\Business\Search\DataMapper\CategoryNodePageSearchDataMapperInterface;
use Spryker\Zed\CategoryPageSearch\CategoryPageSearchDependencyProvider;
use Spryker\Zed\CategoryPageSearch\Dependency\Facade\CategoryPageSearchToCategoryFacadeInterface;
use Spryker\Zed\CategoryPageSearch\Dependency\Facade\CategoryPageSearchToEventBehaviorFacadeInterface;
use Spryker\Zed\CategoryPageSearch\Dependency\Facade\CategoryPageSearchToStoreFacadeInterface;
use Spryker\Zed\CategoryPageSearch\Dependency\Service\CategoryPageSearchToUtilEncodingInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\CategoryPageSearch\CategoryPageSearchConfig getConfig()
 * @method \Spryker\Zed\CategoryPageSearch\Persistence\CategoryPageSearchQueryContainerInterface getQueryContainer()
 */
class CategoryPageSearchBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\CategoryPageSearch\Business\Search\CategoryNodePageSearchInterface
     */
    public function createCategoryNodeSearch(): CategoryNodePageSearchInterface
    {
        return new CategoryNodePageSearch(
            $this->getUtilEncodingService(),
            $this->createCategoryNodePageSearchDataMapper(),
            $this->getQueryContainer(),
            $this->getStoreFacade(),
            $this->getCategoryFacade(),
            $this->getEventBehaviorFacade()
        );
    }

    /**
     * @return \Spryker\Zed\CategoryPageSearch\Business\Search\DataMapper\CategoryNodePageSearchDataMapperInterface
     */
    public function createCategoryNodePageSearchDataMapper(): CategoryNodePageSearchDataMapperInterface
    {
        return new CategoryNodePageSearchDataMapper();
    }

    /**
     * @return \Spryker\Zed\CategoryPageSearch\Dependency\Facade\CategoryPageSearchToStoreFacadeInterface
     */
    public function getStoreFacade(): CategoryPageSearchToStoreFacadeInterface
    {
        return $this->getProvidedDependency(CategoryPageSearchDependencyProvider::FACADE_STORE);
    }

    /**
     * @return \Spryker\Zed\CategoryPageSearch\Dependency\Facade\CategoryPageSearchToCategoryFacadeInterface
     */
    public function getCategoryFacade(): CategoryPageSearchToCategoryFacadeInterface
    {
        return $this->getProvidedDependency(CategoryPageSearchDependencyProvider::FACADE_CATEGORY);
    }

    /**
     * @return \Spryker\Zed\CategoryPageSearch\Dependency\Facade\CategoryPageSearchToEventBehaviorFacadeInterface
     */
    public function getEventBehaviorFacade(): CategoryPageSearchToEventBehaviorFacadeInterface
    {
        return $this->getProvidedDependency(CategoryPageSearchDependencyProvider::FACADE_EVENT_BEHAVIOR);
    }

    /**
     * @return \Spryker\Zed\CategoryPageSearch\Dependency\Service\CategoryPageSearchToUtilEncodingInterface
     */
    public function getUtilEncodingService(): CategoryPageSearchToUtilEncodingInterface
    {
        return $this->getProvidedDependency(CategoryPageSearchDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
