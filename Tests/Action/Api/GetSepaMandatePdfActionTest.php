<?php

namespace Valiton\Payum\Payone\Tests\Action\Api;

use Valiton\Payum\Payone\Action\Api\GetSepaMandatePdfAction;
use Valiton\Payum\Payone\Request\Api\GetSepaMandatePdf;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class GetSepaMandatePdfActionTest extends AbstractActionTest
{
    protected $actionClass = GetSepaMandatePdfAction::class;

    protected $requestClass = GetSepaMandatePdf::class;
}
