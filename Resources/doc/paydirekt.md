# Paydirekt

* [Authorize](#authorize)
* [Capture](#capture)
* [Capture Authorized Payment](#capture-authorized-payment)
* [Refund](#refund)

## Authorize

```php?start_inline=1
use Payum\Core\Request\Authorize;
use Valiton\Payum\Payone\Api;

$payment = [];
$payment[Api::FIELD_PAYMENT_METHOD] = Api::PAYMENT_METHOD_PAYDIREKT;

$payum
    ->getGateway('payone')
    ->execute(new Authorize($payment));
```

![Sequence Diagram Authorize Payment](paydirekt-authorize.png "Sequence Diagram Authorize Payment")

## Capture

```php?start_inline=1
use Payum\Core\Request\Capture;
use Valiton\Payum\Payone\Api;

$payment = [];
$payment[Api::FIELD_PAYMENT_METHOD] = Api::PAYMENT_METHOD_PAYDIREKT;

$payum
    ->getGateway('payone')
    ->execute(new Capture($payment));
```

![Sequence Diagram Capture Payment](paydirekt-capture.png "Sequence Diagram Capture Payment")

## Capture Authorized Payment

```php?start_inline=1
use Payum\Core\Model\Payment;
use Payum\Core\Request\Capture;

$payments = $payum
    ->getStorage(Payment::class)
    ->findBy(['number' => $paymentNumber]);
$payment = array_shift($payments);

$token = $payum->getTokenFactory()->createCaptureToken(
    'payone',
    $payment,
    'done.php'
);

header('Location: ' . $this->redirect($token->getTargetUrl());
```

![Sequence Diagram Capture Authorized Payment](capture-authorized.png "Sequence Diagram Capture Authorized Payment")

## Refund

Only captured payments can be refunded.

```php?start_inline=1
use Payum\Core\Model\Payment;
use Payum\Core\Request\Refund;

$payments = $payum
    ->getStorage(Payment::class)
    ->findBy(['number' => $paymentNumber]);
$payment = array_shift($payments);

$payum
    ->getGateway('payone')
    ->execute(new Refund($payment));
```

![Sequence Diagram Refund Payment](refund.png "Sequence Diagram Refund Payment")

