<?php

namespace Valiton\Payum\Payone\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Payum\Core\Bridge\Guzzle\HttpClient;
use Payum\Core\HttpClientInterface;
use Payum\Core\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Valiton\Payum\Payone\Api;

class ApiTest extends TestCase
{
    /**
     * @test
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        $this->expectExceptionMessage("he merchant_id, portal_id, key, sub_account_id fields are required.");
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        new Api(array());
    }

    /**
     * @test
     */
    public function shouldParseErrorFromPreauthorizationRequest()
    {
        $client = $this->createSuccessHttpClientStub('status=ERROR
errorcode=1005
errormessage=Parameter {currency} faulty or missing

customermessage=An error occured while processing this transaction (wrong parameters).

');

        $api = $this->createApi($client);

        $response = $api->preauthorize([]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey(Api::FIELD_STATUS, $response);
        $this->assertSame(Api::STATUS_ERROR, $response[Api::FIELD_STATUS]);
        $this->assertArrayHasKey(Api::FIELD_ERROR_CODE, $response);
        $this->assertArrayHasKey(Api::FIELD_ERROR_MESSAGE, $response);
        $this->assertArrayHasKey('customermessage', $response);
    }

    public function testApproveManageMandate()
    {
        $client = $this->createSuccessHttpClientStub('status=APPROVED
mandate_identification=PO-TESTTEST
mandate_status=pending
mandate_text=%3Cdiv+class%3D%22mandatetext%22%3E%3Cp%3E%3Cstrong%3ETEST+TEST+%2A%2A%2A+SEPA-Lastschriftmandat+%2A%2A%2A+TEST+TEST%3C%2Fstrong%3E%3C%2Fp%3E%3Cp%3EZahlungsempf%26auml%3Bnger%3A+Max+Mustermann+Kiel%2C+DE%3Cbr%3EGl%26auml%3Bubiger-Identifikationsnummer%3A+DE98ZZZ09999999999%3Cbr%3EMandatsreferenz%3A+PO-TESTTEST%3C%2Fp%3E%3Cp%3EIch+erm%26auml%3Bchtige+den+Zahlungsempf%26auml%3Bnger%2C+Zahlungen+von+meinem+Konto+mittels+Lastschrift+einzuziehen.+Zugleich+weise+ich+mein+Kreditinstitut+an%2C+die+von+dem+Zahlungsempf%26auml%3Bnger+auf+mein+Konto+gezogenen+Lastschriften+einzul%26ouml%3Bsen.%3C%2Fp%3E%3Cp%3EHinweis%3A+Ich+kann+innerhalb+von+acht+Wochen%2C+beginnend+mit+dem+Belastungsdatum%2C+die+Erstattung+des+belasteten+Betrages+verlangen.+Es+gelten+dabei+die+mit+meinem+Kreditinstitut+vereinbarten+Bedingungen.%3C%2Fp%3E%3Cdl%3E%3Cdt%3EName+des+Zahlungspflichtigen%3A%3C%2Fdt%3E%3Cdd%3EMax+Mustermann%3C%2Fdd%3E%3Cdt%3EOrt%3A+%3C%2Fdt%3E%3Cdd%3EKiel%3C%2Fdd%3E%3Cdt%3ELand%3A%3C%2Fdt%3E%3Cdd%3EDE%3C%2Fdd%3E%3Cdt%3EE-Mail%3A%3C%2Fdt%3E%3Cdd%3Emax.mustermann%40domain.de%3C%2Fdd%3E%3Cdt%3ESwift+BIC%3A+%3C%2Fdt%3E%3Cdd%3ETESTTEST%3C%2Fdd%3E%3Cdt%3EBankkontonummer+-+IBAN%3A%3C%2Fdt%3E%3Cdd%3EDE85123456782599100003%3C%2Fdd%3E%3C%2Fdl%3E%3Cp%3E%3Cspan+class%3D%22mandatetext_cityofsignature%22%3EKiel%3C%2Fspan%3E%2C+17.03.2016%2C+Max+Mustermann%3C%2Fp%3E%3C%2Fdiv%3E
creditor_identifier=DE98ZZZ09999999999
iban=DE85123456782599100003
bic=TESTTEST');

        $api = $this->createApi($client);
        $response = $api->manageMandate([
            Api::FIELD_CITY => 'Musterhausen',
            Api::FIELD_COUNTRY => 'DE',
            Api::FIELD_CURRENCY => 'EUR',
            Api::FIELD_IBAN => 'DE85123456782599100003',
            Api::FIELD_LAST_NAME => 'Mustermann',
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey(Api::FIELD_STATUS, $response);
        $this->assertSame(Api::STATUS_APPROVED, $response[Api::FIELD_STATUS]);
        $this->assertSame('DE85123456782599100003', $response[Api::FIELD_IBAN]);
        $this->assertSame('DE98ZZZ09999999999', $response[Api::FIELD_CREDITOR_IDENTIFIER]);
        $this->assertSame('TESTTEST', $response[Api::FIELD_BIC]);
    }

    public function testDecodeBodyHandlesNewlineInMandateText()
    {
        $client = $this->createSuccessHttpClientStub('status=APPROVED
mandate_identification=PO-13066168
mandate_status=pending
mandate_text=%3Cdiv+class%3D%22mandatetext%22%3E%0A%3Cp%3E%0A%3Cstrong%3ESEPA-Lastschriftmandat%3C%2Fstrong%3E%0A%3C%2Fp%3E%0A%3Cp%3E%0A++++++++++++++++Zahlungsempf%26auml%3Bnger%3A+DSV+Logistik+GmbH+Stuttgart%2C+DE%3Cbr%3E%0A++++Gl%26auml%3Bubiger-Identifikationsnummer%3A+DE1409H00000002918%3Cbr%3E%0A++++Mandatsreferenz%3A+PO-13066168%3C%2Fp%3E%0A%3Cp%3EIch+erm%26auml%3Bchtige+den+Zahlungsempf%26auml%3Bnger%2C+Zahlungen+von+meinem+Konto+mittels+Lastschrift+einzuziehen.+Zugleich+weise+ich+mein+Kreditinstitut+an%2C+die+von+dem+Zahlungsempf%26auml%3Bnger+auf+mein+Konto+gezogenen+Lastschriften+einzul%26ouml%3Bsen.%3C%2Fp%3E%0A%3Cp%3EHinweis%3A+Ich+kann+innerhalb+von+acht+Wochen%2C+beginnend+mit+dem+Belastungsdatum%2C+die+Erstattung+des+belasteten+Betrages+verlangen.+Es+gelten+dabei+die+mit+meinem+Kreditinstitut+vereinbarten+Bedingungen.%3C%2Fp%3E%0A%3Cdl%3E%0A%3Cdt%3EName+des+Zahlungspflichtigen%3A%3C%2Fdt%3E%0A%3Cdd%3E+Mustermann%3C%2Fdd%3E%0A%3Cdt%3EOrt%3A+%3C%2Fdt%3E%0A%3Cdd%3EMusterhausen%3C%2Fdd%3E%0A%3Cdt%3ELand%3A%3C%2Fdt%3E%0A%3Cdd%3EDE%3C%2Fdd%3E%0A%3Cdt%3EE-Mail%3A%3C%2Fdt%3E%0A%3Cdd%3E%3C%2Fdd%3E%0A%3Cdt%3ESwift+BIC%3A+%3C%2Fdt%3E%0A%3Cdd%3ETESTTEST%3C%2Fdd%3E%0A%3Cdt%3EBankkontonummer+-+IBAN%3A%3C%2Fdt%3E%0A%3Cdd%3EDE12500105170648489890%3C%2Fdd%3E%0A%3C%2Fdl%3E%0A%3Cp%3E%0A%3Cspan+class%3D%22mandatetext_cityofsignature%22%3EMusterhausen%3C%2Fspan%3E%2C+01.08.2016%2C+Mustermann%3C%2Fp%3E%0A%3C%2Fdiv%3E%0A
creditor_identifier=DE1409H00000002918
iban=DE12500105170648489890
bic=TESTTEST
');

        $api = $this->createApi($client);
        $response = $api->manageMandate([
            Api::FIELD_CITY => 'Musterhausen',
            Api::FIELD_COUNTRY => 'DE',
            Api::FIELD_CURRENCY => 'EUR',
            Api::FIELD_IBAN => 'DE85123456782599100003',
            Api::FIELD_LAST_NAME => 'Mustermann',
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey(Api::FIELD_MANDATE_TEXT, $response);
        $this->assertStringStartsWith('<div class="mandatetext">', $response[Api::FIELD_MANDATE_TEXT]);
        $this->assertStringEndsWith('</div>', $response[Api::FIELD_MANDATE_TEXT]);
    }

    public function testGetfile()
    {
        $expected = file_get_contents(__DIR__ . '/XX-T0000000.pdf');
        $api = $this->createApi($this->createSuccessHttpClientStub($expected));

        $actual = $api->getFile([
            'file_type' => 'SEPA_MANDATE',
            'file_format' => 'PDF',
            'file_reference' => 'XX-T0000000',
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testAidParamIsNotSendForGetfileRequest()
    {
        $test = $this;
        $client = $this->createHttpClientMock();
        $client->expects($this->atLeastOnce())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($test) {
                $fields = [];
                parse_str($request->getBody(), $fields);
                $test->assertArrayNotHasKey('aid', $fields);

                return new Response();
            }));

        $api = $this->createApi($client);
        $api->getFile([]);
    }

    public function testThrowsExceptionIfNarrativeTextIsLongerThan81Characters()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $api = $this->createApi(
            $this->createSuccessHttpClientStub()
        );

        $api->capture([
            'narrative_text' => str_pad('', 82, 'x'),
        ]);
    }

    public function testAcceptsNarrativeTextEqualTo81Characters()
    {
        $this->expectNotToPerformAssertions();
        $api = $this->createApi(
            $this->createSuccessHttpClientStub()
        );

        $api->capture([
            'narrative_text' => str_pad('', 81, 'x'),
        ]);
    }

    public function testThrowsExceptionIfNarrativeTextIsLongerThan37CharactersForPaydirekt()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $api = $this->createApi(
            $this->createSuccessHttpClientStub()
        );

        $api->capture([
            'payment_method' => Api::PAYMENT_METHOD_PAYDIREKT,
            'narrative_text' => str_pad('', 38, 'x'),
        ]);
    }

    public function testAcceptsNarrativeTextEqualTo37CharactersForPaydirekt()
    {
        $this->expectNotToPerformAssertions();
        $api = $this->createApi(
            $this->createSuccessHttpClientStub()
        );

        $api->capture([
            'payment_method' => Api::PAYMENT_METHOD_PAYDIREKT,
            'narrative_text' => str_pad('', 37, 'x'),
        ]);
    }

    protected function createApi(HttpClientInterface $client)
    {
        return new Api([
            'merchant_id' => array_key_exists('PAYONE_MERCHANT_ID', $_ENV) ? $_ENV['PAYONE_MERCHANT_ID'] : 12345,
            'portal_id' => array_key_exists('PAYONE_PORTAL_ID', $_ENV) ? $_ENV['PAYONE_PORTAL_ID'] : 7654321,
            'key' => array_key_exists('PAYONE_KEY', $_ENV) ? $_ENV['PAYONE_KEY'] : 'qwertz',
            'sub_account_id' => array_key_exists('PAYONE_SUB_ACCOUNT_ID', $_ENV) ? $_ENV['PAYONE_SUB_ACCOUNT_ID'] : 56789,
            'sandbox' => true,
        ], $client);
    }

    /**
     * @return HttpClient
     */
    protected function createHttpClient()
    {
        return new HttpClient(new Client());
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock(HttpClientInterface::class);
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub($responseBody = '')
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($responseBody) {
                return new Response(200, [], $responseBody);
            }));
        return $clientMock;
    }
}
