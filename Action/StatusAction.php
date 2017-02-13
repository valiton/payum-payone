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

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Valiton\Payum\Payone\Api;


/**
 * Status Action
 *
 * @author     David Fuhr
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model[Api::FIELD_STATUS])) {
            $request->markNew();

            return;
        }

        if ($status = $model->get(Api::FIELD_STATUS)) {
            $markMethod = 'mark' . ucfirst($status);
            if (is_callable([$request, $markMethod])) {
                $request->$markMethod();

                return;
            }
        }

        throw new \LogicException('Status ' . $model[Api::FIELD_STATUS] . ' is not supported.');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
