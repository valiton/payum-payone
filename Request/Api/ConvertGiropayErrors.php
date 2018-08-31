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
use Payum\Core\Request\Generic;

/**
 * ConvertGiropayErrors.
 */
class ConvertGiropayErrors extends Generic
{
    /**
     * @var ArrayObject
     */
    protected $response;

    /**
     * ConvertGiropayErrors constructor.
     *
     * @param mixed       $model
     * @param ArrayObject $response
     */
    public function __construct($model, ArrayObject $response)
    {
        parent::__construct($model);
        $this->response = $response;
    }

    /**
     * @return ArrayObject
     */
    public function getResponse()
    {
        return $this->response;
    }
}
