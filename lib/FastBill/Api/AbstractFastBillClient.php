<?php

namespace FastBill\Api;

use FastBill\Model\Customer;
use FastBill\Model\Expense;
use FastBill\Model\Invoice;
use FastBill\Model\Project;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use RuntimeException;

abstract class AbstractFastBillClient extends AbstractClient
{
    protected $apiKey, $email;

    /**
     * @param ClientInterface $httpClient
     * @param array $options
     */
    public function __construct(ClientInterface $httpClient, array $options)
    {
        parent::__construct($httpClient);

        if (!array_key_exists('apiKey', $options) || empty($options['apiKey'])) {
            throw new InvalidArgumentException("the key: 'apiKey' has to be set on options");
        }

        if (!array_key_exists('email', $options) || empty($options['email'])) {
            throw new InvalidArgumentException("the key: 'email' has to be set on options");
        }

        $this->apiKey = $options['apiKey'];
        $this->email = $options['email'];
    }

    /**
     * @param Invoice $invoice
     * @return Invoice
     */
    public function createInvoice(Invoice $invoice)
    {
        $requestBody = [
            'SERVICE' => 'invoice.create',
            'DATA' => $invoice->serializeJSONXML()
        ];

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'STATUS is not equal to success';

                return isset($response->STATUS) && $response->STATUS === 'success';
            }
        );

        $invoice->setInvoiceId($jsonResponse->RESPONSE->INVOICE_ID);

        return $invoice;
    }

    /**
     * Creates a customer (not matter if it exists)
     *
     * the object as parameter is returned as result but the new id will be set (or overridden)
     *
     * @param Customer $customer
     * @return \FastBill\Model\Customer
     */
    public function createCustomer(Customer $customer)
    {
        $requestBody = [
            'SERVICE' => 'customer.create',
            'DATA' => $customer->serializeJSONXML()
        ];

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key STATUS is not equal to success';

                return isset($response->STATUS) && $response->STATUS === 'success';
            }
        );

        $customer->setCustomerId($jsonResponse->RESPONSE->CUSTOMER_ID);

        return $customer;
    }

    public function getCustomers(array $filters = [], array $props = [])
    {
        $requestBody = $this->createRequestBody('customer.get', $filters, $props);

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key CUSTOMERS is not set';

                return isset($response->CUSTOMERS);
            }
        );

        $customers = [];
        foreach ($jsonResponse->RESPONSE->CUSTOMERS as $xmlCustomer) {
            $customers[] = Customer::fromObject($xmlCustomer);
        }

        return $customers;
    }

    protected function filtersToXml(array $filters, \stdClass $requestBody)
    {
        foreach ($filters as $name => $value) {
            if (!empty($value)) {
                if (!isset($requestBody->FILTER)) {
                    $requestBody->FILTER = new \stdClass();
                }

                $requestBody->FILTER->{mb_strtoupper($name)} = $value;
            }
        }
    }

    protected function createRequestBody($service, array $filters = [], array $props = [])
    {
        $props['service'] = $service;

        $requestBody = new \stdClass;

        $this->filtersToXml($filters, $requestBody);

        foreach ($props as $prop => $value) {
            $requestBody->{mb_strtoupper($prop)} = $value;
        }

        return $requestBody;
    }

    public function getInvoices(array $filters = [], array $props = [])
    {
        $requestBody = $this->createRequestBody('invoice.get', $filters, $props);

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key INVOICES is not set';

                return isset($response->INVOICES);
            }
        );

        $invoices = [];
        foreach ($jsonResponse->RESPONSE->INVOICES as $xmlInvoice) {
            $invoices[] = Invoice::fromObject($xmlInvoice);
        }

        return $invoices;
    }

    public function getProjects(array $filters = [], array $props = [])
    {
        $requestBody = $this->createRequestBody('project.get', $filters, $props);

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key PROJECTS is not set';

                return isset($response->PROJECTS);
            }
        );

        $projects = [];
        foreach ($jsonResponse->RESPONSE->PROJECTS as $xmlProject) {
            $projects[] = Project::fromObject($xmlProject);
        }

        return $projects;
    }

    public function getExpenses(array $filters = [], array $props = [])
    {
        $requestBody = $this->createRequestBody('expense.get', $filters, $props);

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key EXPENSES is not set';

                return isset($response->EXPENSES);
            }
        );

        $expenses = [];
        foreach ($jsonResponse->RESPONSE->EXPENSES as $xml) {
            $expenses[] = Expense::fromObject($xml);
        }

        return $expenses;
    }

    protected function expandUrl($relativeResource)
    {
        return '/api/1.0/api.php';
    }

    /**
     * @param \stdClass $jsonResponse
     * @param callable $validateResponse
     * @return the whole response including REQUEST and RESPONSE keys
     */
    protected function validateResponse(\stdClass $jsonResponse, \Closure $validateResponse)
    {
        //$stringified = JSONConverter::create()->stringify($jsonResponse, JSONConverter::PRETTY_PRINT);

        if (!isset($jsonResponse->RESPONSE)) {
            throw new RuntimeException('The property RESPONSE is expected in jsonResponse.');
            //throw new RuntimeException('The property response is expected in jsonResponse. Got: '.$stringified);
        }

        $msg = null;
        if (!$validateResponse($jsonResponse->RESPONSE, $msg)) {
            throw BadRequestException::fromResponse($jsonResponse);
        }

        return $jsonResponse;
    }
}
