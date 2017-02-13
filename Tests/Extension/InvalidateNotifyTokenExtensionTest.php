<?php

namespace Valiton\Payum\Payone\Tests\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Request\Notify;
use Payum\Core\Storage\StorageInterface;
use Valiton\Payum\Payone\Action\NotifyAction;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Extension\InvalidateNotifyTokenExtension;

class InvalidateNotifyTokenExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldNotErrorIfNoTokenStorageIsPassed()
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new InvalidateNotifyTokenExtension(null);
        $extension->onPostExecute($context);
    }

    /**
     * @test
     */
    public function shouldDeleteTokenIfPaymentStatusIsApproved()
    {
        // propably we can remove if txaction is paid or capture. but then we need to re-create a notify capture token for refund
        $this->markTestSkipped('Deletion has been disabled because we receive multiple notifies on the same token');

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->method('getAction')
            ->willReturn(new NotifyAction());

        $context->method('getRequest')
            ->willReturn(new Notify([
                'param' => 'foobarparam',
                Api::FIELD_STATUS => 'completed',
                'completed_status' => 'completed',
            ]));

        $storage = $this->getMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('delete');

        $extension = new InvalidateNotifyTokenExtension($storage);
        $extension->onPostExecute($context);
    }

    /**
     * @test
     */
    public function shouldDoNotDeleteTokenIfPaymentStatusIsNotApproved()
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->method('getAction')
            ->willReturn(new NotifyAction());

        $context->method('getRequest')
            ->willReturn(new Notify([
                'param' => 'foobarparam',
                Api::FIELD_TRANSACTION_STATUS => 'whatever',
            ]));

        $storage = $this->getMock(StorageInterface::class);
        $storage->expects($this->never())
            ->method('delete');

        $extension = new InvalidateNotifyTokenExtension($storage);
        $extension->onPostExecute($context);
    }
}
