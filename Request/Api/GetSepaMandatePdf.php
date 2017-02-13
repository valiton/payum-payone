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
 * Get Sepa Mandate Pdf
 *
 * @author     David Fuhr
 */
class GetSepaMandatePdf extends Generic
{
    /**
     * @return string       file content
     */
    public function getFileContents()
    {
        $model = ArrayObject::ensureArrayObject($this->getModel());

        return $model->get(Api::FIELD_FILE_CONTENTS);
    }
}
