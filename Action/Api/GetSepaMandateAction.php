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
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\GetSepaMandate;

/**
 * Get Sepa Mandate Action
 *
 * @author     David Fuhr
 */
class GetSepaMandateAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    /**
     * @var GatewayInterface
     */
    private $gateway;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        parent::__construct();

        $this->templateName = $templateName;
    }

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

        try {
            $model->validateNotEmpty([
                Api::FIELD_CITY,
                Api::FIELD_COUNTRY,
                Api::FIELD_CURRENCY,
                Api::FIELD_IBAN,
                Api::FIELD_LAST_NAME,
            ]);
        }
        catch (LogicException $e) {
            $model[Api::FIELD_STATUS] = 'failed';
            $model[Api::FIELD_ERROR_MESSAGE] = $e->getMessage();

            $this->logger->error('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "failed" (' . $model[Api::FIELD_ERROR_MESSAGE] . ')');

            return;
        }

        // sepa mandate has to be fetched again after two hours
        if ($model->get(Api::FIELD_MANDATE_DATE, 0) > time() - 2 * 60 * 60) {
            // we already have a fresh sepa mandate
            $this->logger->info('Payment ' . $model->get('reference') . ' already has a fresh sepa mandate.');

            return;
        }

        // process form submission if present
        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if ('POST' === $httpRequest->method) {
            $postParams = [];
            parse_str($httpRequest->content, $postParams);
            if (array_key_exists(Api::FIELD_MANDATE_IDENTIFICATION, $postParams) && null !== $postParams[Api::FIELD_MANDATE_IDENTIFICATION] && $postParams[Api::FIELD_MANDATE_IDENTIFICATION] === $model[Api::FIELD_MANDATE_IDENTIFICATION]) {
                $model[Api::FIELD_MANDATE_DATE] = time();

                $this->logger->info('Payment ' . $model->get('reference') . ' sepa mandate ' . $model[Api::FIELD_MANDATE_IDENTIFICATION] . ' confirmed by user.');

                return;
            }
        }

        $response = $this->api->manageMandate($model->toUnsafeArray());

        $response = ArrayObject::ensureArrayObject($response);

        if (Api::STATUS_ERROR === $response[Api::FIELD_STATUS]) {
            $model[Api::FIELD_STATUS] = 'failed';
            $model[Api::FIELD_CUSTOMER_MESSAGE] = $response[Api::FIELD_CUSTOMER_MESSAGE];
            $model[Api::FIELD_ERROR_CODE] = $response[Api::FIELD_ERROR_CODE];
            $model[Api::FIELD_ERROR_MESSAGE] = $response[Api::FIELD_ERROR_MESSAGE];

            $this->logger->error('Payment ' . $model->get('reference') . ' changed status from "' . $previousStatus . '" to "failed" (' . $response[Api::FIELD_ERROR_CODE] . ': ' . $response[Api::FIELD_ERROR_MESSAGE] . ')');

            return;
        }

        if (Api::STATUS_APPROVED === $response[Api::FIELD_STATUS]) {
            $model[Api::FIELD_MANDATE_IDENTIFICATION] = $response[Api::FIELD_MANDATE_IDENTIFICATION];
            $model[Api::FIELD_MANDATE_STATUS] = $response[Api::FIELD_MANDATE_STATUS];
            $model[Api::FIELD_MANDATE_TEXT] = $response[Api::FIELD_MANDATE_TEXT];
            $model[Api::FIELD_CREDITOR_IDENTIFIER] = $response[Api::FIELD_CREDITOR_IDENTIFIER];
            $model[Api::FIELD_IBAN] = $response[Api::FIELD_IBAN];
            $model[Api::FIELD_BIC] = $response[Api::FIELD_BIC];
            
            if (Api::MANDATE_STATUS_ACTIVE === $model[Api::FIELD_MANDATE_STATUS]) {
                // mandate is active, so we need no further user confirmation
                $model[Api::FIELD_MANDATE_DATE] = time();

                $this->logger->info('Payment ' . $model->get('reference') . ' successfully retrieved an active sepa mandate.');

                return;
            }

            // mandate is pending (newly created) so we need to user confirmation
            $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, [
                'model' => $model,
                'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
                'backUrl' => $model['backurl'],
            ]));

            $this->logger->debug('Payment ' . $model->get('reference') . ' sepa mandate ' .  $model[Api::FIELD_MANDATE_IDENTIFICATION] . ' is pending. Asking user for confirmation.');

            throw new HttpResponse($renderTemplate->getResult());
        }

        throw new LogicException('Unexpected response status ' . $response[Api::FIELD_STATUS]);
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof GetSepaMandate &&
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
