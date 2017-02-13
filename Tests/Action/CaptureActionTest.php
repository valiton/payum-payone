<?php

namespace Valiton\Payum\Payone\Tests\Action;

use Payum\Core\Request\Capture;
use Valiton\Payum\Payone\Action\CaptureAction;

class CaptureActionTest extends AbstractActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;
}
