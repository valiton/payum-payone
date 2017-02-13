<?php

namespace Valiton\Payum\Payone;

use GuzzleHttp\Psr7\Request;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;

class Api
{
    const SOLUTION_NAME = 'valiton';
    const SOLUTION_VERSION = '0.6';
    const INTEGRATOR_NAME = 'payum';
    const INTEGRATOR_VERSION = '1.2';

    const FIELD_AMOUNT = 'amount';
    const FIELD_BIC = 'bic';
    const FIELD_CITY = 'city';
    const FIELD_COMPANY = 'company';
    const FIELD_COUNTRY = 'country';
    const FIELD_CREDITOR_IDENTIFIER = 'creditor_identifier';
    const FIELD_CURRENCY = 'currency';
    const FIELD_CURRENCY_CODE = 'currency';
    const FIELD_CUSTOMER_MESSAGE = 'customermessage';
    const FIELD_ERROR_CODE = 'errorcode';
    const FIELD_ERROR_MESSAGE = 'errormessage';
    const FIELD_FILE_CONTENTS = 'file_contents';
    const FIELD_FILE_FORMAT = 'file_format';
    const FIELD_FILE_REFERENCE = 'file_reference';
    const FIELD_FILE_TYPE = 'file_type';
    const FIELD_FIRST_NAME = 'firstname';
    const FIELD_IBAN = 'iban';
    const FIELD_LANGUAGE = 'language';
    const FIELD_LAST_NAME = 'lastname';
    const FIELD_MANDATE_DATE = 'mandate_confirmed';
    const FIELD_MANDATE_IDENTIFICATION = 'mandate_identification';
    const FIELD_MANDATE_STATUS = 'mandate_status';
    const FIELD_MANDATE_TEXT = 'mandate_text';
    const FIELD_NARRATIVE_TEXT = 'narrative_text';
    const FIELD_PAYMENT_METHOD = 'payment_method';
    const FIELD_PSEUDO_CARD_PAN = 'pseudocardpan';
    const FIELD_REFERENCE = 'reference';
    const FIELD_SHIPPING_CITY = 'shipping_city';
    const FIELD_SHIPPING_COMPANY = 'shipping_company';
    const FIELD_SHIPPING_COUNTRY = 'shipping_country';
    const FIELD_SHIPPING_FIRST_NAME = 'shipping_firstname';
    const FIELD_SHIPPING_LAST_NAME = 'shipping_lastname';
    const FIELD_SHIPPING_STREET = 'shipping_street';
    const FIELD_SHIPPING_ZIP = 'shipping_zip';
    const FIELD_STATUS = 'status';
    const FIELD_TRANSACTION_STATUS = 'transaction_status';
    const FIELD_TRUNCATED_CARD_PAN = 'truncatedcardpan';
    const FIELD_TX_ACTION = 'txaction';
    const FIELD_ZIP = 'zip';

    /**
     * @deprecated Use FIELD_FIRST_NAME instead
     */
    const FIELD_FIRSTNAME = 'firstname';

    /**
     * @deprecated Use FIELD_LAST_NAME instead
     */
    const FIELD_LASTNAME = 'lastname';

    const MANDATE_STATUS_ACTIVE = 'active';
    const MANDATE_STATUS_PENDING = 'pending';

    const PAYMENT_METHOD_CREDIT_CARD_PPAN = 'credit_card_ppan';
    const PAYMENT_METHOD_DIRECT_DEBIT_SEPA = 'direct_debit_sepa';
    const PAYMENT_METHOD_GIROPAY = 'giropay';
    const PAYMENT_METHOD_PAYDIREKT = 'paydirekt';

    const STATUS_APPROVED = 'APPROVED';
    const STATUS_ERROR = 'ERROR';
    const STATUS_REDIRECT = 'REDIRECT';

