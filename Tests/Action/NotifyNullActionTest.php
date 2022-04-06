<?php

namespace Valiton\Payum\Payone\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Notify;
use Valiton\Payum\Payone\Action\NotifyNullAction;

class NotifyNullActionTest extends AbstractActionTest
{
    protected $actionClass = NotifyNullAction::class;

    protected $requestClass = Notify::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass(null)];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
        yield array(new $this->requestClass(new \stdClass(), 'array'));
    }

    /**
     * @test
     */
    public function shouldThrowHttpErrorIfTokenParamIsMissing()
    {
        $this->expectException(\Payum\Core\Reply\HttpResponse::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $this->action->setGateway($gateway);
        $this->action->execute(new Notify(null));
    }
}
