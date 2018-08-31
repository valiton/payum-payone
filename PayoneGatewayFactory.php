<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Valiton\Payum\Payone\Action;
use Valiton\Payum\Payone\Extension\InvalidateNotifyTokenExtension;

/**
 * Payone Gateway Factory
 *
 * @author     David Fuhr
 */
class PayoneGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'payone',
            'payum.factory_title' => 'Payone',

            'payum.template.get_pseudo_card_pan' => '@PayumPayone/Action/get_pseudo_card_pan.html.twig',
            'payum.template.get_sepa_mandate' => '@PayumPayone/Action/get_sepa_mandate.html.twig',

            'payum.action.authorize' => new Action\AuthorizeAction(),
            'payum.action.capture' => new Action\CaptureAction(),
            'payum.action.convert_payment' => new Action\ConvertPaymentAction(),
            'payum.action.notify' => new Action\NotifyAction(),
            'payum.action.notify_null' => new Action\NotifyNullAction(),
            'payum.action.status' => new Action\StatusAction(),

            'payum.action.api.authorize' => new Action\Api\AuthorizeAction(),
            'payum.action.api.capture' => new Action\Api\CaptureAction(),
            'payum.action.api.get_sepa_mandate' => function (ArrayObject $config) {
                return new Action\Api\GetSepaMandateAction($config['payum.template.get_sepa_mandate']);
            },
            'payum.action.api.get_sepa_mandate_pdf' => new Action\Api\GetSepaMandatePdfAction(),
            'payum.action.api.pre_authorize' => new Action\Api\PreAuthorizeAction(),
            'payum.action.get_pseudo_card_pan' => function (ArrayObject $config) {
                return new Action\Api\GetPseudoCardPanAction(
                    $config['payum.template.get_pseudo_card_pan'],
                    (array)$config
                );
            },
            'payum.action.api.refund' => new Action\Api\RefundAction(),

            'payum.action.api.convert_giropay_errors' => new Action\Api\ConvertGiropayErrorsAction(),

            'payum.extension.invalidate_notify_token' => function (ArrayObject $config) {
                return new InvalidateNotifyTokenExtension($config['payum.security.token_storage']);
            }
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'merchant_id',
                'portal_id',
                'key',
                'sub_account_id',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array)$config, $config['payum.http_client']);
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumPayone' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
