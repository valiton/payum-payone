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
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Notify Null Action
 *
 * @author     David Fuhr
 */
class NotifyNullAction extends GatewayAwareAction implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $postParams = [];
        parse_str($httpRequest->content, $postParams);

        $this->logger->info('Incoming notify: ' . implode(' ', [
                $httpRequest->method,
                $httpRequest->uri,
                $httpRequest->userAgent,
                $httpRequest->content,
            ]));

        if (empty($postParams['param'])) {
            $this->logger->error('Received notify. But token parameter "param" is missing or empty');

            throw new HttpResponse('Token parameter "param" is missing or empty', 400, ['Content-Type' => 'text/plain']);
        }

        try {
            $this->gateway->execute($getToken = new GetToken($postParams['param']));
            $this->gateway->execute(new Notify($getToken->getToken()));
        } catch (Base $e) {
            throw $e;
        } catch (LogicException $e) {
            $this->logger->error($e->getMessage());

            throw new HttpResponse($e->getMessage(), 400, ['Content-Type' => 'text/plain']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            null === $request->getModel();
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
