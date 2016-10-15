# Changelog

All Notable changes to `l5ebtapi` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - 2017-01-31

### Added
- Method: getEbayOfficialTime() - get the eBay official time.
- Method: getEbayDetails(array $attributes) - Get various eBay metadata. Example. get the eBay shipping service options.
- Method: getCategories(array $attributes) - Use this call to retrieve the latest category hierarchy for the eBay
site specified in the CategorySiteID property. By default, this is the site to which you submit the request.
- Method: uploadSiteHostedPictures(array $attributes, $image) - Use this call to upload a picture to eBay Picture
 Services (EPS). You can either include a binary attachment or supply a URL in the ExternalPictureURL field to the
 location of the picture on an external web server.
- Method: addFixedPriceItem(array $attributes) - Add an eBay fixed priced item listing. All features now supported.
- Method: getItem(array $attributes) - Retrieves the eBay item detail.

### Deprecated
- Nothing.

### Fixed
- Changed the return type to SimpleXML Object for all methods except getEbayOfficialTime and uploadSiteHostedPictures
- (Both getEbayOfficialTime and uploadSiteHostedPictures return an array)
 
### Removed
- Testing section. No testing setup at this time.

### Security
- Nothing.
