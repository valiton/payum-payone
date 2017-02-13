<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone\Request;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Generic as BaseGeneric;
use Valiton\Payum\Payone\Api;

/**
 * Generic
 *
 * @author     David Fuhr
 */
abstract class Generic extends BaseGeneric
{
    /**
     * @return bool
     */
    public function isApproved()
    {
        $model = ArrayObject::ensureArrayObject($this->getModel());

        return Api::STATUS_APPROVED === $model->get(Api::FIELD_MANDATE_STATUS);
    }

    /**
     * @return bool
     */
    public function isError()
    {
        $model = ArrayObject::ensureArrayObject($this->getModel());

        return Api::STATUS_ERROR === $model->get(Api::FIELD_MANDATE_STATUS);
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        $model = ArrayObject::ensureArrayObject($this->getModel());

        return Api::STATUS_REDIRECT === $model->get(Api::FIELD_MANDATE_STATUS);
    }
}