    const TRANSACTION_STATUS_COMPLETED = 'completed';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @param array $options
     * @param HttpClientInterface $client
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client = null)
    {
        $options = ArrayObject::ensureArrayObject($options);

        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'merchant_id',
            'portal_id',
            'key',
            'sub_account_id',
        ));

        if (false === is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client ?: HttpClientFactory::create();
    }

    /**
     * @param array $fields
     * @return array
     */
    public function preauthorize(array $fields)
    {
        $fields['request'] = 'preauthorization';

        return $this->decodeBody($this->doRequest($fields));
    }

    /**
     * @param array $fields
     * @return array
     */
    public function authorize(array $fields)
    {
        $fields['request'] = 'authorization';

        return $this->decodeBody($this->doRequest($fields));
    }

    /**
     * @param array $fields
     * @return array
     */
    public function capture(array $fields)
    {
        $fields['request'] = 'capture';

        return $this->decodeBody($this->doRequest($fields));
    }

    /**
     * @param array $fields
     * @return array
     */
    public function manageMandate(array $fields)
    {
        $fields['clearingtype'] = 'elv';
        $fields['request'] = 'managemandate';

        return $this->decodeBody($this->doRequest($fields));
    }

    /**
     * @param array $fields
     * @return string
     */
    public function getFile(array $fields)
    {
        $fields = array_intersect_key(
            $fields,
            array_flip([
                Api::FIELD_FILE_FORMAT,
                Api::FIELD_FILE_REFERENCE,
                Api::FIELD_FILE_TYPE,
            ])
        );

        $fields['request'] = 'getfile';

        return $this->doRequest($fields);
    }

    public function refund(array $fields)
    {
        $fields['request'] = 'refund';
        $fields['sequencenumber'] = 2;

        return $this->decodeBody($this->doRequest($fields));
    }

    /**
     * @param array $fields
     *
     * @return string
     */
    protected function doRequest(array $fields)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $fields['mid'] = $this->options['merchant_id'];
        $fields['portalid'] = $this->options['portal_id'];
        $fields['key'] = hash('md5', $this->options['key']);
        if ('getfile' !== $fields['request']) {
            // "aid" may not be passed for getfile requests
            $fields['aid'] = $this->options['sub_account_id'];
        }
        $fields['api_version'] = '3.9';
        $fields['mode'] = $this->options['sandbox'] ? 'test' : 'live';
        $fields['encoding'] = 'UTF-8';
        $fields['solution_name'] = static::SOLUTION_NAME;
        $fields['solution_version'] = static::SOLUTION_VERSION;
        $fields['integrator_name'] = static::INTEGRATOR_NAME;
        $fields['integrator_version'] = static::INTEGRATOR_VERSION;

        $narrativeTextMaxLength = 81;
        if (array_key_exists(static::FIELD_PAYMENT_METHOD, $fields)) {
            switch ($fields[static::FIELD_PAYMENT_METHOD]) {
                case Api::PAYMENT_METHOD_CREDIT_CARD_PPAN:
                    unset(
                        $fields[static::FIELD_BIC],
                        $fields[static::FIELD_IBAN]
                    );
                    $fields['clearingtype'] = 'cc';
                    break;
                case Api::PAYMENT_METHOD_DIRECT_DEBIT_SEPA:
                    $fields['clearingtype'] = 'elv';
                    break;
                case Api::PAYMENT_METHOD_GIROPAY:
                    $fields['clearingtype'] = 'sb';
                    $fields['onlinebanktransfertype'] = 'GPY';
                    break;
                case Api::PAYMENT_METHOD_PAYDIREKT:
                    unset(
                        $fields[static::FIELD_BIC],
                        $fields[static::FIELD_IBAN]
                    );
                    $fields['clearingtype'] = 'wlt';
                    $fields['wallettype'] = 'PDT';
                    $narrativeTextMaxLength = 37;
                    break;
                default:
                    throw new InvalidArgumentException('Invalid payment method "' . $fields[static::FIELD_PAYMENT_METHOD] . '"');
            }

            unset($fields[Api::FIELD_PAYMENT_METHOD]);
        }

        // remove some fields from request
        $fields = array_diff_key(
            $fields,
            array_flip([
                Api::FIELD_CUSTOMER_MESSAGE,
                Api::FIELD_ERROR_CODE,
                Api::FIELD_ERROR_MESSAGE,
                'completed_status',
            ])
        );

        // validate fields
        if (array_key_exists(static::FIELD_NARRATIVE_TEXT, $fields) && $narrativeTextMaxLength < strlen($fields[static::FIELD_NARRATIVE_TEXT])) {
            throw new InvalidArgumentException('Field "narrative_text" must not be longer than ' . $narrativeTextMaxLength . ' characters.');
        }

        $request = new Request('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false === ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return (string)$response->getBody();
    }

    /**
     * @param string $bodyContents
     *
     * @return array
     */
    private function decodeBody($bodyContents)
    {
        $result = array();
        foreach (explode("\n", $bodyContents) as $responseLine) {
            $responseLine = urldecode($responseLine);
            $delimiterPosition = strpos($responseLine, '=');
            if (false === $delimiterPosition) {
                continue;
            }
            $result[substr($responseLine, 0, $delimiterPosition)] = trim(substr($responseLine, $delimiterPosition + 1));
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return 'https://api.pay1.de/post-gateway/';
    }
}
