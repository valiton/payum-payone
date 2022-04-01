<?php

namespace Valiton\Payum\Payone\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\TokenInterface;
use Valiton\Payum\Payone\Action\Api\GetSepaMandateAction;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\GetSepaMandate;
use Valiton\Payum\Payone\Tests\Action\AbstractActionTest;

class GetSepaMandateActionTest extends AbstractActionTest
{
    protected $actionClass = GetSepaMandateAction::class;

    protected $requestClass = GetSepaMandate::class;

    protected function setUp() : void
    {
        $this->action = new $this->actionClass('@PayumPayone/Action/get_sepa_mandate.html.twig');

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

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass(new ArrayObject(), 'array')];
        yield [new $this->requestClass($this->createMock(ArrayAccess::class), 'array')];
        yield [new $this->requestClass(new ArrayObject(), 'array', $this->createMock(TokenInterface::class))];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
        yield array(new $this->requestClass(new \stdClass(), 'array'));
    }

    public function testPaymentMarkedAsFailedIfIbanIsMissing()
    {
        $this->action->execute($request = new GetSepaMandate(new ArrayObject([])));

        $this->assertEquals('failed', $request->getModel()[Api::FIELD_STATUS]);
    }

    public function testDoNotErrorIfMandateIdentificationIsNotPresentInRequest()
    {
        $this->expectNotToPerformAssertions();
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway
            ->method('execute')
            ->willReturnCallback(function(GetHttpRequest $httpRequest) {
                $httpRequest->method = 'POST';
            });

        $this->action->setGateway($gateway);

        $this->api
            ->method('managemandate')
            ->willReturn([Api::FIELD_STATUS => Api::STATUS_ERROR]);

        $this->action->execute($request = new GetSepaMandate(new ArrayObject([
            Api::FIELD_CITY => 'Musterhausen',
            Api::FIELD_COUNTRY => 'DE',
            Api::FIELD_CURRENCY => 'EUR',
            Api::FIELD_IBAN => 'DE58123456782599100004',
            Api::FIELD_LAST_NAME => 'Mustermann',
        ])));
    }
}
