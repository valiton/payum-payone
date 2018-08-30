<?php

namespace Valiton\Payum\Payone\Tests\Api\Action;

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

    public function testRewriteGiropayError1087To885()
    {
        $api = $this
            ->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $api
            ->expects($this->once())
            ->method('capture')
            ->with(
                [
                    Api::FIELD_STATUS         => 'authorized',
                    Api::FIELD_PAYMENT_METHOD => Api::PAYMENT_METHOD_GIROPAY
                ]
            )
            ->willReturn(
                [
                    'status'                    => 'ERROR',
                    Api::FIELD_CUSTOMER_MESSAGE => 'error happened',
                    Api::FIELD_ERROR_CODE       => '1087',
                    Api::FIELD_ERROR_MESSAGE    => 'error happened',
                ]
            )
        ;

        $this->action->setApi($api);

        $model   = new \ArrayObject(
            [
                Api::FIELD_STATUS         => 'authorized',
                Api::FIELD_PAYMENT_METHOD => Api::PAYMENT_METHOD_GIROPAY
            ]
        );
        $request = new $this->requestClass($model);
        $this->action->execute($request);

        $this->assertEquals('authorized', $model[Api::FIELD_STATUS]);
        $this->assertEquals(885, $model[Api::FIELD_ERROR_CODE]);
        $this->assertEquals('Bank is not supported by giropay', $model[Api::FIELD_ERROR_MESSAGE]);
        $this->assertEquals('Bank is not supported by giropay', $model[Api::FIELD_CUSTOMER_MESSAGE]);
    }
}
