<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Business;

use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\QuoteApproveRequestTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\QuoteApproval\Business\QuoteApprovalBusinessFactory getFactory()
 * @method \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalRepositoryInterface getRepository()
 * @method \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface getEntityManager()
 */
class QuoteApprovalFacade extends AbstractFacade implements QuoteApprovalFacadeInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteApproveRequestTransfer $quoteApproveRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function sendQuoteApproveRequest(QuoteApproveRequestTransfer $quoteApproveRequestTransfer): QuoteResponseTransfer
    {
        return $this->getFactory()->createQuoteApprovalRequestSender()->sendQuoteApproveRequest($quoteApproveRequestTransfer);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CompanyUserCollectionTransfer
     */
    public function getPotentialQuoteApproversList(QuoteTransfer $quoteTransfer): CompanyUserCollectionTransfer
    {
        return $this->getFactory()->createPotentialQuoteApproversProvider()->getApproversList($quoteTransfer);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function updateApprovals(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()->createQuoteApprovalWriter()->updateApprovals($quoteTransfer);
    }
}
