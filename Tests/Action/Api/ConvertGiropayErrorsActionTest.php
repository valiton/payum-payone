<?php

namespace Valiton\Payum\Payone\Tests\Api\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Tests\GenericActionTest;
use Valiton\Payum\Payone\Action\Api\ConvertGiropayErrorsAction;
use Valiton\Payum\Payone\Api;
use Valiton\Payum\Payone\Request\Api\ConvertGiropayErrors;
use Valiton\Payum\Payone\Request\Generic;

/**
 * Tests of ConvertGiropayErrorsAction.
 *
 * @group unit
 */
class ConvertGiropayErrorsActionTest extends GenericActionTest
{
    /**
     * @var ConvertGiropayErrorsAction
     */
    protected $action;

    protected $actionClass  = ConvertGiropayErrorsAction::class;

    protected $requestClass = ConvertGiropayErrors::class;

    public function provideSupportedRequests()
    {
        return [
            [new $this->requestClass([], new ArrayObject())],
            [new $this->requestClass(new \ArrayObject(), new ArrayObject())],
        ];
    }

    public function provideNotSupportedRequests()
    {
        return [
            ['foo', new ArrayObject()],
            [['foo'], new ArrayObject()],
            [new \stdClass(), new ArrayObject()],
            [new $this->requestClass('foo', new ArrayObject())],
            [new $this->requestClass(new \stdClass(), new ArrayObject())],
            [$this->getMockForAbstractClass(Generic::class, [[]])],
        ];
    }

    /**
     * @return array
     */
    public function nonSpeakingErrorsProvider()
    {
        return [
            ['1087', 'error happened'],
            ['887', 'error happened']
        ];
    }

    /**
     * @test
     * @dataProvider nonSpeakingErrorsProvider
     *
     * @param $errorCode
     * @param $errorMessage
     */
    public function shouldRewriteGiropayNonSpeakingErrors($errorCode, $errorMessage)
    {
        $model    = new ArrayObject(
            [
                Api::FIELD_PAYMENT_METHOD => Api::PAYMENT_METHOD_GIROPAY
            ]
        );
        $response = new ArrayObject(
            [
                Api::FIELD_STATUS           => Api::STATUS_ERROR,
                Api::FIELD_CUSTOMER_MESSAGE => $errorMessage,
                Api::FIELD_ERROR_CODE       => $errorCode,
                Api::FIELD_ERROR_MESSAGE    => $errorMessage,
            ]
        );
        $request  = new $this->requestClass($model, $response);
        $this->action->execute($request);

        $this->assertEquals('885', $response[Api::FIELD_ERROR_CODE]);
        $this->assertEquals('Bank is not supported by giropay', $response[Api::FIELD_ERROR_MESSAGE]);
        $this->assertEquals('Bank is not supported by giropay', $response[Api::FIELD_CUSTOMER_MESSAGE]);
    }

    /**
     * @test
     */
    public function shouldNotRewritePtherGiropayErrors()
    {
        $model    = new ArrayObject(
            [
                Api::FIELD_PAYMENT_METHOD => Api::PAYMENT_METHOD_GIROPAY
            ]
        );
        $response = new ArrayObject(
            [
                Api::FIELD_STATUS           => Api::STATUS_ERROR,
                Api::FIELD_CUSTOMER_MESSAGE => 'other error',
                Api::FIELD_ERROR_CODE       => '11',
                Api::FIELD_ERROR_MESSAGE    => 'other error',
            ]
        );
        $request  = new $this->requestClass($model, $response);
        $this->action->execute($request);

        $this->assertEquals('11', $response[Api::FIELD_ERROR_CODE]);
        $this->assertEquals('other error', $response[Api::FIELD_ERROR_MESSAGE]);
        $this->assertEquals('other error', $response[Api::FIELD_CUSTOMER_MESSAGE]);
    }
}
