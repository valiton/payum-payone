<?php

namespace Valiton\Payum\Payone\Tests\Api\Action;

use Payum\Core\Model\Payment;
use Valiton\Payum\Payone\Action\Api\CaptureAction;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\Capture;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class CaptureActionTest extends AbstractActionTest
{
    /**
     * @var CaptureAction
     */
    protected $action;

    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

    public function testStatusStaysAuthorizedIfError()
    {
        $api = $this->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $api
            ->expects($this->once())
            ->method('capture')
            ->willReturn([
                'status' => 'ERROR',
            ]);

        $this->action->setApi($api);

        $model = new \ArrayObject([Api::FIELD_STATUS => 'authorized']);
        $request = new $this->requestClass($model);
        $this->action->execute($request);

        $this->assertEquals('authorized', $model[Api::FIELD_STATUS]);
    }

    public function testStatusChangesToCaptureIfApproved()
    {
        $api = $this->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $api
            ->expects($this->once())
            ->method('capture')
            ->willReturn([
                'status' => 'APPROVED',
            ]);

        $this->action->setApi($api);

        $model = new \ArrayObject([Api::FIELD_STATUS => 'authorized']);
        $request = new $this->requestClass($model);
        $this->action->execute($request);

        $this->assertEquals('captured', $model[Api::FIELD_STATUS]);
    }
}
