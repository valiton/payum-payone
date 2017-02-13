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

use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Valiton\Payum\Payone\Request\Api\PreAuthorize;

/**
 * Authorize Action
 *
 * @author     David Fuhr
 */
class AuthorizeAction extends AbstractPurchaseAction
{
    /**
     * {@inheritDoc}
     */
    protected function preExecute($request)
    {
        $model = parent::preExecute($request);
        $payment = $request->getFirstModel();

        if ($payment instanceof PaymentInterface) {
            $this->gateway->execute($status = new GetHumanStatus($payment));
            if ($status->isNew()) {
                $this->gateway->execute($convert = new Convert($payment, 'array', $request->getToken()));

                $model->replace($convert->getResult());
            }
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess;
    }

    /**
     * {@inheritDoc}
     */
    protected function createApiRequest($model)
    {
        return new PreAuthorize($model);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCompletedStatus()
    {
        return 'authorized';
    }
}
