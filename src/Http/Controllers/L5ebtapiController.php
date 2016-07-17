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
    private $api_url = 'https://api.ebay.com/ws/api.dll';
    private $api_verify_ssl = true;
    private $api_compatibility_level = '971';
    private $api_error_language = 'US';
    private $api_warning_level = 'LOW';
    private $api_runame = '';
    private $api_user_token = '';
    private $api_dev_id;
    private $api_app_id;
    private $api_cert_id;
    private $api_site_id = '0';

    /**
     * Create a new L5ebtapiController instance.
     *
     * @return void
     */
    public function __construct($attributes)
    {
        if (isset($attributes['api_url']) && strlen($attributes['api_url']) > 0) {

            $this->api_url = $attributes['api_url'];

        }
        if (isset($attributes['api_verify_ssl']) && $attributes['api_verify_ssl'] == true || $attributes['api_verify_ssl'] == false) {

            $this->api_verify_ssl = $attributes['api_verify_ssl'];

        } else {

            $this->api_verify_ssl = true;

        }
        if (isset($attributes['api_compatibility_level']) && strlen($attributes['api_compatibility_level']) > 0) {

            $this->api_compatibility_level = $attributes['api_compatibility_level'];

        }
        if (isset($attributes['api_error_language']) && strlen($attributes['api_error_language']) > 0) {

            $this->api_error_language = $attributes['api_error_language'];

        }
        if (isset($attributes['api_warning_level']) && strlen($attributes['api_warning_level']) > 0) {

            $this->api_warning_level = $attributes['api_warning_level'];

        }
        if (isset($attributes['api_runame']) && empty($attributes['api_runame']) || is_null($attributes['api_runame'])) {

            $this->api_runame = '';

        } else {

            $this->api_runame = $attributes['api_runame'];

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
        if (isset($attributes['api_site_id']) && strlen($attributes['api_site_id']) > 0) {

            $this->api_site_id = $attributes['api_site_id'];

        }

    }// END constructor

    /**
     * Method: getEbayOfficialTime() - get the eBay official time API call.
     *
     * @param $multiPartImageData the image data. Acceptable formats (jpg, gif, png)
     * @param $image_name The name the uploaded image will have on the eBay Picture Services server.
     * @return string The URL of the image on the eBay Picture Services server OR an error message string if an error
     * occurs.
     */
    public function getEbayOfficialTime()
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                        <GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                        <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                        </RequesterCredentials>
                        </GeteBayOfficialTimeRequest>​​​';

        $responseXml = L5ebtapiController::request('GeteBayOfficialTime', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getEbayOfficialTime() 404 Not Found');

            $message = ['Error: 404 Not Found. Please verify all eBay API settings are correct and try again.'];

            return $message;

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getEbayOfficialTime() Error sending request' .
                'the XML response is an empty string');

            $message = ['Error: The XML response is an empty string. Please verify all eBay' .
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

            if ($xml->Ack && ((string)$xml->Ack == 'Success')) {

                if ($xml->Timestamp) {

                    $timestamp = (string)$xml->Timestamp;

                } else {

                    $timestamp = 'Error: An error occurred While processing the getEbayOfficialTime() request. Please' .
                        'verify all eBay API settings are correct and try the request again.';

                }

            } else {

                $timestamp = 'Error: An error occurred While processing the getEbayOfficialTime() request. Please' .
                    'verify all eBay API settings are correct and try the request again.';

            }

        }

        return $timestamp;

    }// END getEbayOfficialTime()

    /**
     * Method: uploadSiteHostedPictures($multiPartImageData, $image_name) - Upload an image to the eBay Picture Service.
     *
     * @param $multiPartImageData the image data. Acceptable formats (jpg, gif, png)
     * @param $image_name The name the uploaded image will have on the eBay Picture Services server.
     * @return string The URL of the image on the eBay Picture Services server OR an error message string if an error
     * occurs.
     */
    public function uploadSiteHostedPictures($multiPartImageData, $image_name)
    {

        ///Build the request XML request which is first part of multi-part POST
        $xmlReq = '<?xml version="1.0" encoding="utf-8"?>
                           <UploadSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                           <Version>' . $this->api_compatibility_level . '</Version>
                           <RequesterCredentials>
                           <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                           </RequesterCredentials>
                           <PictureName>' . $image_name . '</PictureName>
                           <PictureSet>Standard</PictureSet>
                           <PictureUploadPolicy>ClearAndAdd</PictureUploadPolicy>
                           </UploadSiteHostedPicturesRequest>';

        $boundary = "==Multipart_Boundary_x" . md5(mt_rand()) . "x";
        $CRLF = "\r\n";

        // The complete POST consists of an XML request plus the binary image separated by boundaries
        $firstPart = '';
        $firstPart .= "--" . $boundary . $CRLF;
        $firstPart .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
        $firstPart .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
        $firstPart .= $xmlReq;
        $firstPart .= $CRLF;

        //$secondPart = '';
        $secondPart = "--" . $boundary . $CRLF;
        $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
        $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
        $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
        $secondPart .= $multiPartImageData;
        $secondPart .= $CRLF;
        $secondPart .= "--" . $boundary . "--" . $CRLF;

        $request_body = $firstPart . $secondPart;

        $respXmlStr = L5ebtapiController::multiPartRequest('UploadSiteHostedPictures', $request_body, $boundary);   // send multi-part request and get string XML response

        if (stristr($respXmlStr, 'HTTP 404')) {

            Log::error('eBay API Call: uploadSiteHostedPictures() 404 Not Found');

            return 'Error: 404 Not Found. Please verify all eBay API settings are correct and try again.';

        }

        if ($respXmlStr == '') {

            Log::error('eBay API Call: uploadSiteHostedPictures() Error sending request' .
                'the XML response is an empty string');

            return 'Error: The XML response is an empty string. Please verify all eBay API settings are correct and' .
            'try again.';

        }

        $respXmlObj = simplexml_load_string($respXmlStr);

        if($respXmlObj->SiteHostedPictureDetails->FullURL) {

            $picURL = (string)$respXmlObj->SiteHostedPictureDetails->FullURL;

        }
        else {

            $picURL = 'Error: An error occurred While processing the uploadSiteHostedPictures() request. Please' .
                'verify all eBay API settings are correct and all input is correct and then try the request again.';

        }

        return $picURL;

    }// END of uploadSiteHostedPictures($multiPartImageData, $image_name)

    /**
     * Method: request($call_name, $request_body) - Make an eBay API request.
     *
     * @param $call_name the eBay API call name
     * @param $request_body the body of the request
     * @return mixed
     */
    public function request($call_name, $request_body)
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
                    'X-EBAY-API-CALL-NAME' => $call_name
                ),
                'body' => $request_body
            ));

        } catch (\GuzzleHttp\Exception\ServerException $e) {

            $response = $e->getResponse();

            Log::warning($response->getBody()->getContents());

        }

        $body = $response->getBody()->getContents();

        return $body;

    }// END - request($call_name, $request_body)

    /**
     * Method: multiPartRequest($call_name, $request_body, $boundary) - Make an eBay API multi-part request.
     *
     * @param $call_name the eBay API call name
     * @param $request_body the body of the request
     * @param $boundary the boundary for the multi-part data
     * @return mixed
     */
    public function multiPartRequest($call_name, $request_body, $boundary)
    {

            $client = new Client();

            try {

                $response = $client->post($this->api_url, array(
                    'verify' => $this->api_verify_ssl,
                    'headers' => array(
                        'HTTP' => '1.0',
                        'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
                        'Content-Length' => strlen($request_body),
                        'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->api_compatibility_level,
                        'X-EBAY-API-DEV-NAME' => $this->api_dev_id,
                        'X-EBAY-API-APP-NAME' => $this->api_app_id,
                        'X-EBAY-API-CERT-NAME' => $this->api_cert_id,
                        'X-EBAY-API-SITEID' => $this->api_site_id,
                        'X-EBAY-API-CALL-NAME' => $call_name
                    ),
                    'body' => $request_body
                ));

            } catch (\GuzzleHttp\Exception\ServerException $e) {

                $response = $e->getResponse();

                Log::warning($response->getBody()->getContents());

            }

            return $response->getBody()->getContents();

    } // END - multiPartRequest($call_name, $request_body, $boundary)


}// END of class L5ebtapiController