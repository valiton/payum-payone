<?php

namespace Valiton\Payum\Payone\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Valiton\Payum\Payone\Action\StatusAction;

class StatusActionTest extends AbstractActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    public function provideStatus()
    {
        return [
            [GetHumanStatus::STATUS_AUTHORIZED],
            [GetHumanStatus::STATUS_CANCELED],
            [GetHumanStatus::STATUS_CAPTURED],
            [GetHumanStatus::STATUS_FAILED],
            [GetHumanStatus::STATUS_PENDING],
        ];
    }

    /**
     * @dataProvider provideStatus
     * @test
     */
    public function shouldMarkRequestWithCorrectStatus($status)
    {
        $action = new $this->actionClass;
        $request = new $this->requestClass(['status' => $status]);

        $action->execute($request);

        $statusGetter = 'is' . ucfirst($status);
        $this->assertTrue($request->$statusGetter());
    }
}
