<?php

namespace l5ebtapi\l5ebtapi\Http\Controllers;

//use Illuminate\Http\Request;

use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

//use Intervention\Image\Facades\Image;

class L5ebtapiController extends Controller
{

    // Declare private variables
    private $api_url = '';
    private $api_verify_ssl = true;
    private $api_compatibility_level = '';
    private $api_error_language = 'US';
    private $api_warning_level = 'LOW';
    private $api_user_token = '';
    private $api_dev_id;
    private $api_app_id;
    private $api_cert_id;
    private $api_site_id;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($attributes)
    {

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
        if (isset($attributes['api_compatibility_level']) && empty($attributes['api_compatibility_level']) || is_null($attributes['api_compatibility_level'])) {

            $this->api_compatibility_level = '971';

        } else {

            $this->api_compatibility_level = $attributes['api_compatibility_level'];

        }
        if (isset($attributes['api_error_language']) && empty($attributes['api_error_language']) || is_null($attributes['api_error_language'])) {

            $this->api_error_language = 'US';

        } else {

            $this->api_error_language = $attributes['api_error_language'];

        }
        if (isset($attributes['api_warning_level']) && empty($attributes['api_warning_level']) || is_null($attributes['api_warning_level'])) {

            $this->api_warning_level = 'LOW';

        } else {

            $this->api_warning_level = $attributes['api_warning_level'];

        }
        if (isset($attributes['api_user_token']) && empty($attributes['api_user_token']) || is_null($attributes['api_user_token'])) {

            $this->api_user_token = '';

        } else {

            $this->api_user_token = $attributes['api_user_token'];

        }
        if (isset($attributes['api_dev_id'])) {

            $this->api_dev_id = $attributes['api_dev_id'];

        }
        if (isset($attributes['api_app_id'])) {

            $this->api_app_id = $attributes['api_app_id'];

        }
        if (isset($attributes['api_cert_id'])) {

            $this->api_cert_id = $attributes['api_cert_id'];

        }
        if (isset($attributes['api_site_id']) && empty($attributes['api_site_id']) || is_null($attributes['api_site_id'])) {

            $this->api_site_id = '0';

        } else {

            $this->api_site_id = $attributes['api_site_id'];

        }

    }// END constructor

    public function getEbayOfficialTime()
    {
        //$timestamp = '';

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                        <GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                        <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                        </RequesterCredentials>
                        </GeteBayOfficialTimeRequest>​​​';

        $responseXml = L5ebtapiController::request('GeteBayOfficialTime', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: FetchToken: User: ' . Auth::user()->id . ' 404 Not Found');

            $message = ['error' => '404 Not Found. Please verify all eBay API settings are correct and try again.'];

            return $message;

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: FetchToken: User: ' . Auth::user()->id . ' Error sending request' .
                'XML response is an empty string');

            $message = ['error' => 'Error sending request XML response is an empty string. Please verify all eBay' .
                'API settings are correct and try again.'];

            return $message;

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        //get any error nodes
        $errors = $responseDoc->getElementsByTagName('Errors');

        //if there are error nodes return the error message (array)
        if ($errors->length > 0) {

            $code = $errors->item(0)->getElementsByTagName('ErrorCode');

            $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

            $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

            //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
            if (count($longMsg) > 0) {

                Log::warning('eBay API Call: getEbayOfficialTime(). Short message: ' .
                    $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                Log::warning('eBay API Call: getEbayOfficialTime(). Long message: ' .
                    $longMsg->item(0)->nodeValue);

            } else {

                Log::warning('eBay API Call: getEbayOfficialTime(). Short message: ' .
                    $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

            }

            return 'An error occurred while processing the getEbayOfficialTime() request. Please verify all eBay' .
            'API settings are correct and try the request again.';

        } else //no errors so return the eBay official time as a (string)
        {
            $xml = simplexml_load_string($responseDoc->saveXML());

            if ($xml->Timestamp) {

                $timestamp = (string) $xml->Timestamp;

            }
            else {

                $timestamp = 'An error occurred while processing the getEbayOfficialTime() request. Please verify all' .
                    'eBay API settings are correct and try the request again.';

            }

        }

        return $timestamp;

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
                    'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->api_compatibility_level,
                    'X-EBAY-API-DEV-NAME' => $this->api_dev_id,
                    'X-EBAY-API-APP-NAME' => $this->api_app_id,
                    'X-EBAY-API-CERT-NAME' => $this->api_cert_id,
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