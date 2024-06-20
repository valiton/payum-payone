<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\ConvertGiropayErrors;

/**
 * ConvertGiropayErrorsAction.
 */
class ConvertGiropayErrorsAction implements ActionInterface
{
    /**
     * Rewrites non speaking error messages of the case, that the bank is not supported.
     *
     * @param ConvertGiropayErrors $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model    = ArrayObject::ensureArrayObject($request->getModel());
        $response = $request->getResponse();

        if (Api::STATUS_ERROR != $response[Api::FIELD_STATUS]
            || !$model[Api::FIELD_PAYMENT_METHOD]
            || $model[Api::FIELD_PAYMENT_METHOD] != Api::PAYMENT_METHOD_GIROPAY
        ) {
            return;
        }

        if ($this->isNonSpeakingErrorMessage($model, $response)) {
            $response[Api::FIELD_ERROR_CODE]       = '885';
            $response[Api::FIELD_ERROR_MESSAGE]    = 'Bank is not supported by giropay';
            $response[Api::FIELD_CUSTOMER_MESSAGE] = 'Bank is not supported by giropay';
        }
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return $request instanceof ConvertGiropayErrors
               && $request->getModel() instanceof \ArrayAccess
               && $request->getResponse() instanceof \ArrayAccess;
    }

    /**
     * @param $model
     * @param $response
     *
     * @return bool
     */
    protected function isNonSpeakingErrorMessage($model, $response)
    {
        $errorCode = (int)$response[Api::FIELD_ERROR_CODE];

        return $errorCode == 887 || $errorCode == 1087;
    }
}
