<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Valiton\Payum\Payone\Api;

/**
 * Notify Action
 *
 * @author     David Fuhr
 */
class NotifyAction extends GatewayAwareAction implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $previousStatus = $model[Api::FIELD_STATUS];

        if (in_array($model[Api::FIELD_STATUS], ['canceled', 'failed', 'refunded'], true)) {
            $this->logger->info('Payment ' . $model->get('reference') . ' received notify. But no transition allowed from status "' . $previousStatus . '"');

            throw new HttpResponse('TSOK', 200, ['Content-Type' => 'text/plain']);
        }

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $postParams = [];
        parse_str($httpRequest->content, $postParams);

        if (false === array_key_exists('txaction', $postParams)) {
            $this->logger->error('Payment ' . $model->get('reference') . ' received notify. But txaction is missing.');

            throw new HttpResponse('Parameter "txaction" is missing."', 400, ['Content-Type' => 'text/plain']);
        }

        if ('cancelation' === $postParams['txaction']) {
            $model[Api::FIELD_STATUS] = 'canceled';
            $model[Api::FIELD_TX_ACTION] = $postParams['txaction'];

            $this->logger->info('Payment ' . $model->get('reference') . ' received cancelation notify. Changed status from "' . $previousStatus . '" to "' . $model[Api::FIELD_STATUS] . '"');

            throw new HttpResponse('TSOK', 200, ['Content-Type' => 'text/plain']);
        }

        $transactionStatus = null;
        if (array_key_exists(Api::FIELD_TRANSACTION_STATUS, $postParams)) {
            $transactionStatus = $postParams[Api::FIELD_TRANSACTION_STATUS];
        }
        if ((null === $transactionStatus || Api::TRANSACTION_STATUS_COMPLETED === $transactionStatus) && 'appointed' === $postParams['txaction']) {
            $model[Api::FIELD_STATUS] = $model['completed_status'];
            $model[Api::FIELD_TRANSACTION_STATUS] = $transactionStatus;
            $model[Api::FIELD_TX_ACTION] = $postParams['txaction'];

            $this->logger->info('Payment ' . $model->get('reference') . ' received transaction_status notify. Changed status from "' . $previousStatus . '" to "' . $model[Api::FIELD_STATUS] . '"');

            throw new HttpResponse('TSOK', 200, ['Content-Type' => 'text/plain']);
        }

        if (in_array($postParams['txaction'], ['capture', 'paid'], true)) {
            $model[Api::FIELD_STATUS] = $model['completed_status'];
            $model[Api::FIELD_TX_ACTION] = $postParams['txaction'];

            $this->logger->info('Payment ' . $model->get('reference') . ' received txaction notify. Changed status from "' . $previousStatus . '" to "' . $model[Api::FIELD_STATUS] . '"');

            throw new HttpResponse('TSOK', 200, ['Content-Type' => 'text/plain']);
        }

        throw new LogicException('Unsupported txaction: ' . $postParams['txaction']);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
