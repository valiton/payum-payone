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
use Valiton\Payum\Payone\Request\Api\Capture;

/**
 * Capture Action
 *
 * @author     David Fuhr
 */
class CaptureAction extends BaseApiAwareAction
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
        $previousStatus = $model[Api::FIELD_STATUS];

        $response = $this->api->capture($model->toUnsafeArray());

        $response = ArrayObject::ensureArrayObject($response);

        if (Api::STATUS_APPROVED === $response->get(Api::FIELD_STATUS)) {
            $model[Api::FIELD_STATUS] = 'captured';

            $this->logger->info('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "' . $model[Api::FIELD_STATUS] . '"');

            return;
        }

        if (Api::STATUS_ERROR === $response[Api::FIELD_STATUS]) {
            if (array_key_exists(Api::FIELD_PAYMENT_METHOD, $model)) {
                if ($this->isGiropayError1087($model, $response)) {
                    $response[Api::FIELD_ERROR_CODE]       = 885;
                    $response[Api::FIELD_ERROR_MESSAGE]    = 'Bank is not supported by giropay';
                    $response[Api::FIELD_CUSTOMER_MESSAGE] = 'Bank is not supported by giropay';
                }
            }

            $model[Api::FIELD_CUSTOMER_MESSAGE] = $response[Api::FIELD_CUSTOMER_MESSAGE];
            $model[Api::FIELD_ERROR_CODE] = $response[Api::FIELD_ERROR_CODE];
            $model[Api::FIELD_ERROR_MESSAGE] = $response[Api::FIELD_ERROR_MESSAGE];

            $this->logger->error('Payment ' . $model->get('reference') . ' capture failed (' . $response[Api::FIELD_ERROR_CODE] . ': ' . $response[Api::FIELD_ERROR_MESSAGE] . ')');
        }
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    /**
     * @param $model
     * @param $response
     *
     * @return bool
     */
    protected function isGiropayError1087($model, $response)
    {
        return $model[Api::FIELD_PAYMENT_METHOD] == Api::PAYMENT_METHOD_GIROPAY
               && (int)$response[Api::FIELD_ERROR_CODE] == 1087;
    }
}
