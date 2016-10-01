# Changelog

All Notable changes to `l5ebtapi` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - 2017-01-31

### Added
- Method: getEbayOfficialTime() - get the eBay official time.
- Method: getEbayDetails(array $attributes) - Get various eBay metadata. Example. get the eBay shipping service options.
- Method: getCategories(array $attributes) - Use this call to retrieve the latest category hierarchy for the eBay
site specified in the CategorySiteID property. By default, this is the site to which you submit the request.
- Method: uploadSiteHostedPictures($image, $image_name) - Upload an image to the eBay Picture Service.
- Method: addFixedPriceItem(array $attributes) - Add an eBay fixed priced item listing. Method works but is not complete.
 Does not support all the options. I have tested this method using Flat rate shipping (No international shipping support
 at this time) I have not tested with Calculated shipping. Not all options supported at this time. I have an example on
 the Wiki which will show what is supported at this time.
- Method: getItem($item_id) - Retrieves the eBay item detail for the given eBay item id.

### Deprecated
- Nothing.

### Fixed
- Changed the return type to SimpleXML Object for all methods except getEbayOfficialTime and uploadSiteHostedPictures
- (Both getEbayOfficialTime and uploadSiteHostedPictures return an array)
 
### Removed
- Testing section. No testing setup at this time.

### Security
- Nothing.
