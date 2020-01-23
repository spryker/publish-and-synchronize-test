<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SearchElasticsearchGui\Business\DocumentReader;

use Generated\Shared\Transfer\ElasticsearchSearchContextTransfer;
use Generated\Shared\Transfer\SearchContextTransfer;
use Generated\Shared\Transfer\SearchDocumentTransfer;
use Spryker\Zed\SearchElasticsearchGui\Dependency\Client\SearchElasticsearchGuiToSearchElasticsearchClientInterface;

class DocumentReader implements DocumentReaderInterface
{
    /**
     * @var \Spryker\Zed\SearchElasticsearchGui\Dependency\Client\SearchElasticsearchGuiToSearchElasticsearchClientInterface
     */
    protected $searchElasticsearchClient;

    /**
     * @param \Spryker\Zed\SearchElasticsearchGui\Dependency\Client\SearchElasticsearchGuiToSearchElasticsearchClientInterface $searchElasticsearchClient
     */
    public function __construct(SearchElasticsearchGuiToSearchElasticsearchClientInterface $searchElasticsearchClient)
    {
        $this->searchElasticsearchClient = $searchElasticsearchClient;
    }

    /**
     * @param string $documentId
     * @param string $indexName
     * @param string $typeName
     *
     * @return \Generated\Shared\Transfer\SearchDocumentTransfer
     */
    public function readDocument(string $documentId, string $indexName, string $typeName): SearchDocumentTransfer
    {
        $searchDocumentTransfer = $this->createSearchDocumentTransfer($documentId, $indexName, $typeName);

        return $this->searchElasticsearchClient->readDocument($searchDocumentTransfer);
    }

    /**
     * @param string $documentId
     * @param string $indexName
     * @param string $typeName
     *
     * @return \Generated\Shared\Transfer\SearchDocumentTransfer
     */
    protected function createSearchDocumentTransfer(string $documentId, string $indexName, string $typeName): SearchDocumentTransfer
    {
        $elasticsearchContextTransfer = $this->createElasticsearchContextTransferFromIndexName($indexName, $typeName);
        $searchContextTransfer = (new SearchContextTransfer())->setElasticsearchContext($elasticsearchContextTransfer);
        $searchDocumentTransfer = (new SearchDocumentTransfer())->setId($documentId)->setSearchContext($searchContextTransfer);

        return $searchDocumentTransfer;
    }

    /**
     * @param string $indexName
     * @param string|null $typeName
     *
     * @return \Generated\Shared\Transfer\ElasticsearchSearchContextTransfer
     */
    protected function createElasticsearchContextTransferFromIndexName(string $indexName, ?string $typeName = null): ElasticsearchSearchContextTransfer
    {
        return (new ElasticsearchSearchContextTransfer())
            ->setIndexName($indexName)
            ->setTypeName($typeName);
    }
}
