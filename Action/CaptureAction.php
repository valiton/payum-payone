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

use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Valiton\Payum\Payone\Request\Api\Authorize;

/**
 * Capture Action
 *
 * @author     David Fuhr
 */
class CaptureAction extends AbstractPurchaseAction
{
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }

    /**
     * @param $model
     *
     * @return Generic
     */
    protected function createApiRequest($model)
    {
        if ('authorized' === $model->get('status')) {
            return new \Valiton\Payum\Payone\Request\Api\Capture($model);
        }

        return new Authorize($model);
    }

    /**
     * @return string
     */
    protected function getCompletedStatus()
    {
        return 'captured';
    }
}
