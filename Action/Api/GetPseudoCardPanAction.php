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
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\GetPseudoCardPan;

/**
 * Get Pseudo Card Pan Action
 *
 * @author     David Fuhr
 */
class GetPseudoCardPanAction extends BaseApiAwareAction implements GatewayAwareInterface
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
     * @var ArrayObject
     */
    private $options;

    /**
     * @param string $templateName
     */
    public function __construct($templateName, array $options)
    {
        parent::__construct();

        $this->templateName = $templateName;
        $this->options = ArrayObject::ensureArrayObject($options);
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

        $model->validateNotEmpty([
            Api::FIELD_LANGUAGE,
        ]);

        if (null !== $model->get(Api::FIELD_PSEUDO_CARD_PAN, null)) {
            // we already have a pseudo card pan
            $this->logger->info('Payment ' . $model->get('reference') . ' already has pseudo card pan ' . $model[Api::FIELD_PSEUDO_CARD_PAN] . '.');

            return;
        }

        // process form submission if present
        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if ('POST' === $httpRequest->method) {
            $postParams = [];
            parse_str($httpRequest->content, $postParams);
            if (array_key_exists(Api::FIELD_PSEUDO_CARD_PAN, $postParams) && array_key_exists(Api::FIELD_TRUNCATED_CARD_PAN, $postParams)) {
                $model[Api::FIELD_PSEUDO_CARD_PAN] = $postParams[Api::FIELD_PSEUDO_CARD_PAN];
                $model[Api::FIELD_TRUNCATED_CARD_PAN] = $postParams[Api::FIELD_TRUNCATED_CARD_PAN];

                $this->logger->info('Payment ' . $model->get('reference') . ' received pseudo card pan ' . $model[Api::FIELD_PSEUDO_CARD_PAN] . ' for ' . $model[Api::FIELD_TRUNCATED_CARD_PAN] . '.');

                return;
            }
        }

        $language = strtolower($model[Api::FIELD_LANGUAGE]);
        if (1 !== preg_match('/^[a-z]{2}$/', $language)) {
            $language = 'en';
        }
        
        $params = [
            'aid' => $this->options['sub_account_id'],
            'encoding' => 'UTF-8',
            'mid' => $this->options['merchant_id'],
            'mode' => $this->options['sandbox'] ? 'test' : 'live',
            'portalid' => $this->options['portal_id'],
            'request' => 'creditcardcheck',
            'responsetype' => 'JSON',
            'storecarddata' => 'yes',
        ];
        ksort($params);
        $hash = hash('md5', implode('', $params) . $this->options['key']);

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, [
            'params' => $params,
            'hash' => $hash,
            'language' => $language,
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof GetPseudoCardPan &&
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
