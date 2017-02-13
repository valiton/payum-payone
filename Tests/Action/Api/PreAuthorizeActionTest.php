<?php

namespace Valiton\Payum\Payone\Tests\Action\Api;

use Valiton\Payum\Payone\Action\Api\PreAuthorizeAction;
use Valiton\Payum\Payone\Request\Api\PreAuthorize;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class PreAuthorizeActionTest extends AbstractActionTest
{
    protected $actionClass = PreAuthorizeAction::class;

    protected $requestClass = PreAuthorize::class;
}
