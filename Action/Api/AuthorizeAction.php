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
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpRedirect;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\Authorize;
use Valiton\Payum\Payone\Request\Api\ConvertGiropayErrors;

/**
 * Authorize Action
 *
 * @author     David Fuhr
 */
class AuthorizeAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @param Authorize $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $response = $this->api->authorize($model->toUnsafeArray());

        $response = ArrayObject::ensureArrayObject($response);

        if ($txid = $response->get('txid')) {
            $model['txid'] = $txid;
        }
        if ($userid = $response->get('userid')) {
            $model['userid'] = $userid;
        }

        if (Api::STATUS_ERROR === $response[Api::FIELD_STATUS]) {
            $this->gateway->execute(new ConvertGiropayErrors($model, $response));
            $previousStatus = $model[Api::FIELD_STATUS];
            $model[Api::FIELD_STATUS] = 'failed';
            $model[Api::FIELD_CUSTOMER_MESSAGE] = $response[Api::FIELD_CUSTOMER_MESSAGE];
            $model[Api::FIELD_ERROR_CODE] = $response[Api::FIELD_ERROR_CODE];
            $model[Api::FIELD_ERROR_MESSAGE] = $response[Api::FIELD_ERROR_MESSAGE];

            $this->logger->error('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "failed" (' . $response[Api::FIELD_ERROR_CODE] . ': ' . $response[Api::FIELD_ERROR_MESSAGE] . ')');

            return;
        }

        if (in_array($response[Api::FIELD_STATUS], [Api::STATUS_REDIRECT, Api::STATUS_APPROVED], true)) {
            $previousStatus = $model[Api::FIELD_STATUS];
            $model[Api::FIELD_STATUS] = 'pending';

            $this->logger->info('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "pending"');

            if (Api::STATUS_REDIRECT === $response[Api::FIELD_STATUS]) {
                throw new HttpRedirect($response['redirecturl']);
            }

            return;
        }

        throw new LogicException('Unknown status: ' . $response[Api::FIELD_STATUS]);
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess;
    }

    /**
     * @param \Payum\Core\GatewayInterface $gateway
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}
