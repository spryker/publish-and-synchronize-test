<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CompanyBusinessUnitGui\Communication\Controller;

use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Shared\CompanyBusinessUnitGui\CompanyBusinessUnitGuiConstants;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\CompanyBusinessUnitGui\Communication\CompanyBusinessUnitGuiCommunicationFactory getFactory()
 */
class DeleteCompanyBusinessUnitController extends AbstractController
{
    protected const MESSAGE_COMPANY_BUSINESS_UNIT_DELETE_SUCCESS = 'Company Business Unit "%s" was deleted.';
    protected const MESSAGE_COMPANY_BUSINESS_UNIT_DELETE_ERROR = 'You can not delete a business unit while it contains users';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request): RedirectResponse
    {
        $redirectUrl = Url::generate(CompanyBusinessUnitGuiConstants::REDIRECT_URL_DEFAULT)->build();

        $idCompanyBusinessUnit = $this->castId(
            $request->query->get(CompanyBusinessUnitGuiConstants::REQUEST_ID_COMPANY_BUSINESS_UNIT)
        );

        $companyBusinessUnitTransfer = new CompanyBusinessUnitTransfer();
        $companyBusinessUnitTransfer->setIdCompanyBusinessUnit($idCompanyBusinessUnit);

        $companyBusinessUnit = $this
            ->getFactory()
            ->getCompanyBusinessUnitFacade()
            ->getCompanyBusinessUnitById($companyBusinessUnitTransfer);

        $companyBusinessUnitResponseTransfer = $this
            ->getFactory()
            ->getCompanyBusinessUnitFacade()
            ->delete($companyBusinessUnit);

        if ($companyBusinessUnitResponseTransfer->getIsSuccessful()) {
            $this->addSuccessMessage(sprintf(
                static::MESSAGE_COMPANY_BUSINESS_UNIT_DELETE_SUCCESS,
                $companyBusinessUnit->getName()
            ));

            return $this->redirectResponse($redirectUrl);
        }

        $this->addErrorMessage(sprintf(
            static::MESSAGE_COMPANY_BUSINESS_UNIT_DELETE_ERROR,
            $companyBusinessUnit->getName()
        ));

        return $this->redirectResponse($redirectUrl);
    }
}
