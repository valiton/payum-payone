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
use Payum\Core\Request\Refund;
use Valiton\Payum\Payone\Api;

/**
 * Refund Action
 *
 * @author     David Fuhr
 */
class RefundAction extends BaseApiAwareAction
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

        $fields = $model->toUnsafeArray();
        $fields['amount'] = $model->get('amount', 0) * -1;

        $response = $this->api->refund($fields);

        if (Api::STATUS_APPROVED === $response[Api::FIELD_STATUS]) {
            $previousStatus = $model[Api::FIELD_STATUS];
            $model[Api::FIELD_STATUS] = 'refunded';

            $this->logger->info('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "' . $model[Api::FIELD_STATUS] . '"');
        }

        if (Api::STATUS_ERROR === $response[Api::FIELD_STATUS]) {
            $model[Api::FIELD_CUSTOMER_MESSAGE] = $response[Api::FIELD_CUSTOMER_MESSAGE];
            $model[Api::FIELD_ERROR_CODE] = $response[Api::FIELD_ERROR_CODE];
            $model[Api::FIELD_ERROR_MESSAGE] = $response[Api::FIELD_ERROR_MESSAGE];

            $this->logger->error('Payment ' . $model->get('reference') . ' refund failed (' . $response[Api::FIELD_ERROR_CODE] . ': ' . $response[Api::FIELD_ERROR_MESSAGE] . ')');
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
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
