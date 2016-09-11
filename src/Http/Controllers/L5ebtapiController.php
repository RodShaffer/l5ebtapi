<?php

namespace rodshaffer\l5ebtapi\Http\Controllers;

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
    private $api_warning_level = 'Low';
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
    public function __construct()
    {

    }// END constructor

    public function init($attributes)
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

    }

    /**
     * Method: getEbayOfficialTime() - get the eBay official time API call.
     *
     * @return Array The eBay official time key = 'eBay_Official_Time' OR Array with a key = 'Error:' and Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try the request again.']
     */
    public function getEbayOfficialTime()
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                        <GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                        <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                        </RequesterCredentials>

                        <!-- Standard Input Fields -->
                         <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                         <Version>' . $this->api_compatibility_level . '</Version>
                         <WarningLevel>' . $this->api_warning_level . '</WarningLevel>

                        </GeteBayOfficialTimeRequest>​​​';

        $responseXml = L5ebtapiController::request('GeteBayOfficialTime', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getEbayOfficialTime() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getEbayOfficialTime() Error sending request' .
                'the XML response is an empty string');

            return ['Error:' => 'The XML response is an empty string. Please verify all eBay API settings are correct' .
                'and try again.'];

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

            return ['Error:' => 'An error occurred while processing the getEbayOfficialTime() request.' .
                'Please verify all eBay API settings are correct and try the request again.'];

        } else //no errors so return the eBay official time as a (string)
        {
            $xml = simplexml_load_string($responseDoc->saveXML());

            if ($xml->Ack && ((string)$xml->Ack == 'Success')) {

                if ($xml->Timestamp) {

                    return ['eBay_Official_Time' => (string)$xml->Timestamp];

                } else {

                    $timestamp = ['Error:' => 'An error occurred While processing the getEbayOfficialTime() request.' .
                        'Please verify all eBay API settings are correct and try the request again.'];

                }

            } else {

                $timestamp = ['Error:' => 'An error occurred While processing the getEbayOfficialTime() request.' .
                    'Please verify all eBay API settings are correct and try the request again.'];

            }

        }

        return $timestamp;

    }// END getEbayOfficialTime()

    /**
     * Method: getEbayDetails(array $detailNames) - Retrieves eBay IDs and codes for Example shipping service codes, enumerated data
     * for Example payment methods, and other common eBay meta-data.
     *
     * @return SimpleXML Object OR Array with a key = 'Error:' and Value = 'The error message'
     */
    public function getEbayDetails(array $detailNames)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                         <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                         <RequesterCredentials>
                         <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                         </RequesterCredentials>';

                         /* Call-specific Input Fields */
                         foreach($detailNames as $detailName) {

                             $request_body .= '<DetailName>' . $detailName . '</DetailName>
                             ';

                         }

                         /* Standard Input Fields */
        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                         <Version>' . $this->api_compatibility_level . '</Version>
                         <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                         </GeteBayDetailsRequest>​';

        $responseXml = L5ebtapiController::request('GeteBayDetails', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getEbayDetails() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getEbayDetails() Error sending request. The XML response is an empty string');

            return ['Error:' => 'There was an error sending request the XML response is an empty string.' .
                'Please verify all eBay API settings are correct and try the request again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        $ack = $responseDoc->getElementsByTagName('Ack');

        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Failure' || $ack->item(0)->nodeValue == 'Warning') {

            //get any error nodes
            $errors = $responseDoc->getElementsByTagName('Errors');

            //if there are error nodes return the error message (array)
            if ($errors->length > 0) {

                $code = $errors->item(0)->getElementsByTagName('ErrorCode');

                $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

                $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

                if ($ack->item(0)->nodeValue == 'Failure') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::error('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::error('eBay API Call: getEbayDetails() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' .
                            $shortMsg->item(0)->nodeValue . ' : Long Message: ' . $longMsg->item(0)->nodeValue];

                    } else {

                        Log::error('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' . $shortMsg->item(0)->nodeValue];

                    }

                }
                if ($ack->item(0)->nodeValue == 'Warning') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::warning('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::warning('eBay API Call: getEbayDetails() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                    } else {

                        Log::warning('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                }

            }


        }
        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Success' || $ack->item(0)->nodeValue == 'Warning') {

            $xml = simplexml_load_string($responseDoc->saveXML());

            return $xml;

        } else {

            Log::error('eBay API Call: getEbayDetails(): An error occurred during the getEbayDetails() ebay API' .
                'call. Please verify all API settings are correct and then try the request again.');

            return ['Error:' => 'An error occurred during the getEbayDetails() ebay API call. Please verify all' .
                'API settings are correct and then try the request again. Additionally More information about the' .
                'error may be available in the error.log file.'];

        }

    }// END getEbayDetails()
    
    /**
     * Method: getCategoryFeatures(array $attributes) - returns information that describes the feature and value
     * settings that apply to the set of eBay categories.
     *
     * @return SimpleXML Object OR Array with a key = 'Error:' and Value = 'The error message'
     */
    public function getCategoryFeatures(array $attributes)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                        <GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                         <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                         </RequesterCredentials>
                         ';

                        /* Call-specific Input Fields */
                        if(isset($attributes['AllFeaturesForCategory'])) {
                            $request_body .= '<AllFeaturesForCategory>' . $attributes['AllFeaturesForCategory'] . '</AllFeaturesForCategory>
                            ';
                        }
                        if(isset($attributes['CategoryID'])) {
                            $request_body .= '<CategoryID>' . $attributes['CategoryID'] . '</CategoryID>
                            ';
                        }
                        if(isset($attributes['FeatureID'])) {

                            foreach($attributes['FeatureID'] as $featureID) {

                                $request_body .= '<FeatureID>' . $featureID . '</FeatureID>
                                ';

                            }
                        }
                        if(isset($attributes['LevelLimit'])) {

                            $request_body .= '<LevelLimit>' . $attributes['LevelLimit'] . '</LevelLimit>
                            ';

                        }
                        if(isset($attributes['ViewAllNodes'])) {

                            $request_body .= '<ViewAllNodes>' . $attributes['ViewAllNodes'] . '</ViewAllNodes>
                            ';

                        }

                        /* Standard Input Fields */
                        if(isset($attributes['DetailLevel'])) {

                            foreach($attributes['DetailLevel'] as $detailLevel) {

                                $request_body .= '<DetailLevel>' . $detailLevel . '</DetailLevel>
                                ';

                            }
                        }
                        if(isset($attributes['MessageID'])) {

                            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>
                            ';

                        }
                        if(isset($attributes['OutputSelector'])) {

                            foreach($attributes['OutputSelector'] as $outputSelector) {

                                $request_body .= '<OutputSelector>' . $outputSelector . '</OutputSelector>
                                ';

                            }
                        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                         <Version>' . $this->api_compatibility_level . '</Version>
                         <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                         </GetCategoryFeaturesRequest>​';

        $responseXml = L5ebtapiController::request('GetCategoryFeatures', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getCategoryFeatures() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getCategoryFeatures() Error sending request. The XML response is an empty string');

            return ['Error:' => 'There was an error sending request the XML response is an empty string.' .
                'Please verify all eBay API settings are correct and try the request again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        $ack = $responseDoc->getElementsByTagName('Ack');

        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Failure' || $ack->item(0)->nodeValue == 'Warning') {

            //get any error nodes
            $errors = $responseDoc->getElementsByTagName('Errors');

            //if there are error nodes return the error message (array)
            if ($errors->length > 0) {

                $code = $errors->item(0)->getElementsByTagName('ErrorCode');

                $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

                $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

                if ($ack->item(0)->nodeValue == 'Failure') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::error('eBay API Call: getCategoryFeatures() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::error('eBay API Call: getCategoryFeatures() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' .
                            $shortMsg->item(0)->nodeValue . ' : Long Message: ' . $longMsg->item(0)->nodeValue];

                    } else {

                        Log::error('eBay API Call: getCategoryFeatures() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' . $shortMsg->item(0)->nodeValue];

                    }

                }
                if ($ack->item(0)->nodeValue == 'Warning') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::warning('eBay API Call: getCategoryFeatures() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::warning('eBay API Call: getCategoryFeatures() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                    } else {

                        Log::warning('eBay API Call: getCategoryFeatures() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                }

            }
            
        }
        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Success' || $ack->item(0)->nodeValue == 'Warning') {

            $xml = simplexml_load_string($responseDoc->saveXML());

            return $xml;

        } else {

            Log::error('eBay API Call: getCategoryFeatures(): An error occurred during the getCategoryFeatures() ebay API' .
                'call. Please verify all API settings are correct and then try the request again.');

            return ['Error:' => 'An error occurred during the getCategoryFeatures() ebay API call. Please verify all' .
                'API settings are correct and then try the request again. Additionally More information about the' .
                'error may be available in the error.log file.'];

        }

    }// END getCategoryFeatures()

    /**
     * Method: getItem(array $attributes) - Retrieves the eBay item detail for the given eBay item id.
     *
     * @return SimpleXML Object OR Array with a key = 'Error:' and Value = 'The error message'
     */
    public function getItem(array $attributes)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                        <GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                        <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                        </RequesterCredentials>';
                        
                        /* Call-specific Input Fields */
                        if(isset($attributes['IncludeItemCompatibilityList'])) {

                            $request_body .= '<IncludeItemCompatibilityList>' . $attributes['IncludeItemCompatibilityList'] . ' </IncludeItemCompatibilityList>
                            ';

                        }
                        if(isset($attributes['IncludeItemSpecifics'])) {

                            $request_body .= '<IncludeItemSpecifics>' . $attributes['IncludeItemSpecifics'] . ' </IncludeItemSpecifics>
                            ';

                        }
                        if(isset($attributes['IncludeTaxTable'])) {

                            $request_body .= '<IncludeTaxTable>' . $attributes['IncludeTaxTable'] . ' </IncludeTaxTable>
                            ';

                        }
                        if(isset($attributes['IncludeWatchCount'])) {

                            $request_body .= '<IncludeWatchCount>' . $attributes['IncludeWatchCount'] . ' </IncludeWatchCount>
                            ';

                        }
                        if(isset($attributes['ItemID'])) {

                            $request_body .= '<ItemID>' . $attributes['ItemID'] . ' </ItemID>
                            ';

                        }
                        if(isset($attributes['SKU'])) {

                            $request_body .= '<SKU>' . $attributes['SKU'] . ' </SKU>
                            ';

                        }
                        if(isset($attributes['TransactionID'])) {

                            $request_body .= '<TransactionID>' . $attributes['TransactionID'] . ' </TransactionID>
                            ';

                        }
                        if(isset($attributes['VariationSKU'])) {

                            $request_body .= '<VariationSKU>' . $attributes['VariationSKU'] . ' </VariationSKU>
                            ';

                        }
                        if(isset($attributes['VariationSpecifics'])) {

                            $request_body .= '<VariationSpecifics>
                                             ';

                            foreach($attributes['VariationSpecifics'] as $variationSpecific) {

                                $request_body .= '<NameValueList>
                                                  <name>' . $variationSpecific['name'] . '</name>
                                                 ';

                                foreach ($variationSpecific['values'] as $value) {

                                    $request_body .= '<Value>' . $value . '</Value>';

                                }
                            }

                            $request_body .= '</VariationSpecifics>';

                        }
                        
                        /* Standard Input Fields */
                        if(isset($attributes['DetailLevel'])) {

                            $request_body .= '<DetailLevel>' . $attributes['DetailLevel'] . ' </DetailLevel>
                            ';

                        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                         ';

                        if(isset($attributes['MessageID'])) {

                            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>
                            ';

                        }

                        if(isset($attributes['OutputSelector'])) {

                            foreach($attributes['OutputSelector'] as $outputSelector) {

                                $request_body .= '<OutputSelector>' . $outputSelector . '</OutputSelector>
                                ';

                            }

                        }

        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>
                        <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                        </GetItemRequest>';

        $responseXml = L5ebtapiController::request('GetItem', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: GetItem($item_id) 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: GetItem($item_id) Error sending request. The XML response is an empty string');

            return ['Error:' => 'There was an error sending request the XML response is an empty string.' .
                'Please verify all eBay API settings are correct and try the request again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        $ack = $responseDoc->getElementsByTagName('Ack');

        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Failure' || $ack->item(0)->nodeValue == 'Warning') {

            //get any error nodes
            $errors = $responseDoc->getElementsByTagName('Errors');

            //if there are error nodes return the error message (array)
            if ($errors->length > 0) {

                $code = $errors->item(0)->getElementsByTagName('ErrorCode');

                $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

                $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

                if ($ack->item(0)->nodeValue == 'Failure') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::error('eBay API Call: getItem($item_id) Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::error('eBay API Call: getItem($item_id) Long message: ' .
                            $longMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' .
                            $shortMsg->item(0)->nodeValue . ' : Long Message: ' . $longMsg->item(0)->nodeValue];

                    } else {

                        Log::error('eBay API Call: getItem($item_id) Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' . $shortMsg->item(0)->nodeValue];

                    }

                }
                if ($ack->item(0)->nodeValue == 'Warning') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::warning('eBay API Call: getItem($item_id) Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::warning('eBay API Call: getItem($item_id) Long message: ' .
                            $longMsg->item(0)->nodeValue);

                    } else {

                        Log::warning('eBay API Call: getItem($item_id) Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                }

            }


        }
        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Success' || $ack->item(0)->nodeValue == 'Warning') {

            $xml = simplexml_load_string($responseDoc->saveXML());

            return $xml;

        } else {

            Log::error('eBay API Call: getItem($item_id): An error occurred during the getItem($item_id) ebay API' .
                'call. Please verify all API settings are correct and then try the request again.');

            return ['Error:' => 'An error occurred during the getItem($item_id) ebay API call.' .
                'Please verify all API settings are correct and then try the request again.'];

        }

    }// END getItem($item_id)

    /**
     * Method: uploadSiteHostedPictures($multiPartImageData, $image_name) - Upload an image to the eBay Picture Service.
     *
     * @param $multiPartImageData the image data. Acceptable formats (jpg, gif, png)
     * @param $image_name The name the uploaded image will have on the eBay Picture Services server.
     * @return Array key = 'eBay_Picture_Url' Value = 'The URL to the picture' OR Array with a key = 'Error:' and
     * Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try again.']
     */
    public function uploadSiteHostedPictures($multiPartImageData, $imageName)
    {
        $xmlReq = '<?xml version="1.0" encoding="utf-8"?>
                           <UploadSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                           <RequesterCredentials>
                           <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                           </RequesterCredentials>

                           <!-- Call-specific Input Fields -->
                           <PictureName>' . $imageName . '</PictureName>
                           <PictureSet>Standard</PictureSet>
                           <PictureUploadPolicy>ClearAndAdd</PictureUploadPolicy>

                           <!-- Standard Input Fields -->
                           <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                           <Version>' . $this->api_compatibility_level . '</Version>
                           <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
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

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request' .
                'again.'];

        }

        if ($respXmlStr == '') {

            Log::error('eBay API Call: uploadSiteHostedPictures() Error sending request' .
                'the XML response is an empty string');

            return ['Error:' => 'The XML response is an empty string. Please verify all eBay API settings are correct' .
                'and try the request again.'];

        }

        $respXmlObj = simplexml_load_string($respXmlStr);

        if ($respXmlObj->SiteHostedPictureDetails->FullURL) {

            $picURL = ['eBay_Picture_Url' => (string)$respXmlObj->SiteHostedPictureDetails->FullURL];

        } else {

            $picURL = ['Error:' => 'An error occurred While processing the uploadSiteHostedPictures() request.' .
                'Please verify all eBay API settings and input are correct and try the request again.'];

        }

        return $picURL;

    }// END of uploadSiteHostedPictures($multiPartImageData, $image_name)

    /**
     * Method: addFixedPriceItem(array $attributes) - List a single eBay Fixed Priced Item.
     *
     * @param $attributes
     *
     * @return Array The eBay item id and associated fees OR Array with a key = 'Error:' and Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try again.']
     */
    public function addFixedPriceItem(array $attributes)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                                <AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                                <RequesterCredentials>
                                <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                                </RequesterCredentials>

                                <!-- Call-specific Input Fields -->
                                <Item>
                                <Title>' . $attributes['Item_Title'] . '</Title>
                                <Description>' . $attributes['Item_Description'] . '</Description>
                                <PrimaryCategory>
                                <CategoryID>' . $attributes['Item_Category_Id'] . '</CategoryID>
                                </PrimaryCategory>
                                <StartPrice>' . $attributes['Item_Start_Price'] . '</StartPrice>
                                <Quantity>' . $attributes['Item_Quantity'] . '</Quantity>
                                <AutoPay>' . $attributes['Item_Auto_Pay'] . '</AutoPay>
                                <BuyerRequirementDetails>
                                <ShipToRegistrationCountry>' . $attributes['Shipping_Options'][0]['Ship_To_Registration_Country'] . '</ShipToRegistrationCountry>
                                </BuyerRequirementDetails>
                                <CategoryMappingAllowed>' . $attributes['Item_Category_Mapping_Allowed'] . '</CategoryMappingAllowed>
                                <ConditionID>' . $attributes['Item_Condition_Id'] . '</ConditionID>
                                <PostalCode>' . $attributes['Item_Postal_Code'] . '</PostalCode>
                                <Country>' . $attributes['Item_Country'] . '</Country>
                                <Currency>' . $attributes['Item_Currency'] . '</Currency>
                                <DispatchTimeMax>' . $attributes['Item_Dispatch_Time_Max'] . '</DispatchTimeMax>
                                <ListingDuration>' . $attributes['Item_Listing_Duration'] . '</ListingDuration>
                                <ListingType>' . $attributes['Item_Listing_Type'] . '</ListingType>
                                ';


        foreach ($attributes['Item_Payment_Methods'] as $payment_method) {

            if ($payment_method == 'VisaMC' && $attributes['Item_Auto_Pay'] == 'false') {

                $request_body .= '<PaymentMethods>' . $payment_method . '</PaymentMethods>
                    ';

            }
            if ($payment_method == 'Discover' && $attributes['Item_Auto_Pay'] == 'false') {

                $request_body .= '<PaymentMethods>' . $payment_method . '</PaymentMethods>
                    ';

            }
            if ($payment_method == 'AmEx' && $attributes['Item_Auto_Pay'] == 'false') {

                $request_body .= '<PaymentMethods>' . $payment_method . '</PaymentMethods>
                    ';

            }
            if ($payment_method == 'PayPal') {

                $request_body .= '<PaymentMethods>' . $payment_method . '</PaymentMethods>
                                <PayPalEmailAddress>' . $attributes['PayPal_Email_Address'] . '</PayPalEmailAddress>
                                ';
            }

        }

        $request_body .= '<PictureDetails>
                        <GalleryType>' . $attributes['Item_Gallery_Type'] . '</GalleryType>
                        <PictureSource>' . $attributes['Item_Picture_Source'] . '</PictureSource>
                        ';

        foreach ($attributes['Item_Image_Urls'] as $image_url) {

            $request_body .= '<PictureURL>' . $image_url . '</PictureURL>
            ';

        }

        $request_body .= '</PictureDetails>
        ';

        if (isset($attributes['Item_Specifics'])) {

            $request_body .= '<ItemSpecifics>
                              ';
            
            foreach ($attributes['Item_Specifics'] as $item_specific) {

                $request_body .= '<NameValueList>
                                <Name>' . $item_specific['Name'] . '</Name>
                                <Value>' . $item_specific['Value'] . '</Value>
                                </NameValueList>
                                ';
                
            }

            $request_body .= '</ItemSpecifics>
                              ';

        }

        if (isset($attributes['Return_Options'])) {

            $return_options = $attributes['Return_Options'];

            $request_body .= '<ReturnPolicy>
            ';

            if(isset($return_options[0]['Description'])) {

                $request_body .= '<Description>' . $return_options[0]['Description'] . '</Description>
                ';

            }
            if(isset($return_options[0]['EAN'])) {

                $request_body .= '<EAN>' . $return_options[0]['EAN'] . '</EAN>
                ';

            }
            if(isset($return_options[0]['Extended_Holiday_Returns'])) {

                $request_body .= '<ExtendedHolidayReturns>' . $return_options[0]['Extended_Holiday_Returns'] . '</ExtendedHolidayReturns>
                ';

            }
            if (isset($return_options[0]['Refund_Option'])) {

                $request_body .= '<RefundOption>' . $return_options[0]['Refund_Option'] . '</RefundOption>
                ';

            }
            if (isset($return_options[0]['Restocking_Fee_Value_Option'])) {

                $request_body .= '<RestockingFeeValue>' . $return_options[0]['Restocking_Fee_Value_Option'] . '</RestockingFeeValue>
                ';

            }
            if (isset($return_options[0]['Returns_Accepted_Option'])) {

                $request_body .= '<ReturnsAcceptedOption>' . $return_options[0]['Returns_Accepted_Option'] . '</ReturnsAcceptedOption>
                ';

            }
            if (isset($return_options[0]['Returns_Within_Option'])) {

                $request_body .= '<ReturnsWithinOption>' . $return_options[0]['Returns_Within_Option'] . '</ReturnsWithinOption>
                ';

            }
            if(isset($return_options[0]['Shipping_Cost_Paid_By_Option'])) {

                $request_body .= '<ShippingCostPaidByOption>' . $return_options[0]['Shipping_Cost_Paid_By_Option'] . '</ShippingCostPaidByOption>
                ';

            }
            if(isset($return_options[0]['Warranty_Duration_Option'])) {

                $request_body .= '<WarrantyDurationOption>' . $return_options[0]['Warranty_Duration_Option'] . '</WarrantyDurationOption>
                ';

            }
            if(isset($return_options[0]['Warranty_Offered_Option'])) {

                $request_body .= '<WarrantyOfferedOption>' . $return_options[0]['Warranty_Offered_Option'] . '</WarrantyOfferedOption>
                ';

            }
            if(isset($return_options[0]['Warranty_Type_Option'])) {

                $request_body .= '<WarrantyTypeOption>' . $return_options[0]['Warranty_Type_Option'] . '</WarrantyTypeOption>
                ';

            }

            $request_body .= '</ReturnPolicy>
            ';

        }

        $request_body .= '<ShippingDetails>
                          ';

        $request_body .= '<ShippingType>' . $attributes['Shipping_Options'][0]['Shipping_Type'] . '</ShippingType>
        ';

        if (isset($attributes['Exclude_Ship_To_Locations'])) {

            $excluded_ship_locations = $attributes['Exclude_Ship_To_Locations'];

            foreach ($excluded_ship_locations as $excluded_location) {

                $request_body .= '<ExcludeShipToLocation>' . $excluded_location . '</ExcludeShipToLocation>
                ';

            }

        }

        if (isset($attributes['Shipping_Options'])) {

            $shipping_options = $attributes['Shipping_Options'];

            foreach ($attributes['Shipping_Options'] as $shipping_option) {

                $request_body .= '<ShippingServiceOptions>
                ';

                if (isset($shipping_option['Shipping_Service_Priority'])) {

                    $request_body .= '<ShippingServicePriority>' . $shipping_option['Shipping_Service_Priority'] . '</ShippingServicePriority>
                    ';

                }
                if (isset($shipping_option['Shipping_Service'])) {

                    $request_body .= '<ShippingService>' . $shipping_option['Shipping_Service'] . '</ShippingService>
                    ';

                }
                if (isset($shipping_option['Free_Shipping'])) {

                    $request_body .= '<FreeShipping>' . $shipping_option['Free_Shipping'] . '</FreeShipping>
                    ';

                }
                if (isset($shipping_option['Shipping_Service_Cost_Currency_Id']) && isset($shipping_option['Shipping_Service_Cost'])) {

                    $request_body .= '<ShippingServiceCost currencyID="' . $shipping_option['Shipping_Service_Cost_Currency_Id'] . '">' . $shipping_option['Shipping_Service_Cost'] . '</ShippingServiceCost>
                    ';

                }
                if (isset($shipping_option['Shipping_Service_Additional_Cost_Currency_Id']) && isset($shipping_option['Shipping_Service_Additional_Cost'])) {

                    $request_body .= '<ShippingServiceAdditionalCost currencyID="' . $shipping_option['Shipping_Service_Additional_Cost_Currency_Id'] . '">' . $shipping_option['Shipping_Service_Additional_Cost'] . '</ShippingServiceAdditionalCost>
                    ';

                }

                $request_body .= '</ShippingServiceOptions>
                ';

            }

        }

        $request_body .= '</ShippingDetails>
                          ';

        if (isset($attributes['Ship_To_Locations'])) {

            $ship_to_locations = $attributes['Ship_To_Locations'];

            foreach ($ship_to_locations as $ship_to_location) {

                $request_body .= '<ShipToLocations>' . $ship_to_location . '</ShipToLocations>
                ';

            }

        }

        $request_body .= '<Site>' . $attributes['Item_Site'] . '</Site>
                        </Item>

                        <!-- Standard Input Fields -->
                        <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                        <Version>' . $this->api_compatibility_level . '</Version>
                        <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                        </AddFixedPriceItemRequest>';

        //dd($request_body);

        $responseXml = L5ebtapiController::request('AddFixedPriceItem', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: addFixedPriceItem() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: addFixedPriceItem() Error sending request. The XML response is an empty string');

            return ['Error:' => 'There was an error sending request the XML response is an empty string. Please verify all' .
                'eBay API settings are correct and try the request again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        //dd($responseDoc->saveXML());

        $ack = $responseDoc->getElementsByTagName('Ack');

        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Failure' || $ack->item(0)->nodeValue == 'Warning') {

            //get any error nodes
            $errors = $responseDoc->getElementsByTagName('Errors');

            //if there are error nodes return the error message (array)
            if ($errors->length > 0) {

                $code = $errors->item(0)->getElementsByTagName('ErrorCode');

                $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

                $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

                if ($ack->item(0)->nodeValue == 'Failure') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::error('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::error('eBay API Call: AddFixedPriceItem() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' .
                            $shortMsg->item(0)->nodeValue . ' : Long Message: ' . $longMsg->item(0)->nodeValue];

                    } else {

                        Log::error('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' . $shortMsg->item(0)->nodeValue];

                    }

                }
                if ($ack->item(0)->nodeValue == 'Warning') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::warning('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::warning('eBay API Call: AddFixedPriceItem() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                    } else {

                        Log::warning('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                }

            }


        }
        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Success' || $ack->item(0)->nodeValue == 'Warning') {

            $xml = simplexml_load_string($responseDoc->saveXML());

            if ($xml->ItemID) {

                $data = json_encode($xml);

                return json_decode($data, TRUE);

            }

        } else {

            Log::error('eBay API Call: AddFixedPriceItem(): An error occurred during the AddFixedPriceItem() ebay API' .
                'call. Please verify all API settings are correct and then try the request again.');

            return ['Error:' => 'An error occurred during the AddFixedPriceItem() ebay API call. Please verify all' .
                'API settings are correct and then try the request again.'];

        }


    }// END - addFixedPriceItem(array $attributes)

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