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

    public function provideSupportedRequests()
    {
        return [
            [new $this->requestClass(null)],
        ];
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
        );
    }

    /**
     * @test
     * @expectedException \Payum\Core\Reply\HttpResponse
     */
    public function shouldThrowHttpErrorIfTokenParamIsMissing()
    {
        $gateway = $this->getMock(GatewayInterface::class);

        $this->action->setGateway($gateway);
        $this->action->execute(new Notify(null));
    }
}
