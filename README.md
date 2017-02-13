# PAYONE

The Payum extension. It provides PAYONE payment integration

## Getting started

Register factory and gateway with the `PayumBuilder`

```php?start_inline=1
use Payum\Core\GatewayFactoryInterface;
use Valiton\Payum\Payone\PayoneGatewayFactory;

$payumBuilder->addGatewayFactory('payone', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
    return new PayoneGatewayFactory($config, $coreGatewayFactory);
});

$payumBuilder->addGateway('payone', [
    'factory' => 'payone',
    'merchant_id' => 123456, // change this
    'sub_account_id' => 567890 // change this
    'portal_id' => 6543276, // change this
    'key' => '1Q2W3E4R5T6' // change this
]);
```

The Payone gateway needs some additional fields to work properly

```php?start_inline=1
$payment = new Payment();
$payment->setDetails([
    // add required key value pairs for the payment method
]);
```

Please see the Payment Method Documentation for Payment Method specific fields.

* [Credit Card](Resources/doc/credit-card.md)
* [Giropay](Resources/doc/giropay.md)
* [Paydirekt](Resources/doc/paydirekt.md)
* [Direct Debit SEPA](Resources/doc/direct-debit-sepa.md)


## Symfony Integration

Payum Bundle 2.0 requires at least Symfony 2.8. If you want to use an older version of Symfony take a look at
http://stackoverflow.com/questions/35896718/register-custom-gateway-with-payumbundle/35900365

Install the Payum Bundle and set it up according to it's documentation.

    composer require payum/payum-bundle ^2.0

Register the Payone Gateway Factory as a service

```yml
# app/config/services.yml

services:
    app.payum.payone.factory:
        class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
        arguments: [Valiton\Payum\Payone\PayoneGatewayFactory]
        tags:
            - { name: payum.gateway_factory_builder, factory: payone }
```

And configure your credentials for the gateway

```yml
# app/config/config.yml

payum:
    gateways:
        payone:
            factory: payone
            merchant_id: 123456 # change this
            sub_account_id: 567890 # change this
            portal_id: 6543276 # change this
            key: 1Q2W3E4R5T6 # change this
```

Now you can retrieve the gateway from the `payum` service

```php
$gateway = $this->get('payum')->getGeteway('payone');
```

##License

The MIT License (MIT)

Copyright (c) 2016 Valiton GmbH

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.