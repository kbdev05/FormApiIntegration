<?php

namespace Drupal\land_price_calculator;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\GuzzleException;

class CalculatorApi
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param $http_client_factory \Drupal\Core\Http\ClientFactory
     */
    public function __construct($http_client_factory)
    {
        $this->client = $http_client_factory;
    }

    /**
     * Endpoint for calculating price.
     *
     *
     * @return array
     */
    public function rates($json)
    {
        try {
            $apikey = Settings::get('planning_portal_rate_generator_apikey', 'daa8fd44cf3b4bf4a8ed20bac2bd1312');
            
            $req = $this->client->fromOptions([
                'body' => $json,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'api-key' => $apikey,
                    // dev 'api-key' => 'ff0bf32a79494f4dbf716b9a0a5bb2f1',
                    //uat Api 'api-key' => 'daa8fd44cf3b4bf4a8ed20bac2bd1312',
                    //prd 'api-key' => '175c088d542f4e108d12995c42241b15',
                ],
            ]);
            
            $response = $req->post(Settings::get(
                    'planning_portal_rate_generator',
                    'https://api-uat.apps1.nsw.gov.au/planning/RateGeneratorAPI/v1/RateGenerator'
                ));
            return Json::decode($response->getBody());
        } catch (GuzzleException $error) {
            $response = $error->getResponse();
            // Get the info returned from the remote server.
            watchdog_exception('land_price_calculator', $error, $error->getMessage());

            return Json::decode($response->getBody()->getContents());
        }
    }

    /**
     * End point for addresslist.
     *
     *
     * @return array
     */
    public function addresslist($a)
    {
        $req = $this->client->fromOptions();
        $response = $req->get(('https://api.apps1.nsw.gov.au/planning/viewersf/V1/ePlanningApi/address'), [
      'query' => [
        'a' => $a,
      ],
    ]);

        return Json::decode($response->getBody()->getContents());
    }

    /**
     * Endpoint for getting project details.
     *
     *
     * @return array
     */
    public function projectdetails($propertyid)
    {
        try {
            $req = $this->client->fromOptions();
            //         $response = $req->get(Settings::get(
            //             'planning_portal_layers',
            //             'https://api.apps1.nsw.gov.au/planning/viewersf/V1/ePlanningApi/layerintersect'
            //     ), [
            //   'query' => [
            //     'type' => 'property',
            //     'id' => $propertyid,
            //     'layers' => 'default',
            //   ],
            // ]);

            $response = $req->get((
                'https://api.apps1.nsw.gov.au/planning/viewersf/V1/ePlanningApi/layerintersect'
                ), [
              'query' => [
                'type' => 'property',
                'id' => $propertyid,
                'layers' => 'default',
              ],
            ]);

            return Json::decode($response->getBody()->getContents());
        } catch (GuzzleException $error) {
            $response = $error->getResponse();
            // Get the info returned from the remote server.
            watchdog_exception('land_price_calculator', $error, $error->getMessage());

            return Json::decode($response->getBody()->getContents());
        }
    }
}
