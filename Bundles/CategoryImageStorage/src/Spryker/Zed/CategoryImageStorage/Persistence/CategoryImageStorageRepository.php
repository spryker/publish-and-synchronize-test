<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CategoryImageStorage\Persistence;

use Orm\Zed\CategoryImage\Persistence\Map\SpyCategoryImageSetTableMap;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\CategoryImageStorage\Persistence\CategoryImageStoragePersistenceFactory getFactory()
 */
class CategoryImageStorageRepository extends AbstractRepository implements CategoryImageStorageRepositoryInterface
{
    public const FK_CATEGORY = 'fkCategory';

    /**
     * {@inheritdoc}
     */
    public function findCategoryIdsByCategoryImageSetToCategoryImageIds(array $categoryImageSetToCategoryImageIds): array
    {
        return $this->getFactory()
            ->createQueryCategoryImageSetToCategoryImage()
            ->filterByIdCategoryImageSetToCategoryImage_In($categoryImageSetToCategoryImageIds)
            ->innerJoinSpyCategoryImageSet()
            ->withColumn('DISTINCT ' . SpyCategoryImageSetTableMap::COL_FK_CATEGORY, static::FK_CATEGORY)
            ->select([static::FK_CATEGORY])
            ->addAnd(SpyCategoryImageSetTableMap::COL_FK_CATEGORY, null, ModelCriteria::NOT_EQUAL)
            ->find();
    }

    /**
     * {@inheritdoc}
     */
    public function findCategoryImageSetsByFkCategoryIn(array $categoryIds)
    {
        $categoryImageSetsQuery = $this->getFactory()
            ->createCategoryImageSetQuery()
            ->innerJoinWithSpyLocale()
            ->innerJoinWithSpyCategoryImageSetToCategoryImage()
            ->useSpyCategoryImageSetToCategoryImageQuery()
            ->innerJoinWithSpyCategoryImage()
            ->endUse()
            ->filterByFkCategory_In($categoryIds);

        return $this->buildQueryFromCriteria($categoryImageSetsQuery)->find();
    }

    /**
     * {@inheritdoc}
     */
    public function findCategoryImageStorageByIds(array $categoryIds): array
    {
        $categoryImageStorageQuery = $this->getFactory()
            ->createSpyCategoryImageStorageQuery()
            ->filterByFkCategory_In($categoryIds);

        return $this->buildQueryFromCriteria($categoryImageStorageQuery)->find();
    }

    /**
     * {@inheritdoc}
     */
    public function findCategoryIdsByCategoryImageIds(array $categoryImageIds): array
    {
        return $this->getFactory()
            ->createQueryCategoryImageSetToCategoryImage()
            ->filterByFkCategoryImage_In($categoryImageIds)
            ->innerJoinSpyCategoryImageSet()
            ->withColumn('DISTINCT ' . SpyCategoryImageSetTableMap::COL_FK_CATEGORY, static::FK_CATEGORY)
            ->select([static::FK_CATEGORY])
            ->addAnd(SpyCategoryImageSetTableMap::COL_FK_CATEGORY, null, ModelCriteria::NOT_EQUAL)
            ->find();
    }
}
