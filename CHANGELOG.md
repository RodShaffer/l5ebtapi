# Changelog

All Notable changes to `l5ebtapi` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

#####12/02/2017 - I apologize for the inactivity on the L5ebtapi package but I have had some unforeseen family issues come up and I no longer have time to invest into the L5ebtapi package. If anyone has interest in taking over the project feel free to clone the project on<a href="https://github.com/rodshaffer/l5ebtapi">Github</a>. The project was developed using PhpStorm 2017

### Added
Config file for the app's eBay developer credentials. Use vendor:publish to publish a default config file(l5ebtapi.php)
to the config directory. Make sure to edit and save this file before using l5ebtapi.

- Method: getEbayOfficialTime(array $attributes) - get the eBay official time.

- Method: getSessionId(array $attributes) - Use this call to retrieve a SessionID, which is a unique identifier
that you use for authentication during the token-creation process. You must have a valid SessionID value in order to
make a FetchToken request.

- Method: fetchToken(array $attributes) - Use this call to retrieve an authentication token for a user. The call can be
used to get a token only after the specified user has given consent for the token to be generated. Consent is given
through the eBay sign-in page. After token retrieval, the token can be used to authenticate other calls made on behalf
of the associated user.

- Method: getEbayDetails(array $attributes) - Get various eBay metadata. Example. get the eBay shipping service options.

- Method: getCategories(array $attributes) - Use this call to retrieve the latest category hierarchy for the eBay
site specified in the CategorySiteID property. By default, this is the site to which you submit the request.
 
 - Method: getCategoryFeatures(array $attributes) - returns information that describes the feature and value settings
 that apply to the set of eBay categories.
 
 - Method: getCategorySpecifics(array $attributes) - Use this call to retrieve the most popular Item Specifics that
 sellers can use when they list items in certain categories.
 
 - Method: getItem(array $attributes) - Retrieves the eBay item detail.
 
 - Method: uploadSiteHostedPictures(array $attributes, $image) - Use this call to upload a picture to eBay Picture
  Services (EPS). You can either include a binary attachment or supply a URL in the ExternalPictureURL field to the
  location of the picture on an external web server.
 
- Method: addFixedPriceItem(array $attributes) - Add an eBay fixed priced item listing. All options now supported.

 I will be updating the documentation in the near future. I will also be changing the location that the documentation
 is stored. This will hopefully make the readability better. So please stay tuned for the changes that are coming.
 
 The addfixedpriceitem method now supports all options. If you find any bugs please send me an email at the following
 email address htmlplus43@yahoo.com
 
 I also fixed the uploadsitehostedpictures method to support all options. If using an external picture url simply set
 the image parameter to NULL like the following example. uploadSiteHostedPictures($attributes, NULL)

### Deprecated
- Nothing.

### Fixed
- Changed the return type to SimpleXMLElement object for all methods. This allows access to the XML key/values and also
a DomDocument object can be easily created if desired.
 
### Removed
- Testing section. No testing setup at this time.

### Security
- Nothing.
