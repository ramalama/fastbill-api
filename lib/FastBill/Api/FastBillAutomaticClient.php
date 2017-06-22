<?php

namespace FastBill\Api;

use FastBill\Model\Article;
use FastBill\Model\Subscription;
use GuzzleHttp\ClientInterface;

class FastBillAutomaticClient extends AbstractFastBillClient
{
    public function __construct(ClientInterface $guzzleClient, array $options)
    {
        $guzzleClient->setBaseUrl("https://automatic.fastbill.com/");
        parent::__construct($guzzleClient, $options);
    }

    /**
     * @return FastBill\Model\Subscription
     */
    public function createSubscription(Subscription $subscription)
    {
        $requestBody = [
            'SERVICE' => 'subscription.create',
            'DATA' => $subscription->serializeJSONXML()
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

        $subscription->setSubscriptionId($jsonResponse->RESPONSE->SUBSCRIPTION_ID);

        return $subscription;
    }

    /**
     * @return FastBill\Model\Subscription
     */
    public function updateSubscription(Subscription $subscription)
    {
        $requestBody = [
            'SERVICE' => 'subscription.update',
            'DATA' => $subscription->serializeJSONXML()
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

        return $subscription;
    }

    /**
     * @return FastBill\Model\Subscription
     */
    public function cancelSubscription(Subscription $subscription)
    {
        $requestBody = [
            'SERVICE' => 'subscription.cancel',
            'DATA' => $subscription->serializeJSONXML()
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

        $subscription->setCancellationDate($jsonResponse->RESPONSE->CANCELLATION_DATE);

        return $subscription;
    }

    /**
     * @return FastBill\Model\Subscription
     */
    public function reactivateSubscription(Subscription $subscription)
    {
        $requestBody = [
            'SERVICE' => 'subscription.reactivate',
            'DATA' => $subscription->serializeJSONXML()
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

        return $subscription;
    }

    public function getSubscriptions(array $filters = [])
    {
        $requestBody = (object) [
            'SERVICE' => 'subscription.get'
        ];

        $this->filtersToXml($filters, $requestBody);

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key SUBSCRIPTIONS is not set';

                return isset($response->SUBSCRIPTIONS);
            }
        );

        $subscriptions = [];
        foreach ($jsonResponse->RESPONSE->SUBSCRIPTIONS as $xmlSubscription) {
            $subscriptions[] = Subscription::fromObject($xmlSubscription);
        }

        return $subscriptions;
    }

    public function getArticles(array $filters = [])
    {
        $requestBody = (object) [
            'SERVICE' => 'article.get'
        ];

        $this->filtersToXml($filters, $requestBody);

        $jsonResponse = $this->validateResponse(
            $this->dispatchRequest(
                $this->createRequest('POST', '/', $requestBody)
            ),
            function ($response, &$msg) {
                $msg = 'key ARTICLES is not set';

                return isset($response->ARTICLES);
            }
        );

        $articles = [];
        foreach ($jsonResponse->RESPONSE->ARTICLES as $xmlSubscription) {
            $articles[] = Article::fromObject($xmlSubscription);
        }

        return $articles;
    }
}
