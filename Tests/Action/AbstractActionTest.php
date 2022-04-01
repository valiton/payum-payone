<?php

namespace Valiton\Payum\Payone\Tests\Action;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Tests\GenericActionTest;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Valiton\Payum\Payone\Api;

abstract class AbstractActionTest extends GenericActionTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $api;

    protected function setUp() : void
    {
        parent::setUp();

        if ($this->action instanceof LoggerAwareInterface) {
            $this->action->setLogger($this->createMock(LoggerInterface::class));
        }

        if ($this->action instanceof ApiAwareInterface) {
            $this->api = $this->getMockBuilder(Api::class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->action->setApi($this->api);
        }
    }
}
