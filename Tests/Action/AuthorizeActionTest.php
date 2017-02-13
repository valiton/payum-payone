<?php

namespace Valiton\Payum\Payone\Tests\Action;

use Payum\Core\Request\Authorize;
use Valiton\Payum\Payone\Action\AuthorizeAction;

class AuthorizeActionTest extends AbstractActionTest
{
    protected $actionClass = AuthorizeAction::class;

    protected $requestClass = Authorize::class;
}
