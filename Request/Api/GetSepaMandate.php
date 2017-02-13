<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone\Request\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Generic;

/**
 * Get Sepa Mandate
 *
 * @author     David Fuhr
 */
class GetSepaMandate extends Generic
{
    /**
     * @return string
     */
    public function getMandateIdentification()
    {
        $model = ArrayObject::ensureArrayObject($this->getModel());

        return $model->get(Api::FIELD_MANDATE_IDENTIFICATION);
    }

    /**
     * @return string
     */
    public function getMandateText()
    {
        $model = ArrayObject::ensureArrayObject($this->getModel());

        return $model->get(Api::FIELD_MANDATE_TEXT);
    }
}
