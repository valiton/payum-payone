<?php

namespace Valiton\Payum\Payone\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\TokenInterface;
use Valiton\Payum\Payone\Action\Api\GetPseudoCardPanAction;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\GetPseudoCardPan;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class GetPseudoCardPanActionTest extends AbstractActionTest
{
    protected $actionClass = GetPseudoCardPanAction::class;

    protected $requestClass = GetPseudoCardPan::class;

    protected function setUp()
    {
        $this->action = new $this->actionClass('@PayumPayone/Action/get_pseudo_card_pan.html.twig', []);

        if ($this->action instanceof ApiAwareInterface) {
            $this->api = $this->getMockBuilder(Api::class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->action->setApi($this->api);
        }
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        // This action cannot be constructed without arguments and that is fine
        $this->assertTrue(true);
    }

    public function provideSupportedRequests()
    {
        return [
            [new $this->requestClass(new ArrayObject(), 'array')],
            [new $this->requestClass($this->getMock(ArrayAccess::class), 'array')],
            [new $this->requestClass(new ArrayObject(), 'array', $this->getMock(TokenInterface::class))],
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
     * @expectedException \Payum\Core\Reply\HttpResponse
     */
    public function testDoNotFailOnEmptyPostRequest()
    {
        $gateway = $this->getMock(GatewayInterface::class);
        $gateway
            ->method('execute')
            ->willReturnCallback(function($arg) {
                if ($arg instanceof GetHttpRequest) {
                    $arg->method = 'POST';
                }
            });

        $this->action->setGateway($gateway);

        $model = new ArrayObject([
            Api::FIELD_LANGUAGE => 'de',
        ]);
        $this->action->execute($request = new GetPseudoCardPan($model));

        $this->assertNotEquals('failed', $model->get(Api::FIELD_STATUS));
    }
}
