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
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\GetPseudoCardPan;
use Valiton\Payum\Payone\Request\Api\GetSepaMandate;

/**
 * Abstract Purchase Action
 *
 * @author     David Fuhr
 */
abstract class AbstractPurchaseAction extends GatewayAwareAction implements GenericTokenFactoryAwareInterface, LoggerAwareInterface
{
    /**
     * @var GenericTokenFactoryInterface $tokenFactory
     */
    protected $tokenFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     *
     * @return void
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /**
     * @param Authorize|Capture $request
     *
     * @return ArrayObject
     */
    protected function preExecute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        return ArrayObject::ensureArrayObject($request->getModel());
    }

    /**
     * {@inheritDoc}
     *
     * @param Authorize|Capture $request
     */
    public final function execute($request)
    {
        $model = $this->preExecute($request);

        if ($this->getCompletedStatus() === $model[Api::FIELD_STATUS]) {
            return;
        }

        $model['completed_status'] = $this->getCompletedStatus();

        $model['successurl'] = $request->getToken()->getAfterUrl();
        $model['errorurl'] = $request->getToken()->getTargetUrl() . '?canceled=1';
        $model['backurl'] = $request->getToken()->getTargetUrl() . '?canceled=1';

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if (isset($httpRequest->query['canceled'])) {
            $previousStatus = $model[Api::FIELD_STATUS];
            $model[Api::FIELD_STATUS] = 'canceled';

            $this->logger->notice('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "canceled"');

            return;
        }

        if (in_array($model[Api::FIELD_STATUS], ['canceled', 'failed', 'refunded'], true)) {
            return;
        }

        if (Api::PAYMENT_METHOD_DIRECT_DEBIT_SEPA === $model[Api::FIELD_PAYMENT_METHOD]) {
            $this->gateway->execute($mandate = new GetSepaMandate($model));
        }
        if (Api::PAYMENT_METHOD_CREDIT_CARD_PPAN === $model[Api::FIELD_PAYMENT_METHOD]) {
            $this->gateway->execute($ppan = new GetPseudoCardPan($model));
        }

        if (in_array($model[Api::FIELD_STATUS], ['failed'], true)) {
            return;
        }

        if (false === $model->get('param', false) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $model['param'] = $notifyToken->getHash();
        }

        if ('pending' !== $model[Api::FIELD_STATUS]) {
            $this->gateway->execute($this->createApiRequest($model));
        }

        if (in_array($model[Api::FIELD_STATUS], ['authorized', 'captured', 'failed'], true)) {
            return;
        }

        // if notification is needed the payment will be completed in the NotifyAction
    }

    /**
     * @param string $paymentMethod
     *
     * @return mixed
     */
    protected function needsNotify($paymentMethod)
    {
        return in_array($paymentMethod, [
            Api::PAYMENT_METHOD_CREDIT_CARD_PPAN,
            Api::PAYMENT_METHOD_GIROPAY,
            Api::PAYMENT_METHOD_PAYDIREKT
        ], true);
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $model
     *
     * @return Generic
     */
    abstract protected function createApiRequest($model);

    /**
     * @return string
     */
    abstract protected function getCompletedStatus();
}
