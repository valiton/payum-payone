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

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\GetSepaMandatePdf;

/**
 * Get Sepa Mandate Pdf Action
 *
 * @author     David Fuhr
 */
class GetSepaMandatePdfAction extends BaseApiAwareAction
{
    /**
     * @param mixed $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            Api::FIELD_FILE_REFERENCE
        ]);

        $model[Api::FIELD_FILE_TYPE] = 'SEPA_MANDATE';
        $model[Api::FIELD_FILE_FORMAT] = 'PDF';
        $model[Api::FIELD_FILE_CONTENTS] = $this->api->getFile($model->toUnsafeArray());
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof GetSepaMandatePdf &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
