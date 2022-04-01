<?php

namespace Valiton\Payum\Payone\Tests\Api\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Valiton\Payum\Payone\Action\Api\CaptureAction;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\Capture;
use Valiton\Payum\Payone\Request\Api\ConvertGiropayErrors;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class CaptureActionTest extends AbstractActionTest
{
    /**
     * @var CaptureAction
     */
    protected $action;

    protected $actionClass  = CaptureAction::class;

    protected $requestClass = Capture::class;

    public function testStatusStaysAuthorizedIfError()
    {
        $model   = new ArrayObject([Api::FIELD_STATUS => 'authorized']);
        $request = new $this->requestClass($model);

        $api     = $this->getMockBuilder(Api::class)
                        ->disableOriginalConstructor()
                        ->getMock()
        ;
        $gateway = $this->createMock(GatewayInterface::class);

        $api
            ->expects($this->once())
            ->method('capture')
            ->willReturn([
                             'status' => 'ERROR',
                         ]
            )
        ;

        $gateway
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->callback(
                    function (ConvertGiropayErrors $request) use ($model) {
                        $this->assertEquals($model, $request->getModel());
                        $this->assertEquals(['status' => 'ERROR'], $request->getResponse()->toUnsafeArray());

                        return true;
                    }
                )
            )
        ;

        $this->action->setApi($api);
        $this->action->setGateway($gateway);

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
                         ]
            )
        ;

        $this->action->setApi($api);

        $model   = new \ArrayObject([Api::FIELD_STATUS => 'authorized']);
        $request = new $this->requestClass($model);
        $this->action->execute($request);

        $this->assertEquals('captured', $model[Api::FIELD_STATUS]);
    }
}
