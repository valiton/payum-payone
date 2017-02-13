<?php

namespace Valiton\Payum\Payone\Tests\Action\Api;

use Valiton\Payum\Payone\Action\Api\AuthorizeAction;
use Valiton\Payum\Payone\Request\Api\Authorize;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class AuthorizeActionTest extends AbstractActionTest
{
    protected $actionClass = AuthorizeAction::class;

    protected $requestClass = Authorize::class;
}
