<?php

namespace l5ebtapi\l5ebtapi\Http\Controllers;

//use Illuminate\Http\Request;

//use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

//use Intervention\Image\Facades\Image;

class L5ebtapiController extends Controller
{

    // Declare private variables
    private $api_url = '';
    private $api_verify_ssl = true;
    private $api_compat_lvl = '';
    private $api_dev_name;
    private $api_app_name;
    private $api_cert_name;
    private $api_site_id;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($attributes)
    {

        parent::__construct($attributes);

        if (isset($attributes['api_url']) && empty($attributes['api_url']) || is_null($attributes['api_url'])) {

            $this->api_url = 'https://api.ebay.com/ws/api.dll';

        } else {

            $this->api_url = $attributes['api_url'];

        }
        if (isset($attributes['api_verify_ssl']) && $attributes['api_verify_ssl'] == true || $attributes['api_verify_ssl'] == false) {

            $this->api_verify_ssl = $attributes['api_verify_ssl'];

        } else {

            $this->api_verify_ssl = true;

        }
        if (isset($attributes['api_compat_lvl']) && empty($attributes['api_compat_lvl']) || is_null($attributes['api_compat_lvl'])) {

            $this->api_compat_lvl = '971';

        } else {

            $this->api_compat_lvl = $attributes['api_compat_lvl'];

        }
        if (isset($attributes['api_dev_name'])) {

            $this->api_dev_name = $attributes['api_dev_name'];

        }
        if (isset($attributes['api_app_name'])) {

            $this->api_app_name = $attributes['ebay_api_app_name'];

        }
        if (isset($attributes['api_cert_name'])) {

            $this->api_cert_name = $attributes['ebay_api_cert_name'];

        }
        if (isset($attributes['api_site_id']) && empty($attributes['api_site_id']) || is_null($attributes['api_site_id'])) {

            $this->api_site_id = '0';

        } else {

            $this->api_site_id = $attributes['api_site_id'];

        }

    }// END constructor

    public function getEbayOfficialTime()
    {

        return 'This will return the eBay official time in future release.';

    }// END getEbayOfficialTime()

    /**
     * Make an eBay API request
     *
     * @param $method the eBay API method
     * @param $request_body the body of the request
     * @return mixed
     */
    public function request($method, $request_body)
    {

        $client = new Client();

        try {

            $response = $client->post($this->api_url, array(
                'verify' => $this->api_verify_ssl,
                'headers' => array(
                    'Content-Type' => 'text/xml',
                    'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->api_compat_lvl,
                    'X-EBAY-API-DEV-NAME' => $this->api_dev_name,
                    'X-EBAY-API-APP-NAME' => $this->api_app_name,
                    'X-EBAY-API-CERT-NAME' => $this->api_cert_name,
                    'X-EBAY-API-SITEID' => $this->api_site_id,
                    'X-EBAY-API-CALL-NAME' => $method
                ),
                'body' => $request_body
            ));

        } catch (\GuzzleHttp\Exception\ServerException $e) {

            $response = $e->getResponse();

            Log::warning($response->getBody()->getContents());

        }

        $body = $response->getBody()->getContents();

        return $body;

    }

}