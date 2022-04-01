<?php

namespace Valiton\Payum\Payone\Tests\Action;

use ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Valiton\Payum\Payone\Action\NotifyAction;
use Valiton\Payum\Payone\Api;

class NotifyActionTest extends AbstractActionTest
{
    protected $actionClass = NotifyAction::class;

    protected $requestClass = Notify::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass(new ArrayObject([]))];

    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass(null)];
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
        yield array(new $this->requestClass(new \stdClass(), 'array'));
    }

    /**
     * @test
     *
     */
    public function shouldThrowExceptionIfTransactionStatusParamIsMissing()
    {
        $this->expectException(\Payum\Core\Reply\HttpResponse::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $this->action->setGateway($gateway);
        $this->action->execute(new Notify(new ArrayObject([])));
    }

    /**
     * @test
     */
    public function shouldStoreTransactionStatusInModel()
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturnCallback(function($request) {
                if ($request instanceof GetHttpRequest) {
                    $request->content = http_build_query([
                        'txaction' => 'appointed',
                        Api::FIELD_TRANSACTION_STATUS => Api::TRANSACTION_STATUS_COMPLETED,
                    ]);
                }
            });

        $this->action->setGateway($gateway);
        try {
            $this->action->execute($notify = new Notify(new ArrayObject([])));
        }
        catch (HttpResponse $reply) {
            $this->assertEquals(
                'TSOK',
                $reply->getContent()
            );
        }

        $this->assertEquals(
            Api::TRANSACTION_STATUS_COMPLETED,
            $notify->getModel()[Api::FIELD_TRANSACTION_STATUS]
        );
    }

    public function testRefundedPaymentDoesNotChangeBackToCaptured()
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function($request) {
                if ($request instanceof GetHttpRequest) {
                    $request->content = http_build_query([
                        'txaction' => 'paid',
                    ]);
                }
            });

        $this->action->setGateway($gateway);
        try {
            $this->action->execute($notify = new Notify(new ArrayObject([
                Api::FIELD_STATUS => 'refunded',
                'completed_status' => 'captured'
            ])));
        }
        catch (HttpResponse $reply) {
            $this->assertEquals(
                'TSOK',
                $reply->getContent()
            );
        }

        $this->assertEquals(
            'refunded',
            $notify->getModel()[Api::FIELD_STATUS]
        );
    }

    public function testStatusIsCapturedAfterTxActionCapture()
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function($request) {
                if ($request instanceof GetHttpRequest) {
                    $request->content = http_build_query([
                        'txaction' => 'capture',
                    ]);
                }
            });

        $this->action->setGateway($gateway);
        try {
            $this->action->execute($notify = new Notify(new ArrayObject([
                Api::FIELD_STATUS => 'captured',
                'completed_status' => 'captured'
            ])));
        }
        catch (HttpResponse $reply) {
            $this->assertEquals(
                'TSOK',
                $reply->getContent()
            );
        }

        $this->assertEquals(
            'captured',
            $notify->getModel()[Api::FIELD_STATUS]
        );
    }

    public function testTxActionAppointedIsProcessEvenWithoutTransactionStatus()
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function($request) {
                if ($request instanceof GetHttpRequest) {
                    $request->content = http_build_query([
                        'txaction' => 'appointed',
                    ]);
                }
            });

        $this->action->setGateway($gateway);
        try {
            $this->action->execute($notify = new Notify(new ArrayObject([
                Api::FIELD_STATUS => 'pending',
                'completed_status' => 'captured'
            ])));
        }
        catch (HttpResponse $reply) {
            $this->assertEquals(
                'TSOK',
                $reply->getContent()
            );
        }

        $this->assertEquals(
            'captured',
            $notify->getModel()[Api::FIELD_STATUS]
        );
    }
}
