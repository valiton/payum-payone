<?php

namespace Valiton\Payum\Payone\Tests\Api\Action;

use Payum\Core\Request\Refund;
use Valiton\Payum\Payone\Action\Api\RefundAction;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class RefundActionTest extends AbstractActionTest
{
    protected $actionClass = RefundAction::class;

    protected $requestClass = Refund::class;
}
