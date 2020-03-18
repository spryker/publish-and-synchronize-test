<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUserGui\Communication\Controller;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\MerchantUserGui\Communication\MerchantUserGuiCommunicationFactory getFactory()
 */
class EditMerchantUserController extends AbstractController
{
    protected const MERCHANT_ID_PARAM_NAME = 'merchant-id';
    protected const MERCHANT_USER_ID_PARAM_NAME = 'merchant-user-id';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $idMerchant = $this->castId($request->get(static::MERCHANT_ID_PARAM_NAME));
        $idMerchantUser = $this->castId($request->get(static::MERCHANT_USER_ID_PARAM_NAME));

        $dataProvider = $this->getFactory()->createMerchantUserUpdateFormDataProvider();
        $providerData = $dataProvider->getData($idMerchant, $idMerchantUser);

        $merchantUserUpdateForm = $this->getFactory()
            ->getMerchantUserUpdateForm($providerData, $dataProvider->getOptions())
            ->handleRequest($request);

        if ($merchantUserUpdateForm->isSubmitted() && $merchantUserUpdateForm->isValid()) {
            return $this->updateMerchant($request, $merchantUserUpdateForm);
        }

        return $this->viewResponse([
            'merchantUserForm' => $merchantUserUpdateForm->createView(),
            'idMerchant' => $idMerchant,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormInterface $merchantUserUpdateForm
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function updateMerchant(Request $request, FormInterface $merchantUserUpdateForm)
    {
        $idMerchant = $this->castId($request->get(static::MERCHANT_ID_PARAM_NAME));

        $redirectUrl = sprintf(
            '/merchant-gui/edit-merchant?id-merchant=%s%s',
            $idMerchant,
            '#tab-content-merchant-user'
        );

        $merchantUser = (new MerchantUserTransfer())->fromArray($merchantUserUpdateForm->getData(), true);
        $user = (new UserTransfer())->fromArray($merchantUserUpdateForm->getData(), true);
        $merchantUser->setUser($user);

        $merchantUserResponseTransfer = $this->getFactory()
            ->getMerchantUserFacade()
            ->update($merchantUser);

        if ($merchantUserResponseTransfer->getIsSuccessful()) {
            $this->addSuccessMessage('Merchant user was successfully updated');

            return $this->redirectResponse($redirectUrl);
        }

        foreach ($merchantUserResponseTransfer->getErrors() as $merchantUserErrorTransfer) {
            $this->addErrorMessage($merchantUserErrorTransfer->getMessage());
        }

        return $this->redirectResponse($redirectUrl);
    }
}
