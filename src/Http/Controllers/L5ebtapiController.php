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
     * @param array $attributes - See the eBay API reference
     * http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GeteBayOfficialTime.html
     * for all possible attributes.
     *
     * @return array key = 'eBayOfficialTime', value = the eBay official timestamp OR key = 'Error:' and value =
     * 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try the
     * request again.']
     */
    public function getEbayOfficialTime(array $attributes = NULL)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $request_body .= '<GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $request_body .= '<RequesterCredentials>' . "\n";
        $request_body .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $request_body .= '</RequesterCredentials>' . "\n";

        /* Standard Input Fields */
        if (isset($attributes['MessageID'])) {

            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
        $request_body .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
        $request_body .= '</GeteBayOfficialTimeRequest>​​​';

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

                    return ['eBayOfficialTime' => (string)$xml->Timestamp];

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
     * Method: getEbayDetails(array attributes) - Retrieves eBay IDs and codes for Example shipping service codes,
     * enumerated data for Example payment methods, and other common eBay meta-data.
     *
     * @param array $attributes - See the eBay API reference
     * http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GeteBayDetails.html
     * for all possible attributes.
     *
     * @return SimpleXML Object
     */
    public function getEbayDetails(array $attributes)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $request_body .= '<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $request_body .= '<RequesterCredentials>' . "\n";
        $request_body .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $request_body .= '</RequesterCredentials>' . "\n";

        /* Call-specific Input Fields */
        if (isset($attributes['DetailName'])) {
            foreach ($attributes['DetailName'] as $detailName) {

                $request_body .= '<DetailName>' . $detailName . '</DetailName>' . "\n";

            }

        }

        /* Standard Input Fields */
        if (isset($attributes['MessageID'])) {

            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
        $request_body .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
        $request_body .= '</GeteBayDetailsRequest>​';

        $responseXml = L5ebtapiController::request('GeteBayDetails', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getEbayDetails() 404 Not Found');

            die('<P>Error sending request. 404 Not Found.</P>');

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getEbayDetails() Error sending request. The XML response is an empty string');

            die('<P>Error sending request. The XML response is an empty string.</P>');

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

                    } else {

                        Log::error('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                    $xml = simplexml_load_string($responseDoc->saveXML());

                    return $xml;

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

        }

    }// END getEbayDetails()

    /**
     * Method: getCategories(array $attributes) - Use this call to retrieve the latest category hierarchy for the eBay
     * site specified in the CategorySiteID property. By default, this is the site to which you submit the request.
     * You can retrieve all categories on the site, or you can use CategoryParent to retrieve one particular category
     * and its subcategories. The returned category list is contained in the CategoryArray property.
     *
     * @param array $attributes - See the eBay API reference
     * http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GetCategories.html
     * for all possible attributes.
     *
     * @return SimpleXML Object
     */
    public function getCategories(array $attributes)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $request_body .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $request_body .= '<RequesterCredentials>' . "\n";
        $request_body .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $request_body .= '</RequesterCredentials>' . "\n";

        /* Call-specific Input Fields */
        if (isset($attributes['CategoryParent'])) {

            foreach ($attributes['CategoryParent'] as $category_parent) {

                $request_body .= '<CategoryParent>' . $category_parent . '</CategoryParent>' . "\n";

            }
        }
        if (isset($attributes['CategorySiteID'])) {

            $request_body .= '<CategorySiteID>' . $attributes['CategorySiteID'] . '</CategorySiteID>' . "\n";

        }
        if (isset($attributes['LevelLimit'])) {

            $request_body .= '<LevelLimit>' . $attributes['LevelLimit'] . '</LevelLimit>' . "\n";

        }
        if (isset($attributes['DetailLevel'])) {

            foreach ($attributes['DetailLevel'] as $detailLevel) {

                $request_body .= '<DetailLevel>' . $detailLevel . '</DetailLevel>' . "\n";

            }
        }
        if (isset($attributes['ViewAllNodes'])) {

            $request_body .= '<ViewAllNodes>' . $attributes['ViewAllNodes'] . '</ViewAllNodes>' . "\n";

        }

        /* Standard Input Fields */
        if (isset($attributes['MessageID'])) {

            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

        }
        if (isset($attributes['OutputSelector'])) {

            foreach ($attributes['OutputSelector'] as $outputSelector) {

                $request_body .= '<OutputSelector>' . $outputSelector . '</OutputSelector>' . "\n";

            }
        }


        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
        $request_body .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
        $request_body .= '</GetCategoriesRequest>';

        $responseXml = L5ebtapiController::request('GetCategories', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getCategories() 404 Not Found');

            die('<P>Error sending request. 404 Not Found.</P>');

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getCategories() Error sending request. The XML response is an empty string');

            die('<P>Error sending request. The XML response is an empty string.</P>');

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

                    } else {

                        Log::error('eBay API Call: getCategoryFeatures() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                    $xml = simplexml_load_string($responseDoc->saveXML());

                    return $xml;

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

        }

    }// END getCategories()

    /**
     * Method: getCategoryFeatures(array $attributes) - returns information that describes the feature and value
     * settings that apply to the set of eBay categories.
     *
     * @param array $attributes - See the eBay API reference
     * http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GetCategoryFeatures.html
     * for all possible attributes.
     *
     * @return SimpleXML Object
     */
    public function getCategoryFeatures(array $attributes)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $request_body .= '<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $request_body .= '<RequesterCredentials>' . "\n";
        $request_body .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $request_body .= '</RequesterCredentials>' . "\n";

        /* Call-specific Input Fields */

        if (isset($attributes['AllFeaturesForCategory'])) {

            $request_body .= '<AllFeaturesForCategory>' . $attributes['AllFeaturesForCategory'] . '</AllFeaturesForCategory>' . "\n";

        }

        if (isset($attributes['CategoryID'])) {

            $request_body .= '<CategoryID>' . $attributes['CategoryID'] . '</CategoryID>' . "\n";

        }

        if (isset($attributes['FeatureID'])) {

            foreach ($attributes['FeatureID'] as $featureID) {

                $request_body .= '<FeatureID>' . $featureID . '</FeatureID>' . "\n";

            }
        }

        if (isset($attributes['LevelLimit'])) {

            $request_body .= '<LevelLimit>' . $attributes['LevelLimit'] . '</LevelLimit>' . "\n";

        }

        if (isset($attributes['ViewAllNodes'])) {

            $request_body .= '<ViewAllNodes>' . $attributes['ViewAllNodes'] . '</ViewAllNodes>' . "\n";

        }

        /* Standard Input Fields */

        if (isset($attributes['DetailLevel'])) {

            foreach ($attributes['DetailLevel'] as $detailLevel) {

                $request_body .= '<DetailLevel>' . $detailLevel . '</DetailLevel>' . "\n";

            }
        }
        if (isset($attributes['MessageID'])) {

            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

        }
        if (isset($attributes['OutputSelector'])) {

            foreach ($attributes['OutputSelector'] as $outputSelector) {

                $request_body .= '<OutputSelector>' . $outputSelector . '</OutputSelector>' . "\n";

            }
        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
        $request_body .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
        $request_body .= '</GetCategoryFeaturesRequest>​';

        $responseXml = L5ebtapiController::request('GetCategoryFeatures', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getCategoryFeatures() 404 Not Found');

            die('<P>Error sending request. 404 Not Found.</P>');

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getCategoryFeatures() Error sending request. The XML response is an empty string');

            die('<P>Error sending request. The XML response is an empty string.</P>');

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

                    } else {

                        Log::error('eBay API Call: getCategoryFeatures() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                    $xml = simplexml_load_string($responseDoc->saveXML());

                    return $xml;

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

        }

    }// END getCategoryFeatures()

    /**
     * Method: getItem(array $attributes) - Retrieves the eBay item detail for the given eBay item id.
     *
     * @param array $attributes - See the eBay API reference
     * http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GetItem.html
     * for all possible attributes.
     *
     * @return SimpleXML Object
     */
    public function getItem(array $attributes)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $request_body .= '<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $request_body .= '<RequesterCredentials>' . "\n";
        $request_body .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $request_body .= '</RequesterCredentials>' . "\n";

        /* Call-specific Input Fields */

        if (isset($attributes['IncludeItemCompatibilityList'])) {

            $request_body .= '<IncludeItemCompatibilityList>' . $attributes['IncludeItemCompatibilityList'] . '</IncludeItemCompatibilityList>' . "\n";

        }

        if (isset($attributes['IncludeItemSpecifics'])) {

            $request_body .= '<IncludeItemSpecifics>' . $attributes['IncludeItemSpecifics'] . '</IncludeItemSpecifics>' . "\n";

        }

        if (isset($attributes['IncludeTaxTable'])) {

            $request_body .= '<IncludeTaxTable>' . $attributes['IncludeTaxTable'] . '</IncludeTaxTable>' . "\n";

        }

        if (isset($attributes['IncludeWatchCount'])) {

            $request_body .= '<IncludeWatchCount>' . $attributes['IncludeWatchCount'] . '</IncludeWatchCount>' . "\n";

        }

        if (isset($attributes['ItemID'])) {

            $request_body .= '<ItemID>' . $attributes['ItemID'] . '</ItemID>' . "\n";

        }

        if (isset($attributes['SKU'])) {

            $request_body .= '<SKU>' . $attributes['SKU'] . '</SKU>' . "\n";

        }

        if (isset($attributes['TransactionID'])) {

            $request_body .= '<TransactionID>' . $attributes['TransactionID'] . '</TransactionID>' . "\n";

        }

        if (isset($attributes['VariationSKU'])) {

            $request_body .= '<VariationSKU>' . $attributes['VariationSKU'] . '</VariationSKU>' . "\n";

        }

        if (isset($attributes['VariationSpecifics'])) {

            $request_body .= '<VariationSpecifics>' . "\n";

            foreach ($attributes['VariationSpecifics'] as $variationSpecific) {

                $request_body .= '<NameValueList>' . "\n";

                $request_body .= '<name>' . $variationSpecific['Name'] . '</name>' . "\n";

                foreach ($variationSpecific['Value'] as $value) {

                    $request_body .= '<Value>' . $value . '</Value>' . "\n";

                }
            }

            $request_body .= '</VariationSpecifics>' . "\n";

        }

        /* Standard Input Fields */

        if (isset($attributes['DetailLevel'])) {

            foreach ($attributes['DetailLevel'] as $detailLevel) {

                $request_body .= '<DetailLevel>' . $detailLevel . '</DetailLevel>' . "\n";

            }
        }

        if (isset($attributes['MessageID'])) {

            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

        }

        if (isset($attributes['OutputSelector'])) {

            foreach ($attributes['OutputSelector'] as $outputSelector) {

                $request_body .= '<OutputSelector>' . $outputSelector . '</OutputSelector>' . "\n";

            }
        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
        $request_body .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
        $request_body .= '</GetItemRequest>';

        $responseXml = L5ebtapiController::request('GetItem', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getItem() 404 Not Found');

            die('<P>Error sending request. 404 Not Found.</P>');

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getItem() Error sending request. The XML response is an empty string');

            die('<P>Error sending request. The XML response is an empty string.</P>');

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

                    } else {

                        Log::error('eBay API Call: getItem($item_id) Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                    $xml = simplexml_load_string($responseDoc->saveXML());

                    return $xml;

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

        }

    }// END getItem($item_id)

    /**
     * Method: uploadSiteHostedPictures($attributes, $image) - Upload an image to the eBay Picture Service.
     *
     * @param $attributes the uploadSiteHostedPictures attributes
     * @param $image the image, Acceptable formats (jpg, gif, png)
     *
     * @return array key = 'eBay_Picture_Url' Value = 'The URL to the picture' OR Array with a key = 'Error:' and
     * Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try again.']
     */
    public function uploadSiteHostedPictures(array $attributes, $image = NULL)
    {
        $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xmlReq .= '<UploadSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlReq .= '<RequesterCredentials>' . "\n";
        $xmlReq .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $xmlReq .= '</RequesterCredentials>' . "\n";

        /* Call-specific Input Fields */

        if (isset($image)) {

            if (isset($attributes['ExtensionInDays'])) {

                $xmlReq .= '<ExtensionInDays>' . $attributes['ExtensionInDays'] . '</ExtensionInDays>' . "\n";

            }

            if (isset($attributes['PictureData'])) {

                $xmlReq .= '<PictureData contentType="string">' . $attributes['PictureData'] . '</PictureData>' . "\n";

            }

            if (isset($attributes['PictureName'])) {

                $xmlReq .= '<PictureName>' . $attributes['PictureName'] . '</PictureName>' . "\n";

            }

            if (isset($attributes['PictureSet'])) {

                $xmlReq .= '<PictureSet>' . $attributes['PictureSet'] . '</PictureSet>' . "\n";

            }

            if (isset($attributes['PictureSystemVersion'])) {

                $xmlReq .= '<PictureSystemVersion>' . $attributes['PictureSystemVersion'] . '</PictureSystemVersion>' . "\n";

            }

            if (isset($attributes['PictureUploadPolicy'])) {

                $xmlReq .= '<PictureUploadPolicy>' . $attributes['PictureUploadPolicy'] . '</PictureUploadPolicy>' . "\n";

            }

            /* Standard Input Fields */

            if (isset($attributes['MessageID'])) {

                $xmlReq .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

            }

            $xmlReq .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
            $xmlReq .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
            $xmlReq .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
            $xmlReq .= '</UploadSiteHostedPicturesRequest>';

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
            $secondPart .= $image;
            $secondPart .= $CRLF;
            $secondPart .= "--" . $boundary . "--" . $CRLF;

            $request_body = $firstPart . $secondPart;

            $respXmlStr = L5ebtapiController::multiPartRequest('UploadSiteHostedPictures', $request_body, $boundary);   // send multi-part request and get string XML response

        } else {

            if (isset($attributes['ExtensionInDays'])) {

                $xmlReq .= '<ExtensionInDays>' . $attributes['ExtensionInDays'] . '</ExtensionInDays>' . "\n";

            }

            if (isset($attributes['ExternalPictureURL'])) {

                $xmlReq .= '<ExternalPictureURL>' . $attributes['ExternalPictureURL'] . '</ExternalPictureURL>' . "\n";

            }

            if (isset($attributes['PictureName'])) {

                $xmlReq .= '<PictureName>' . $attributes['PictureName'] . '</PictureName>' . "\n";

            }

            if (isset($attributes['PictureSet'])) {

                $xmlReq .= '<PictureSet>' . $attributes['PictureSet'] . '</PictureSet>' . "\n";

            }

            if (isset($attributes['PictureSystemVersion'])) {

                $xmlReq .= '<PictureSystemVersion>' . $attributes['PictureSystemVersion'] . '</PictureSystemVersion>' . "\n";

            }

            if (isset($attributes['PictureUploadPolicy'])) {

                $xmlReq .= '<PictureUploadPolicy>' . $attributes['PictureUploadPolicy'] . '</PictureUploadPolicy>' . "\n";

            }

            /* Standard Input Fields */

            if (isset($attributes['MessageID'])) {

                $xmlReq .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

            }

            $xmlReq .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
            $xmlReq .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
            $xmlReq .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
            $xmlReq .= '</UploadSiteHostedPicturesRequest>';

            $respXmlStr = L5ebtapiController::request('UploadSiteHostedPictures', $xmlReq);

        }

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
     * @return SimpleXML Object The eBay item id and associated fees OR the error information.
     */
    public function addFixedPriceItem(array $attributes)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $request_body .= '<AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $request_body .= '<RequesterCredentials>' . "\n";
        $request_body .= '<eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>' . "\n";
        $request_body .= '</RequesterCredentials>' . "\n";

        /* Call-specific Input Fields */
        $request_body .= '<Item>' . "\n";

        if (isset($attributes['ApplicationData'])) {

            $request_body .= '<ApplicationData>' . $attributes['ApplicationData'] . '</ApplicationData>' . "\n";

        }

        if (isset($attributes['AutoPay'])) {

            $request_body .= '<AutoPay>' . $attributes['AutoPay'] . '</AutoPay>' . "\n";

        }

        if (isset($attributes['BestOfferDetails'])) {

            $request_body .= '<BestOfferDetails>' . "\n";

            if (isset($attributes['BestOfferDetails']['BestOfferEnabled'])) {

                $request_body .= '<BestOfferEnabled>' . $attributes['BestOfferDetails']['BestOfferEnabled'] . '</BestOfferEnabled>' . "\n";

            }

            $request_body .= '</BestOfferDetails>' . "\n";

        }

        if (isset($attributes['BuyerRequirementDetails'])) {

            $request_body .= '<BuyerRequirementDetails>' . "\n";

            if (isset($attributes['BuyerRequirementDetails']['LinkedPayPalAccount'])) {

                $request_body .= '<LinkedPayPalAccount>' .
                    $attributes['BuyerRequirementDetails']['LinkedPayPalAccount'] .
                    '</LinkedPayPalAccount>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['MaximumBuyerPolicyViolations'])) {

                $request_body .= '<MaximumBuyerPolicyViolations>' . "\n";

                if (isset($attributes['BuyerRequirementDetails']['MaximumBuyerPolicyViolations']['Count'])) {

                    $request_body .= '<Count>' .
                        $attributes['BuyerRequirementDetails']['MaximumBuyerPolicyViolations']['Count'] .
                        '</Count>' . "\n";

                }

                if (isset($attributes['BuyerRequirementDetails']['MaximumBuyerPolicyViolations']['Period'])) {

                    $request_body .= '<Period>' .
                        $attributes['BuyerRequirementDetails']['MaximumBuyerPolicyViolations']['Period'] .
                        '</Period>' . "\n";

                }

                $request_body .= '</MaximumBuyerPolicyViolations>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['MaximumItemRequirements'])) {

                $request_body .= '<MaximumItemRequirements>' . "\n";

                if (isset($attributes['BuyerRequirementDetails']['MaximumItemRequirements']['MaximumItemCount'])) {

                    $request_body .= '<MaximumItemCount>' .
                        $attributes['BuyerRequirementDetails']['MaximumItemRequirements']['MaximumItemCount'] .
                        '</MaximumItemCount>' . "\n";

                }

                if (isset($attributes['BuyerRequirementDetails']['MaximumItemRequirements']['MinimumFeedbackScore'])) {

                    $request_body .= '<MinimumFeedbackScore>' .
                        $attributes['BuyerRequirementDetails']['MaximumItemRequirements']['MinimumFeedbackScore'] .
                        '</MinimumFeedbackScore>' . "\n";

                }

                $request_body .= '</MaximumItemRequirements>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['MaximumUnpaidItemStrikesInfo'])) {

                $request_body .= '<MaximumUnpaidItemStrikesInfo>' . "\n";

                if (isset($attributes['BuyerRequirementDetails']['MaximumUnpaidItemStrikesInfo']['Count'])) {

                    $request_body .= '<Count>' .
                        $attributes['BuyerRequirementDetails']['MaximumUnpaidItemStrikesInfo']['Count'] .
                        '</Count>' . "\n";

                }

                if (isset($attributes['BuyerRequirementDetails']['MaximumUnpaidItemStrikesInfo']['Period'])) {

                    $request_body .= '<Period>' .
                        $attributes['BuyerRequirementDetails']['MaximumUnpaidItemStrikesInfo']['Period'] .
                        '</Period>' . "\n";

                }

                $request_body .= '</MaximumUnpaidItemStrikesInfo>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['MinimumFeedbackScore'])) {

                $request_body .= '<MinimumFeedbackScore>' .
                    $attributes['BuyerRequirementDetails']['MinimumFeedbackScore'] .
                    '</MinimumFeedbackScore>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['ShipToRegistrationCountry'])) {

                $request_body .= '<ShipToRegistrationCountry>' .
                    $attributes['BuyerRequirementDetails']['ShipToRegistrationCountry'] .
                    '</ShipToRegistrationCountry>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['VerifiedUserRequirements'])) {

                $request_body .= '<VerifiedUserRequirements>' . "\n";

                if (isset($attributes['BuyerRequirementDetails']['VerifiedUserRequirements']['MinimumFeedbackScore'])) {

                    $request_body .= '<MinimumFeedbackScore>' .
                        $attributes['BuyerRequirementDetails']['VerifiedUserRequirements']['MinimumFeedbackScore'] .
                        '</MinimumFeedbackScore>' . "\n";

                }

                if (isset($attributes['BuyerRequirementDetails']['VerifiedUserRequirements']['VerifiedUser'])) {

                    $request_body .= '<VerifiedUser>' .
                        $attributes['BuyerRequirementDetails']['VerifiedUserRequirements']['VerifiedUser'] .
                        '</VerifiedUser>' . "\n";

                }

                $request_body .= '</VerifiedUserRequirements>' . "\n";

            }

            if (isset($attributes['BuyerRequirementDetails']['ZeroFeedbackScore'])) {

                $request_body .= '<ZeroFeedbackScore>' . $attributes['BuyerRequirementDetails']['ZeroFeedbackScore'] .
                    '</ZeroFeedbackScore>' . "\n";

            }


            $request_body .= '</BuyerRequirementDetails>' . "\n";

        }

        if (isset($attributes['CategoryBasedAttributesPrefill'])) {

            $request_body .= '<CategoryBasedAttributesPrefill>' . $attributes['CategoryBasedAttributesPrefill'] .
                '</CategoryBasedAttributesPrefill>' . "\n";

        }

        if (isset($attributes['CategoryMappingAllowed'])) {

            $request_body .= '<CategoryMappingAllowed>' . $attributes['CategoryMappingAllowed'] .
                '</CategoryMappingAllowed>' . "\n";

        }


        if (isset($attributes['Charity'])) {

            $request_body .= '<Charity>' . "\n";

            if (isset($attributes['Charity']['CharityID'])) {

                $request_body .= '<CharityID>' . $attributes['Charity']['CharityID'] . '</CharityID>' . "\n";

            }

            if (isset($attributes['Charity']['CharityNumber'])) {

                $request_body .= '<CharityNumber>' . $attributes['Charity']['CharityNumber'] .
                    '</CharityNumber>' . "\n";

            }

            if (isset($attributes['Charity']['DonationPercent'])) {

                $request_body .= '<DonationPercent>' . $attributes['Charity']['DonationPercent'] .
                    '</DonationPercent>' . "\n";

            }

            $request_body .= '</Charity>' . "\n";

        }

        if (isset($attributes['ConditionDescription'])) {

            $request_body .= '<ConditionDescription>' . $attributes['ConditionDescription'] .
                '</ConditionDescription>' . "\n";

        }

        if (isset($attributes['ConditionID'])) {

            $request_body .= '<ConditionID>' . $attributes['ConditionID'] . '</ConditionID>' . "\n";

        }

        if (isset($attributes['Country'])) {

            $request_body .= '<Country>' . $attributes['Country'] . '</Country>' . "\n";

        }

        if (isset($attributes['CrossBorderTrade'])) {

            foreach ($attributes['CrossBorderTrade'] as $crossBorderTrade) {

                $request_body .= '<CrossBorderTrade>' . $crossBorderTrade . '</CrossBorderTrade>' . "\n";

                /* more CrossBorderTrade values allowed here */

            }

        }

        if (isset($attributes['Currency'])) {

            $request_body .= '<Currency>' . $attributes['Currency'] . '</Currency>' . "\n";

        }

        if (isset($attributes['Description'])) {

            $request_body .= '<Description>' . $attributes['Description'] . '</Description>' . "\n";

        }

        if (isset($attributes['DigitalGoodInfo'])) {

            $request_body .= '<DigitalGoodInfo>' . "\n";

            if (isset($attributes['DigitalGoodInfo']['DigitalDelivery'])) {

                $request_body .= '<DigitalDelivery>' . $attributes['DigitalGoodInfo']['DigitalDelivery'] .
                    '</DigitalDelivery>' . "\n";

            }

            $request_body .= '</DigitalGoodInfo>' . "\n";

        }

        if (isset($attributes['DisableBuyerRequirements'])) {

            $request_body .= '<DisableBuyerRequirements>' . $attributes['DisableBuyerRequirements'] .
                '</DisableBuyerRequirements>' . "\n";

        }


        if (isset($attributes['DiscountPriceInfo'])) {

            $request_body .= '<DiscountPriceInfo>' . "\n";

            if (isset($attributes['DiscountPriceInfo']['MadeForOutletComparisonPrice'])) {

                $request_body .= '<MadeForOutletComparisonPrice>' .
                    $attributes['DiscountPriceInfo']['MadeForOutletComparisonPrice'] .
                    '</MadeForOutletComparisonPrice>' . "\n";

            }

            if (isset($attributes['DiscountPriceInfo']['MinimumAdvertisedPrice'])) {

                $request_body .= '<MinimumAdvertisedPrice>' .
                    $attributes['DiscountPriceInfo']['MinimumAdvertisedPrice'] .
                    '</MinimumAdvertisedPrice>' . "\n";

            }

            if (isset($attributes['DiscountPriceInfo']['MinimumAdvertisedPriceExposure'])) {

                $request_body .= '<MinimumAdvertisedPriceExposure>' .
                    $attributes['DiscountPriceInfo']['MinimumAdvertisedPriceExposure'] .
                    '</MinimumAdvertisedPriceExposure>' . "\n";

            }

            if (isset($attributes['DiscountPriceInfo']['OriginalRetailPrice'])) {

                $request_body .= '<OriginalRetailPrice>' . $attributes['DiscountPriceInfo']['OriginalRetailPrice'] .
                    '</OriginalRetailPrice>' . "\n";

            }

            if (isset($attributes['DiscountPriceInfo']['SoldOffeBay'])) {

                $request_body .= '<SoldOffeBay>' . $attributes['DiscountPriceInfo']['SoldOffeBay'] .
                    '</SoldOffeBay>' . "\n";

            }

            if (isset($attributes['DiscountPriceInfo']['SoldOneBay'])) {

                $request_body .= '<SoldOneBay>' . $attributes['DiscountPriceInfo']['SoldOneBay'] .
                    '</SoldOneBay>' . "\n";

            }

            $request_body .= '</DiscountPriceInfo>' . "\n";

        }

        if (isset($attributes['DispatchTimeMax'])) {

            $request_body .= '<DispatchTimeMax>' . $attributes['DispatchTimeMax'] . '</DispatchTimeMax>' . "\n";

        }

        if (isset($attributes['eBayNowEligible'])) {

            $request_body .= '<eBayNowEligible>' . $attributes['eBayNowEligible'] . '</eBayNowEligible>' . "\n";

        }

        if (isset($attributes['eBayPlus'])) {

            $request_body .= '<eBayPlus>' . $attributes['eBayPlus'] . '</eBayPlus>' . "\n";

        }

        if (isset($attributes['GiftIcon'])) {

            $request_body .= '<GiftIcon>' . $attributes['GiftIcon'] . '</GiftIcon>' . "\n";

        }

        if (isset($attributes['GiftServices'])) {

            foreach ($attributes['GiftServices'] as $giftService) {

                $request_body .= '<GiftServices>' . $giftService . '</GiftServices>' . "\n";

                /* more GiftServices values allowed here */

            }

        }

        if (isset($attributes['HitCounter'])) {

            $request_body .= '<HitCounter>' . $attributes['HitCounter'] . '</HitCounter>' . "\n";

        }

        if (isset($attributes['IncludeRecommendations'])) {

            $request_body .= '<IncludeRecommendations>' . $attributes['IncludeRecommendations'] .
                '</IncludeRecommendations>' . "\n";

        }

        if (isset($attributes['InventoryTrackingMethod'])) {

            $request_body .= '<InventoryTrackingMethod>' . $attributes['InventoryTrackingMethod'] .
                '</InventoryTrackingMethod>' . "\n";

        }

        if (isset($attributes['ItemCompatibilityList'])) {

            $request_body .= '<ItemCompatibilityList>' . "\n";

                if (isset($attributes['ItemCompatibilityList']['Compatibility'])) {

                    foreach ($attributes['ItemCompatibilityList']['Compatibility'] as $compatibility) {

                        $request_body .= '<Compatibility>' . "\n";

                        if (isset($compatibility['CompatibilityNotes'])) {

                            $request_body .= '<CompatibilityNotes>' . $compatibility['CompatibilityNotes'] .
                                '</CompatibilityNotes>' . "\n";

                        }

                        if (isset($compatibility['NameValueList'])) {

                            foreach ($compatibility['NameValueList'] as $nameValueList) {

                                $request_body .= '<NameValueList>' . "\n";

                                if (isset($nameValueList['Name'])) {

                                    $request_body .= '<Name>' . $nameValueList['Name'] . '</Name>' . "\n";

                                }

                                if (isset($nameValueList['Value'])) {

                                    foreach ($nameValueList['Value'] as $value) {

                                        $request_body .= '<Value>' . $value . '</Value>' . "\n";

                                    }

                                }

                                $request_body .= '</NameValueList>' . "\n";

                            }

                        }

                        $request_body .= '</Compatibility>' . "\n";

                    }

                }

            $request_body .= '</ItemCompatibilityList>' . "\n";

        }

        if (isset($attributes['ItemSpecifics'])) {

            $request_body .= '<ItemSpecifics>' . "\n";

            foreach ($attributes['ItemSpecifics']['NameValueList'] as $nameValueList) {

                $request_body .= '<NameValueList>' . "\n";
                $request_body .= '<Name>' . $nameValueList['Name'] . '</Name>' . "\n";

                foreach ($nameValueList['Value'] as $value) {

                    $request_body .= '<Value>' . $value . '</Value>' . "\n";

                    /* more Value values allowed here */

                }

                $request_body .= '</NameValueList>' . "\n";

                /* more NameValueList nodes allowed here */

            }

            $request_body .= '</ItemSpecifics>' . "\n";

        }

        if (isset($attributes['ListingCheckoutRedirectPreference'])) {

            $request_body .= '<ListingCheckoutRedirectPreference>' . "\n";

            if (isset($attributes['ListingCheckoutRedirectPreference']['ProStoresStoreName'])) {

                $request_body .= '<ProStoresStoreName>' .
                    $attributes['ListingCheckoutRedirectPreference']['ProStoresStoreName'] .
                    '</ProStoresStoreName>' . "\n";

            }

            if (isset($attributes['ListingCheckoutRedirectPreference']['SellerThirdPartyUsername'])) {

                $request_body .= '<SellerThirdPartyUsername>' .
                    $attributes['ListingCheckoutRedirectPreference']['SellerThirdPartyUsername'] .
                    '</SellerThirdPartyUsername>' . "\n";

            }

            $request_body .= '</ListingCheckoutRedirectPreference>' . "\n";

        }

        if (isset($attributes['ListingDesigner'])) {

            $request_body .= '<ListingDesigner>' . "\n";

            if (isset($attributes['ListingDesigner']['LayoutID'])) {

                $request_body .= '<LayoutID>' . $attributes['ListingDesigner']['LayoutID'] . '</LayoutID>' . "\n";

            }

            if (isset($attributes['ListingDesigner']['OptimalPictureSize'])) {

                $request_body .= '<OptimalPictureSize>' . $attributes['ListingDesigner']['OptimalPictureSize'] .
                    '</OptimalPictureSize>' . "\n";

            }

            if (isset($attributes['ListingDesigner']['ThemeID'])) {

                $request_body .= '<ThemeID>' . $attributes['ListingDesigner']['ThemeID'] . '</ThemeID>' . "\n";

            }

            $request_body .= '</ListingDesigner>' . "\n";

        }

        if (isset($attributes['ListingDetails'])) {

            $request_body .= '<ListingDetails>' . "\n";

            if (isset($attributes['ListingDetails']['BestOfferAutoAcceptPrice'])) {

                $request_body .= '<BestOfferAutoAcceptPrice>' .
                    $attributes['ListingDetails']['BestOfferAutoAcceptPrice'] . '</BestOfferAutoAcceptPrice>' . "\n";

            }

            if (isset($attributes['ListingDetails']['LocalListingDistance'])) {

                $request_body .= '<LocalListingDistance>' .
                    $attributes['ListingDetails']['LocalListingDistance'] . '</LocalListingDistance>' . "\n";

            }

            if (isset($attributes['ListingDetails']['MinimumBestOfferPrice'])) {

                $request_body .= '<MinimumBestOfferPrice>' .
                    $attributes['ListingDetails']['MinimumBestOfferPrice'] . '</MinimumBestOfferPrice>' . "\n";

            }

            $request_body .= '</ListingDetails>' . "\n";

        }

        if (isset($attributes['ListingDuration'])) {

            $request_body .= '<ListingDuration>' . $attributes['ListingDuration'] . '</ListingDuration>' . "\n";

        }

        if (isset($attributes['ListingEnhancement'])) {

            foreach ($attributes['ListingEnhancement'] as $listingEnhancement) {

                $request_body .= '<ListingEnhancement>' . $listingEnhancement . '</ListingEnhancement>' . "\n";

                /* more ListingEnhancement values allowed here */

            }

        }

        if (isset($attributes['ListingType'])) {

            $request_body .= '<ListingType>' . $attributes['ListingType'] . '</ListingType>' . "\n";

        }

        if (isset($attributes['Location'])) {

            $request_body .= '<Location>' . $attributes['Location'] . '</Location>' . "\n";

        }

        if (isset($attributes['PaymentMethods'])) {

            foreach ($attributes['PaymentMethods'] as $aymentMethod) {

                $request_body .= '<PaymentMethods>' . $aymentMethod . '</PaymentMethods>' . "\n";

                /* more PaymentMethods values allowed here */

            }

        }

        if (isset($attributes['PayPalEmailAddress'])) {

            $request_body .= '<PayPalEmailAddress>' . $attributes['PayPalEmailAddress'] .
                '</PayPalEmailAddress>' . "\n";

        }

        if (isset($attributes['PickupInStoreDetails'])) {

            $request_body .= '<PickupInStoreDetails>' . "\n";

            if (isset($attributes['PickupInStoreDetails']['EligibleForPickupDropOff'])) {

                $request_body .= '<EligibleForPickupDropOff>' .
                    $attributes['PickupInStoreDetails']['EligibleForPickupDropOff'] .
                    '</EligibleForPickupDropOff>' . "\n";

            }

            if (isset($attributes['PickupInStoreDetails']['EligibleForPickupInStore'])) {

                $request_body .= '<EligibleForPickupInStore>' .
                    $attributes['PickupInStoreDetails']['EligibleForPickupInStore'] .
                    '</EligibleForPickupInStore>' . "\n";

            }

            $request_body .= '</PickupInStoreDetails>' . "\n";

        }

        if (isset($attributes['PictureDetails'])) {

            $request_body .= '<PictureDetails>' . "\n";

            if (isset($attributes['PictureDetails']['GalleryDuration'])) {

                $request_body .= '<GalleryDuration>' . $attributes['PictureDetails']['GalleryDuration'] .
                    '</GalleryDuration>' . "\n";

            }

            if (isset($attributes['PictureDetails']['GalleryType'])) {

                $request_body .= '<GalleryType>' . $attributes['PictureDetails']['GalleryType'] .
                    '</GalleryType>' . "\n";

            }

            if (isset($attributes['PictureDetails']['GalleryURL'])) {

                $request_body .= '<GalleryURL>' . $attributes['PictureDetails']['GalleryURL'] . '</GalleryURL>' . "\n";

            }

            if (isset($attributes['PictureDetails']['PhotoDisplay'])) {

                $request_body .= '<PhotoDisplay>' . $attributes['PictureDetails']['PhotoDisplay'] .
                    '</PhotoDisplay>' . "\n";

            }

            if (isset($attributes['PictureDetails']['PictureSource'])) {

                $request_body .= '<PictureSource>' . $attributes['PictureDetails']['PictureSource'] .
                    '</PictureSource>' . "\n";

            }

            foreach ($attributes['PictureDetails']['PictureURL'] as $pictureURL) {

                $request_body .= '<PictureURL>' . $pictureURL . '</PictureURL>' . "\n";

                /*more PictureURL values allowed here*/

            }

            $request_body .= '</PictureDetails>' . "\n";

        }

        if (isset($attributes['PostalCode'])) {

            $request_body .= '<PostalCode>' . $attributes['PostalCode'] . '</PostalCode>' . "\n";

        }

        if (isset($attributes['PostCheckoutExperienceEnabled'])) {

            $request_body .= '<PostCheckoutExperienceEnabled>' . $attributes['PostCheckoutExperienceEnabled'] .
                '</PostCheckoutExperienceEnabled>' . "\n";

        }

        if (isset($attributes['PrimaryCategory'])) {

            $request_body .= '<PrimaryCategory>' . "\n";

            if (isset($attributes['PrimaryCategory']['CategoryID'])) {

                $request_body .= '<CategoryID>' . $attributes['PrimaryCategory']['CategoryID'] . '</CategoryID>' . "\n";

            }

            $request_body .= '</PrimaryCategory>' . "\n";

        }

        if (isset($attributes['PrivateListing'])) {

            $request_body .= '<PrivateListing>' . $attributes['PrivateListing'] . '</PrivateListing>' . "\n";

        }

        if (isset($attributes['PrivateNotes'])) {

            $request_body .= '<PrivateNotes>' . $attributes['PrivateNotes'] . '</PrivateNotes>' . "\n";

        }

        if (isset($attributes['ProductListingDetails'])) {

            $request_body .= '<ProductListingDetails>' . "\n";

            if (isset($attributes['ProductListingDetails']['BrandMPN'])) {

                $request_body .= '<BrandMPN>' . "\n";

                if (isset($attributes['ProductListingDetails']['BrandMPN']['Brand'])) {

                    $request_body .= '<Brand>' . $attributes['ProductListingDetails']['BrandMPN']['Brand'] .
                        '</Brand>' . "\n";

                }

                if (isset($attributes['ProductListingDetails']['BrandMPN']['MPN'])) {

                    $request_body .= '<MPN>' . $attributes['ProductListingDetails']['BrandMPN']['MPN'] .
                        '</MPN>' . "\n";

                }

                $request_body .= '</BrandMPN>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['EAN'])) {

                $request_body .= '<EAN>' . $attributes['ProductListingDetails']['EAN'] . '</EAN>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['IncludeeBayProductDetails'])) {

                $request_body .= '<IncludeeBayProductDetails>' .
                    $attributes['ProductListingDetails']['IncludeeBayProductDetails'] .
                    '</IncludeeBayProductDetails>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['IncludeStockPhotoURL'])) {

                $request_body .= '<IncludeStockPhotoURL>' .
                    $attributes['ProductListingDetails']['IncludeStockPhotoURL'] . '</IncludeStockPhotoURL>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['ISBN'])) {

                $request_body .= '<ISBN>' . $attributes['ProductListingDetails']['ISBN'] . '</ISBN>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['ProductReferenceID'])) {

                $request_body .= '<ProductReferenceID>' . $attributes['ProductListingDetails']['ProductReferenceID'] .
                    '</ProductReferenceID>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['ReturnSearchResultOnDuplicates'])) {

                $request_body .= '<ReturnSearchResultOnDuplicates>' .
                    $attributes['ProductListingDetails']['ReturnSearchResultOnDuplicates'] .
                    '</ReturnSearchResultOnDuplicates>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['TicketListingDetails'])) {

                $request_body .= '<TicketListingDetails>' . "\n";

                if (isset($attributes['ProductListingDetails']['TicketListingDetails']['EventTitle'])) {

                    $request_body .= '<EventTitle>' .
                        $attributes['ProductListingDetails']['TicketListingDetails']['EventTitle'] .
                        '</EventTitle>' . "\n";

                }

                if (isset($attributes['ProductListingDetails']['TicketListingDetails']['PrintedDate'])) {

                    $request_body .= '<PrintedDate>' .
                        $attributes['ProductListingDetails']['TicketListingDetails']['PrintedDate'] .
                        '</PrintedDate>' . "\n";

                }

                if (isset($attributes['ProductListingDetails']['TicketListingDetails']['PrintedTime'])) {

                    $request_body .= '<PrintedTime>' .
                        $attributes['ProductListingDetails']['TicketListingDetails']['PrintedTime'] .
                        '</PrintedTime>' . "\n";

                }

                if (isset($attributes['ProductListingDetails']['TicketListingDetails']['Venue'])) {

                    $request_body .= '<Venue>' . $attributes['ProductListingDetails']['TicketListingDetails']['Venue'] .
                        '</Venue>' . "\n";

                }

                $request_body .= '</TicketListingDetails>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['UPC'])) {

                $request_body .= '<UPC>' . $attributes['ProductListingDetails']['UPC'] . '</UPC>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['UseFirstProduct'])) {

                $request_body .= '<UseFirstProduct>' . $attributes['ProductListingDetails']['UseFirstProduct'] .
                    '</UseFirstProduct>' . "\n";

            }

            if (isset($attributes['ProductListingDetails']['UseStockPhotoURLAsGallery'])) {

                $request_body .= '<UseStockPhotoURLAsGallery>' .
                    $attributes['ProductListingDetails']['UseStockPhotoURLAsGallery'] .
                    '</UseStockPhotoURLAsGallery>' . "\n";

            }

            $request_body .= '</ProductListingDetails>' . "\n";

        }

        if (isset($attributes['Quantity'])) {

            $request_body .= '<Quantity>' . $attributes['Quantity'] . '</Quantity>' . "\n";

        }

        if (isset($attributes['QuantityInfo'])) {

            $request_body .= '<QuantityInfo>' . "\n";

            if (isset($attributes['QuantityInfo']['MinimumRemnantSet'])) {

                $request_body .= '<MinimumRemnantSet>' . $attributes['QuantityInfo']['MinimumRemnantSet'] .
                    '</MinimumRemnantSet>' . "\n";

            }

            $request_body .= '</QuantityInfo>' . "\n";

        }

        if (isset($attributes['QuantityRestrictionPerBuyer'])) {

            $request_body .= '<QuantityRestrictionPerBuyer>' . "\n";

            if (isset($attributes['QuantityRestrictionPerBuyer']['MaximumQuantity'])) {

                $request_body .= '<MaximumQuantity>' . $attributes['QuantityRestrictionPerBuyer']['MaximumQuantity'] .
                    '</MaximumQuantity>' . "\n";

            }

            $request_body .= '</QuantityRestrictionPerBuyer>' . "\n";

        }

        if (isset($attributes['ReturnPolicy'])) {

            $request_body .= '<ReturnPolicy>' . "\n";

            if (isset($attributes['ReturnPolicy']['Description'])) {

                $request_body .= '<Description>' . $attributes['ReturnPolicy']['Description'] . '</Description>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['EAN'])) {

                $request_body .= '<EAN>' . $attributes['ReturnPolicy']['EAN'] . '</EAN>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['ExtendedHolidayReturns'])) {

                $request_body .= '<ExtendedHolidayReturns>' . $attributes['ReturnPolicy']['ExtendedHolidayReturns'] .
                    '</ExtendedHolidayReturns>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['RefundOption'])) {

                $request_body .= '<RefundOption>' . $attributes['ReturnPolicy']['RefundOption'] .
                    '</RefundOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['RestockingFeeValueOption'])) {

                $request_body .= '<RestockingFeeValueOption>' .
                    $attributes['ReturnPolicy']['RestockingFeeValueOption'] . '</RestockingFeeValueOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['ReturnsAcceptedOption'])) {

                $request_body .= '<ReturnsAcceptedOption>' . $attributes['ReturnPolicy']['ReturnsAcceptedOption'] .
                    '</ReturnsAcceptedOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['ReturnsWithinOption'])) {

                $request_body .= '<ReturnsWithinOption>' . $attributes['ReturnPolicy']['ReturnsWithinOption'] .
                    '</ReturnsWithinOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['ShippingCostPaidByOption'])) {

                $request_body .= '<ShippingCostPaidByOption>' .
                    $attributes['ReturnPolicy']['ShippingCostPaidByOption'] . '</ShippingCostPaidByOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['WarrantyDurationOption'])) {

                $request_body .= '<WarrantyDurationOption>' . $attributes['ReturnPolicy']['WarrantyDurationOption'] .
                    '</WarrantyDurationOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['WarrantyOfferedOption'])) {

                $request_body .= '<WarrantyOfferedOption>' . $attributes['ReturnPolicy']['WarrantyOfferedOption'] .
                    '</WarrantyOfferedOption>' . "\n";

            }

            if (isset($attributes['ReturnPolicy']['WarrantyTypeOption'])) {

                $request_body .= '<WarrantyTypeOption>' . $attributes['ReturnPolicy']['WarrantyTypeOption'] .
                    '</WarrantyTypeOption>' . "\n";

            }

            $request_body .= '</ReturnPolicy>' . "\n";

        }

        if (isset($attributes['ScheduleTime'])) {

            $request_body .= '<ScheduleTime>' . $attributes['ScheduleTime'] . '</ScheduleTime>' . "\n";

        }

        if (isset($attributes['SecondaryCategory'])) {

            $request_body .= '<SecondaryCategory>' . "\n";

            if (isset($attributes['SecondaryCategory']['CategoryID'])) {

                $request_body .= '<CategoryID>' . $attributes['SecondaryCategory']['CategoryID'] .
                    '</CategoryID>' . "\n";

            }

            $request_body .= '</SecondaryCategory>' . "\n";

        }

        if (isset($attributes['SellerProfiles'])) {

            $request_body .= '<SellerProfiles>' . "\n";

            if (isset($attributes['SellerProfiles']['SellerPaymentProfile'])) {

                $request_body .= '<SellerPaymentProfile>' . "\n";

                if (isset($attributes['SellerProfiles']['SellerPaymentProfile']['PaymentProfileID'])) {

                    $request_body .= '<PaymentProfileID>' .
                        $attributes['SellerProfiles']['SellerPaymentProfile']['PaymentProfileID'] .
                        '</PaymentProfileID>' . "\n";

                }

                if (isset($attributes['SellerProfiles']['SellerPaymentProfile']['PaymentProfileName'])) {

                    $request_body .= '<PaymentProfileName>' .
                        $attributes['SellerProfiles']['SellerPaymentProfile']['PaymentProfileName'] .
                        '</PaymentProfileName>' . "\n";

                }

                $request_body .= '</SellerPaymentProfile>' . "\n";

            }

            if (isset($attributes['SellerProfiles']['SellerReturnProfile'])) {

                $request_body .= '<SellerReturnProfile>' . "\n";

                if (isset($attributes['SellerProfiles']['SellerReturnProfile']['ReturnProfileID'])) {

                    $request_body .= '<ReturnProfileID>' .
                        $attributes['SellerProfiles']['SellerReturnProfile']['ReturnProfileID'] .
                        '</ReturnProfileID>' . "\n";

                }

                if (isset($attributes['SellerProfiles']['SellerReturnProfile']['ReturnProfileName'])) {

                    $request_body .= '<ReturnProfileName>' .
                        $attributes['SellerProfiles']['SellerReturnProfile']['ReturnProfileName'] .
                        '</ReturnProfileName>' . "\n";

                }

                $request_body .= '</SellerReturnProfile>' . "\n";

            }

            if (isset($attributes['SellerProfiles']['SellerShippingProfile'])) {

                $request_body .= '<SellerShippingProfile>' . "\n";

                if (isset($attributes['SellerProfiles']['SellerShippingProfile']['ShippingProfileID'])) {

                    $request_body .= '<ShippingProfileID>' .
                        $attributes['SellerProfiles']['SellerShippingProfile']['ShippingProfileID'] .
                        '</ShippingProfileID>' . "\n";

                }

                if (isset($attributes['SellerProfiles']['SellerShippingProfile']['ShippingProfileName'])) {

                    $request_body .= '<ShippingProfileName>' .
                        $attributes['SellerProfiles']['SellerShippingProfile']['ShippingProfileName'] .
                        '</ShippingProfileName>' . "\n";

                }

                $request_body .= '</SellerShippingProfile>' . "\n";

            }

            $request_body .= '</SellerProfiles>' . "\n";

        }

        if (isset($attributes['SellerProvidedTitle'])) {

            $request_body .= '<SellerProvidedTitle>' . $attributes['SellerProvidedTitle'] .
                '</SellerProvidedTitle>' . "\n";

        }

        if (isset($attributes['ShippingDetails'])) {

            $request_body .= '<ShippingDetails>' . "\n";

            if (isset($attributes['ShippingDetails']['CalculatedShippingRate'])) {

                $request_body .= '<CalculatedShippingRate>' . "\n";

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['InternationalPackagingHandlingCosts'])) {

                    $request_body .= '<InternationalPackagingHandlingCosts>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['InternationalPackagingHandlingCosts'] .
                        '</InternationalPackagingHandlingCosts>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['MeasurementUnit'])) {

                    $request_body .= '<MeasurementUnit>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['MeasurementUnit'] .
                        '</MeasurementUnit>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['OriginatingPostalCode'])) {

                    $request_body .= '<OriginatingPostalCode>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['OriginatingPostalCode'] .
                        '</OriginatingPostalCode>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['PackageDepth'])) {

                    $request_body .= '<PackageDepth>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['PackageDepth'] .
                        '</PackageDepth>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['PackageLength'])) {

                    $request_body .= '<PackageLength>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['PackageLength'] .
                        '</PackageLength>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['PackageWidth'])) {

                    $request_body .= '<PackageWidth>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['PackageWidth'] .
                        '</PackageWidth>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['PackagingHandlingCosts'])) {

                    $request_body .= '<PackagingHandlingCosts>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['PackagingHandlingCosts'] .
                        '</PackagingHandlingCosts>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['ShippingIrregular'])) {

                    $request_body .= '<ShippingIrregular>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['ShippingIrregular'] .
                        '</ShippingIrregular>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['ShippingPackage'])) {

                    $request_body .= '<ShippingPackage>' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['ShippingPackage'] .
                        '</ShippingPackage>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['WeightMajorUnit']) &&
                    isset($attributes['ShippingDetails']['CalculatedShippingRate']['WeightMajor'])) {

                    $request_body .= '<WeightMajor unit="' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['WeightMajorUnit'] . '">' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['WeightMajor'] .
                        '</WeightMajor>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['CalculatedShippingRate']['WeightMinorUnit']) &&
                    isset($attributes['ShippingDetails']['CalculatedShippingRate']['WeightMinor'])) {

                    $request_body .= '<WeightMinor unit="' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['WeightMinorUnit'] . '">' .
                        $attributes['ShippingDetails']['CalculatedShippingRate']['WeightMinor'] .
                        '</WeightMinor>' . "\n";

                }

                $request_body .= '</CalculatedShippingRate>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['CODCost'])) {

                $request_body .= '<CODCost>' . $attributes['ShippingDetails']['CODCost'] . '</CODCost>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['ExcludeShipToLocation'])) {

                foreach ($attributes['ShippingDetails']['ExcludeShipToLocation'] as $excludeShipToLocation) {

                    $request_body .= '<ExcludeShipToLocation>' . $excludeShipToLocation .
                        '</ExcludeShipToLocation>' . "\n";

                    /* more ExcludeShipToLocation values allowed here */

                }

            }

            if (isset($attributes['ShippingDetails']['GlobalShipping'])) {

                $request_body .= '<GlobalShipping>' . $attributes['ShippingDetails']['GlobalShipping'] .
                    '</GlobalShipping>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InsuranceDetails'])) {

                $request_body .= '<InsuranceDetails>' . "\n";

                if (isset($attributes['ShippingDetails']['InsuranceDetails']['InsuranceFee'])) {

                    $request_body .= '<InsuranceFee>' .
                        $attributes['ShippingDetails']['InsuranceDetails']['InsuranceFee'] .
                        '</InsuranceFee>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['InsuranceDetails']['InsuranceOption'])) {

                    $request_body .= '<InsuranceOption>' .
                        $attributes['ShippingDetails']['InsuranceDetails']['InsuranceOption'] .
                        '</InsuranceOption>' . "\n";

                }

                $request_body .= '</InsuranceDetails>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InsuranceFee'])) {

                $request_body .= '<InsuranceFee>' . $attributes['ShippingDetails']['InsuranceFee'] .
                    '</InsuranceFee>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InsuranceOption'])) {

                $request_body .= '<InsuranceOption>' . $attributes['ShippingDetails']['InsuranceOption'] .
                    '</InsuranceOption>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InternationalInsuranceDetails'])) {

                $request_body .= '<InternationalInsuranceDetails>' . "\n";

                if (isset($attributes['ShippingDetails']['InternationalInsuranceDetails']['InsuranceFee'])) {

                    $request_body .= '<InsuranceFee>' .
                        $attributes['ShippingDetails']['InternationalInsuranceDetails']['InsuranceFee'] .
                        '</InsuranceFee>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['InternationalInsuranceDetails']['InsuranceOption'])) {

                    $request_body .= '<InsuranceOption>' .
                        $attributes['ShippingDetails']['InternationalInsuranceDetails']['InsuranceOption'] .
                        '</InsuranceOption>' . "\n";

                }

                $request_body .= '</InternationalInsuranceDetails>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InternationalPromotionalShippingDiscount'])) {

                $request_body .= '<InternationalPromotionalShippingDiscount>' .
                    $attributes['ShippingDetails']['InternationalPromotionalShippingDiscount'] .
                    '</InternationalPromotionalShippingDiscount>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InternationalShippingDiscountProfileID'])) {

                $request_body .= '<InternationalShippingDiscountProfileID>' .
                    $attributes['ShippingDetails']['InternationalShippingDiscountProfileID'] .
                    '</InternationalShippingDiscountProfileID>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['InternationalShippingServiceOption'])) {

                foreach ($attributes['ShippingDetails']['InternationalShippingServiceOption'] as
                         $internationalShippingServiceOption) {

                    $request_body .= '<InternationalShippingServiceOption>' . "\n";

                    if (isset($internationalShippingServiceOption['ShippingService'])) {

                        $request_body .= '<ShippingService>' .
                            $internationalShippingServiceOption['ShippingService'] .
                            '</ShippingService>' . "\n";

                    }

                    if (isset($internationalShippingServiceOption['ShippingServiceAdditionalCostCurrencyID']) &&
                        isset($internationalShippingServiceOption['ShippingServiceAdditionalCost'])) {

                        $request_body .= '<ShippingServiceAdditionalCost currencyID="' .
                            $internationalShippingServiceOption['ShippingServiceAdditionalCostCurrencyID'] . '">' .
                            $internationalShippingServiceOption['ShippingServiceAdditionalCost'] .
                            '</ShippingServiceAdditionalCost>' . "\n";

                    }

                    if (isset($internationalShippingServiceOption['ShippingServiceCostCurrencyID']) &&
                        isset($internationalShippingServiceOption['ShippingServiceCost'])) {

                        $request_body .= '<ShippingServiceCost currencyID="' .
                            $internationalShippingServiceOption['ShippingServiceCostCurrencyID'] . '">' .
                            $internationalShippingServiceOption['ShippingServiceCost'] .
                            '</ShippingServiceCost>' . "\n";

                    }

                    if (isset($internationalShippingServiceOption['ShippingServicePriority'])) {

                        $request_body .= '<ShippingServicePriority>' .
                            $internationalShippingServiceOption['ShippingServicePriority'] .
                            '</ShippingServicePriority>' . "\n";

                    }

                    if (isset($internationalShippingServiceOption['ShipToLocation'])) {

                        foreach ($internationalShippingServiceOption['ShipToLocation'] as $shipToLocation) {

                            $request_body .= '<ShipToLocation>' . $shipToLocation . '</ShipToLocation>' . "\n";

                            /* more ShipToLocation values allowed here */

                        }

                    }

                    $request_body .= '</InternationalShippingServiceOption>' . "\n";

                    /* more InternationalShippingServiceOption nodes allowed here */

                }

            }

            if (isset($attributes['ShippingDetails']['PaymentInstructions'])) {

                $request_body .= '<PaymentInstructions>' .
                    $attributes['ShippingDetails']['PaymentInstructions'] . '</PaymentInstructions>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['PromotionalShippingDiscount'])) {

                $request_body .= '<PromotionalShippingDiscount>' .
                    $attributes['ShippingDetails']['PromotionalShippingDiscount'] .
                    '</PromotionalShippingDiscount>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['RateTableDetails'])) {

                $request_body .= '<RateTableDetails>' . "\n";

                if (isset($attributes['ShippingDetails']['RateTableDetails']['DomesticRateTable'])) {

                    $request_body .= '<DomesticRateTable>' .
                        $attributes['ShippingDetails']['RateTableDetails']['DomesticRateTable'] .
                        '</DomesticRateTable>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['RateTableDetails']['InternationalRateTable'])) {

                    $request_body .= '<InternationalRateTable>' .
                        $attributes['ShippingDetails']['RateTableDetails']['InternationalRateTable'] .
                        '</InternationalRateTable>' . "\n";

                }

                $request_body .= '</RateTableDetails>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['SalesTax'])) {

                $request_body .= '<SalesTax>' . "\n";

                if (isset($attributes['ShippingDetails']['SalesTax']['SalesTaxPercent'])) {

                    $request_body .= '<SalesTaxPercent>' .
                        $attributes['ShippingDetails']['SalesTax']['SalesTaxPercent'] . '</SalesTaxPercent>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['SalesTax']['SalesTaxState'])) {

                    $request_body .= '<SalesTaxState>' .
                        $attributes['ShippingDetails']['SalesTax']['SalesTaxState'] . '</SalesTaxState>' . "\n";

                }

                if (isset($attributes['ShippingDetails']['SalesTax']['ShippingIncludedInTax'])) {

                    $request_body .= '<ShippingIncludedInTax>' .
                        $attributes['ShippingDetails']['SalesTax']['ShippingIncludedInTax'] .
                        '</ShippingIncludedInTax>' . "\n";

                }

                $request_body .= '</SalesTax>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['ShippingDiscountProfileID'])) {

                $request_body .= '<ShippingDiscountProfileID>' .
                    $attributes['ShippingDetails']['ShippingDiscountProfileID'] . '</ShippingDiscountProfileID>' . "\n";

            }

            if (isset($attributes['ShippingDetails']['ShippingServiceOptions'])) {

                foreach ($attributes['ShippingDetails']['ShippingServiceOptions'] as $shippingServiceOption) {

                    $request_body .= '<ShippingServiceOptions>' . "\n";

                    if (isset($shippingServiceOption['FreeShipping'])) {

                        $request_body .= '<FreeShipping>' .
                            $shippingServiceOption['FreeShipping'] .
                            '</FreeShipping>' . "\n";

                    }

                    if (isset($shippingServiceOption['ShippingService'])) {

                        $request_body .= '<ShippingService>' .
                            $shippingServiceOption['ShippingService'] .
                            '</ShippingService>' . "\n";

                    }

                    if (isset($shippingServiceOption['ShippingServiceAdditionalCostCurrencyID']) &&
                        isset($shippingServiceOption['ShippingServiceAdditionalCost'])) {

                        $request_body .= '<ShippingServiceAdditionalCost currencyID="' .
                            $shippingServiceOption['ShippingServiceAdditionalCostCurrencyID'] . '">' .
                            $shippingServiceOption['ShippingServiceAdditionalCost'] .
                            '</ShippingServiceAdditionalCost>' . "\n";

                    }

                    if (isset($shippingServiceOption['ShippingServiceCostCurrencyID']) &&
                        isset($shippingServiceOption['ShippingServiceCost'])) {

                        $request_body .= '<ShippingServiceCost currencyID="' .
                            $shippingServiceOption['ShippingServiceCostCurrencyID'] . '">' .
                            $shippingServiceOption['ShippingServiceCost'] .
                            '</ShippingServiceCost>' . "\n";

                    }

                    if (isset($shippingServiceOption['ShippingServicePriority'])) {

                        $request_body .= '<ShippingServicePriority>' .
                            $shippingServiceOption['ShippingServicePriority'] .
                            '</ShippingServicePriority>' . "\n";

                    }

                    if (isset($shippingServiceOption['ShippingSurcharge'])) {

                        $request_body .= '<ShippingSurcharge>' .
                            $shippingServiceOption['ShippingSurcharge'] .
                            '</ShippingSurcharge>' . "\n";

                    }

                    $request_body .= '</ShippingServiceOptions>' . "\n";

                    /* more ShippingServiceOptions nodes allowed here */

                }

            }

            if (isset($attributes['ShippingDetails']['ShippingType'])) {

                $request_body .= '<ShippingType>' .
                    $attributes['ShippingDetails']['ShippingType'] . '</ShippingType>' . "\n";

            }

            $request_body .= '</ShippingDetails>' . "\n";

        }

        if (isset($attributes['ShippingPackageDetails'])) {

            $request_body .= '<ShippingPackageDetails>' . "\n";

            if (isset($attributes['ShippingPackageDetails']['MeasurementUnit'])) {

                $request_body .= '<MeasurementUnit>' . $attributes['ShippingPackageDetails']['MeasurementUnit'] .
                    '</MeasurementUnit>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['PackageDepth'])) {

                $request_body .= '<PackageDepth>' . $attributes['ShippingPackageDetails']['PackageDepth'] .
                    '</PackageDepth>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['PackageLength'])) {

                $request_body .= '<PackageLength>' . $attributes['ShippingPackageDetails']['PackageLength'] .
                    '</PackageLength>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['PackageWidth'])) {

                $request_body .= '<PackageWidth>' . $attributes['ShippingPackageDetails']['PackageWidth'] .
                    '</PackageWidth>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['ShippingIrregular'])) {

                $request_body .= '<ShippingIrregular>' . $attributes['ShippingPackageDetails']['ShippingIrregular'] .
                    '</ShippingIrregular>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['ShippingPackage'])) {

                $request_body .= '<ShippingPackage>' . $attributes['ShippingPackageDetails']['ShippingPackage'] .
                    '</ShippingPackage>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['WeightMajorUnit']) &&
                isset($attributes['ShippingPackageDetails']['WeightMajor'])) {

                $request_body .= '<WeightMajor Unit="' .
                    $attributes['ShippingPackageDetails']['WeightMajorUnit'] . '">' .
                    $attributes['ShippingPackageDetails']['WeightMajor'] .
                    '</WeightMajor>' . "\n";

            }

            if (isset($attributes['ShippingPackageDetails']['WeightMinorUnit']) &&
                isset($attributes['ShippingPackageDetails']['WeightMinor'])) {

                $request_body .= '<WeightMinor Unit="' .
                    $attributes['ShippingPackageDetails']['WeightMinorUnit'] . '">' .
                    $attributes['ShippingPackageDetails']['WeightMinor'] .
                    '</WeightMinor>' . "\n";

            }

            $request_body .= '</ShippingPackageDetails>' . "\n";

        }

        if (isset($attributes['ShippingServiceCostOverrideList'])) {

            $request_body .= '<ShippingServiceCostOverrideList>' . "\n";

            if (isset($attributes['ShippingServiceCostOverrideList']['ShippingServiceCostOverride'])) {

                foreach ($attributes['ShippingServiceCostOverrideList']['ShippingServiceCostOverride'] as $shippingServiceCostOverride) {

                    $request_body .= '<ShippingServiceCostOverride>' . "\n";

                    if (isset($shippingServiceCostOverride['ShippingServiceAdditionalCostCurrencyID']) &&
                        isset($shippingServiceCostOverride['ShippingServiceAdditionalCost'])) {

                        $request_body .= '<ShippingServiceAdditionalCost currencyID="' .
                            $shippingServiceCostOverride['ShippingServiceAdditionalCostCurrencyID'] . '">' .
                            $shippingServiceCostOverride['ShippingServiceAdditionalCost'] .
                            '</ShippingServiceAdditionalCost>' . "\n";

                    }

                    if (isset($shippingServiceCostOverride['ShippingServiceCost'])) {

                        $request_body .= '<ShippingServiceCost>' .
                            $shippingServiceCostOverride['ShippingServiceCost'] .
                            '</ShippingServiceCost>' . "\n";

                    }

                    if (isset($shippingServiceCostOverride['ShippingServicePriority'])) {

                        $request_body .= '<ShippingServicePriority>' .
                            $shippingServiceCostOverride['ShippingServicePriority'] .
                            '</ShippingServicePriority>' . "\n";

                    }

                    if (isset($shippingServiceCostOverride['ShippingServiceType'])) {

                        $request_body .= '<ShippingServiceType>' .
                            $shippingServiceCostOverride['ShippingServiceType'] .
                            '</ShippingServiceType>' . "\n";

                    }

                    if (isset($shippingServiceCostOverride['ShippingSurcharge'])) {

                        $request_body .= '<ShippingSurcharge>' .
                            $shippingServiceCostOverride['ShippingSurcharge'] .
                            '</ShippingSurcharge>' . "\n";

                    }

                    $request_body .= '</ShippingServiceCostOverride>' . "\n";

                    /* more ShippingServiceCostOverride nodes allowed here */

                }

            }

            $request_body .= '</ShippingServiceCostOverrideList>' . "\n";

        }

        if (isset($attributes['ShippingTermsInDescription'])) {

            $request_body .= '<ShippingTermsInDescription>' . $attributes['ShippingTermsInDescription'] .
                '</ShippingTermsInDescription>' . "\n";

        }

        if (isset($attributes['ShipToLocations'])) {

            foreach ($attributes['ShipToLocations'] as $shipToLocation) {

                $request_body .= '<ShipToLocations>' . $shipToLocation . '</ShipToLocations>' . "\n";

                /* more ShipToLocations values allowed here */

            }

        }

        if (isset($attributes['Site'])) {

            $request_body .= '<Site>' . $attributes['Site'] . '</Site>' . "\n";

        }

        if (isset($attributes['SKU'])) {

            $request_body .= '<SKU>' . $attributes['SKU'] . '</SKU>' . "\n";

        }

        if (isset($attributes['SkypeContactOption'])) {

            foreach ($attributes['SkypeContactOption'] as $skypeContactOption) {

                $request_body .= '<SkypeContactOption>' . $skypeContactOption . '</SkypeContactOption>' . "\n";

                /* more SkypeContactOption values allowed here */

            }

        }

        if (isset($attributes['SkypeEnabled'])) {

            $request_body .= '<SkypeEnabled>' . $attributes['SkypeEnabled'] . '</SkypeEnabled>' . "\n";

        }

        if (isset($attributes['SkypeID'])) {

            $request_body .= '<SkypeID>' . $attributes['SkypeID'] . '</SkypeID>' . "\n";

        }

        if (isset($attributes['StartPrice'])) {

            $request_body .= '<StartPrice>' . $attributes['StartPrice'] . '</StartPrice>' . "\n";

        }

        if (isset($attributes['Storefront'])) {

            $request_body .= '<Storefront>' . "\n";

            if (isset($attributes['Storefront']['StoreCategory2ID'])) {

                $request_body .= '<StoreCategory2ID>' . $attributes['Storefront']['StoreCategory2ID'] .
                    '</StoreCategory2ID>' . "\n";

            }

            if (isset($attributes['Storefront']['StoreCategory2Name'])) {

                $request_body .= '<StoreCategory2Name>' . $attributes['Storefront']['StoreCategory2Name'] .
                    '</StoreCategory2Name>' . "\n";

            }

            if (isset($attributes['Storefront']['StoreCategoryID'])) {

                $request_body .= '<StoreCategoryID>' . $attributes['Storefront']['StoreCategoryID'] .
                    '</StoreCategoryID>' . "\n";

            }

            if (isset($attributes['Storefront']['StoreCategoryName'])) {

                $request_body .= '<StoreCategoryName>' . $attributes['Storefront']['StoreCategoryName'] .
                    '</StoreCategoryName>' . "\n";

            }

            $request_body .= '</Storefront>' . "\n";

        }

        if (isset($attributes['SubTitle'])) {

            $request_body .= '<SubTitle>' . $attributes['SubTitle'] . '</SubTitle>' . "\n";

        }

        if (isset($attributes['TaxCategory'])) {

            $request_body .= '<TaxCategory>' . $attributes['TaxCategory'] . '</TaxCategory>' . "\n";

        }

        if (isset($attributes['ThirdPartyCheckout'])) {

            $request_body .= '<ThirdPartyCheckout>' . $attributes['ThirdPartyCheckout'] .
                '</ThirdPartyCheckout>' . "\n";

        }

        if (isset($attributes['ThirdPartyCheckoutIntegration'])) {

            $request_body .= '<ThirdPartyCheckoutIntegration>' . $attributes['ThirdPartyCheckoutIntegration'] .
                '</ThirdPartyCheckoutIntegration>' . "\n";

        }

        if (isset($attributes['Title'])) {

            $request_body .= '<Title>' . $attributes['Title'] . '</Title>' . "\n";

        }

        if (isset($attributes['UseRecommendedProduct'])) {

            $request_body .= '<UseRecommendedProduct>' . $attributes['UseRecommendedProduct'] . '</UseRecommendedProduct>' . "\n";

        }

        if (isset($attributes['UseTaxTable'])) {

            $request_body .= '<UseTaxTable>' . $attributes['UseTaxTable'] . '</UseTaxTable>' . "\n";

        }

        if (isset($attributes['UUID'])) {

            $request_body .= '<UUID>' . $attributes['UUID'] . '</UUID>' . "\n";

        }

        if (isset($attributes['Variations'])) {

            $request_body .= '<Variations>' . "\n";

            if (isset($attributes['Variations']['Pictures'])) {

                $request_body .= '<Pictures>' . "\n";

                if (isset($attributes['Variations']['Pictures']['VariationSpecificName'])) {

                    $request_body .= '<VariationSpecificName>' .
                        $attributes['Variations']['Pictures']['VariationSpecificName'] .
                        '</VariationSpecificName>' . "\n";

                }

                if (isset($attributes['Variations']['Pictures']['VariationSpecificPictureSet'])) {

                    foreach ($attributes['Variations']['Pictures']['VariationSpecificPictureSet'] as
                             $variationSpecificPictureSet) {

                        $request_body .= '<VariationSpecificPictureSet>' . "\n";


                        if (isset($variationSpecificPictureSet['PictureURL'])) {

                            foreach ($variationSpecificPictureSet['PictureURL'] as $pictureURL)

                                $request_body .= '<PictureURL>' . $pictureURL . '</PictureURL>' . "\n";

                            /* more PictureURL values allowed here */

                        }

                        if (isset($variationSpecificPictureSet['VariationSpecificValue'])) {

                            $request_body .= '<VariationSpecificValue>' .
                                $variationSpecificPictureSet['VariationSpecificValue'] .
                                '</VariationSpecificValue>' . "\n";

                        }

                        $request_body .= '</VariationSpecificPictureSet>' . "\n";

                        /* more VariationSpecificPictureSet nodes allowed here */

                    }

                }

                $request_body .= '</Pictures>' . "\n";

            }

            if (isset($attributes['Variations']['Variation'])) {

                foreach ($attributes['Variations']['Variation'] as $variation) {

                    $request_body .= '<Variation>' . "\n";

                    if (isset($variation['DiscountPriceInfo'])) {

                        $request_body .= '<DiscountPriceInfo>' . "\n";

                        if (isset($variation['DiscountPriceInfo']['MadeForOutletComparisonPrice'])) {

                            $request_body .= '<MadeForOutletComparisonPrice>' .
                                $variation['DiscountPriceInfo']['MadeForOutletComparisonPrice'] .
                                '</MadeForOutletComparisonPrice>' . "\n";

                        }

                        if (isset($variation['DiscountPriceInfo']['MinimumAdvertisedPrice'])) {

                            $request_body .= '<MinimumAdvertisedPrice>' .
                                $variation['DiscountPriceInfo']['MinimumAdvertisedPrice'] .
                                '</MinimumAdvertisedPrice>' . "\n";

                        }

                        if (isset($variation['DiscountPriceInfo']['MinimumAdvertisedPriceExposure'])) {

                            $request_body .= '<MinimumAdvertisedPriceExposure>' .
                                $variation['DiscountPriceInfo']['MinimumAdvertisedPriceExposure'] .
                                '</MinimumAdvertisedPriceExposure>' . "\n";

                        }

                        if (isset($variation['DiscountPriceInfo']['OriginalRetailPrice'])) {

                            $request_body .= '<OriginalRetailPrice>' .
                                $variation['DiscountPriceInfo']['OriginalRetailPrice'] .
                                '</OriginalRetailPrice>' . "\n";

                        }

                        if (isset($variation['DiscountPriceInfo']['SoldOffeBay'])) {

                            $request_body .= '<SoldOffeBay>' .
                                $variation['DiscountPriceInfo']['SoldOffeBay'] .
                                '</SoldOffeBay>' . "\n";

                        }

                        if (isset($variation['DiscountPriceInfo']['SoldOneBay'])) {

                            $request_body .= '<SoldOneBay>' .
                                $variation['DiscountPriceInfo']['SoldOneBay'] .
                                '</SoldOneBay>' . "\n";

                        }

                        $request_body .= '</DiscountPriceInfo>' . "\n";

                    }

                    if (isset($variation['Quantity'])) {

                        $request_body .= '<Quantity>' . $variation['Quantity'] . '</Quantity>' . "\n";

                    }

                    if (isset($variation['SKU'])) {

                        $request_body .= '<SKU>' . $variation['SKU'] . '</SKU>' . "\n";

                    }

                    if (isset($variation['StartPrice'])) {

                        $request_body .= '<StartPrice>' . $variation['StartPrice'] . '</StartPrice>' . "\n";

                    }

                    if (isset($variation['VariationProductListingDetails'])) {

                        $request_body .= '<VariationProductListingDetails>' . "\n";

                        if (isset($variation['VariationProductListingDetails']['EAN'])) {

                            $request_body .= '<EAN>' . $variation['VariationProductListingDetails']['EAN'] .
                                '</EAN>' . "\n";

                        }

                        if (isset($variation['VariationProductListingDetails']['ISBN'])) {

                            $request_body .= '<ISBN>' . $variation['VariationProductListingDetails']['ISBN'] .
                                '</ISBN>' . "\n";

                        }

                        if (isset($variation['VariationProductListingDetails']['UPC'])) {

                            $request_body .= '<UPC>' . $variation['VariationProductListingDetails']['UPC'] .
                                '</UPC>' . "\n";

                        }

                        $request_body .= '</VariationProductListingDetails>' . "\n";

                    }

                    if (isset($variation['VariationSpecifics'])) {

                        foreach ($variation['VariationSpecifics'] as $variationSpecific) {

                            $request_body .= '<VariationSpecifics>' . "\n";

                            foreach ($variationSpecific['NameValueList'] as $nameValueList) {

                                $request_body .= '<NameValueList>' . "\n";

                                $request_body .= '<Name>' . $nameValueList['Name'] . '</Name>' . "\n";

                                foreach ($nameValueList['Value'] as $value) {

                                    $request_body .= '<Value>' . $value . '</Value>' . "\n";

                                    /* more Value values allowed here */

                                }

                                $request_body .= '</NameValueList>' . "\n";

                                /* more NameValueList nodes allowed here */

                            }

                            $request_body .= '</VariationSpecifics>' . "\n";

                            /* more VariationSpecifics nodes allowed here */

                        }

                    }

                    $request_body .= '</Variation>' . "\n";

                    /* more Variation nodes allowed here */

                }

            }

            if (isset($attributes['Variations']['VariationSpecificsSet'])) {

                $request_body .= '<VariationSpecificsSet>' . "\n";

                foreach ($attributes['Variations']['VariationSpecificsSet']['NameValueList'] as $nameValueList) {

                    $request_body .= '<NameValueList>' . "\n";
                    $request_body .= '<Name>' . $nameValueList['Name'] . '</Name>' . "\n";

                    foreach ($nameValueList['Value'] as $value) {

                        $request_body .= '<Value>' . $value . '</Value>' . "\n";

                        /* more Value values allowed here */

                    }

                    $request_body .= '</NameValueList>' . "\n";

                    /* more NameValueList nodes allowed here */

                }

                $request_body .= '</VariationSpecificsSet>' . "\n";

            }

            $request_body .= '</Variations>' . "\n";

        }

        if (isset($attributes['VATDetails'])) {

            $request_body .= '<VATDetails>' . "\n";

            if (isset($attributes['VATDetails']['BusinessSeller'])) {

                $request_body .= '<BusinessSeller>' . $attributes['VATDetails']['BusinessSeller'] .
                    '</BusinessSeller>' . "\n";

            }

            if (isset($attributes['VATDetails']['RestrictedToBusiness'])) {

                $request_body .= '<RestrictedToBusiness>' . $attributes['VATDetails']['RestrictedToBusiness'] .
                    '</RestrictedToBusiness>' . "\n";

            }

            if (isset($attributes['VATDetails']['VATPercent'])) {

                $request_body .= '<VATPercent>' . $attributes['VATDetails']['VATPercent'] . '</VATPercent>' . "\n";

            }

            $request_body .= '</VATDetails>' . "\n";

        }

        if (isset($attributes['VIN'])) {

            $request_body .= '<VIN>' . $attributes['VIN'] . '</VIN>' . "\n";

        }

        if (isset($attributes['VRM'])) {

            $request_body .= '<VRM>' . $attributes['VRM'] . '</VRM>' . "\n";

        }

        $request_body .= '</Item>' . "\n";

        /* Standard Input Fields */

        if (isset($attributes['MessageID'])) {

            $request_body .= '<MessageID>' . $attributes['MessageID'] . '</MessageID>' . "\n";

        }

        $request_body .= '<ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>' . "\n";
        $request_body .= '<Version>' . $this->api_compatibility_level . '</Version>' . "\n";
        $request_body .= '<WarningLevel>' . $this->api_warning_level . '</WarningLevel>' . "\n";
        $request_body .= '</AddFixedPriceItemRequest>';

        $responseXml = L5ebtapiController::request('AddFixedPriceItem', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: addFixedPriceItem() 404 Not Found');

            die('<P>Error sending request. 404 Not Found.</P>');

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: addFixedPriceItem() Error sending request. The XML response is an empty string');

            die('<P>Error sending request. The XML response is an empty string.</P>');

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

                    } else {

                        Log::error('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                    $xml = simplexml_load_string($responseDoc->saveXML());

                    return $xml;

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

            return $xml;

        }

    }// END - public function addFixedPriceItem(array $attributes)


    /**
     * Method: request($call_name, $request_body) - Make an eBay API request.
     *
     * @param $call_name the eBay API call name
     * @param $request_body the body of the request
     *
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