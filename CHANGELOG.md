# Changelog

All Notable changes to `l5ebtapi` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - 2016-07-29

### Added
- Method: getEbayOfficialTime() - get the eBay official time API call.
- Method: getEbayDetails($detailName) - Get various eBay metadata. Example. get the eBay shipping service options.
- Method: uploadSiteHostedPictures($multiPartImageData, $image_name) - Upload an image to the eBay Picture Service.
- Method: addFixedPriceItem(array $attributes) - Add an eBay fixed priced item listing. Method works but is not complete.
 Does not support all the options. I have tested this method using Flat rate shipping (No international shipping support
 at this time) I have not tested with Calculated shipping. Not all options supported at this time. I have an example on
 the Wiki which will show what is supported at this time.
- Method: getItem($item_id) - Retrieves the eBay item detail for the given eBay item id.

### Deprecated
- Nothing.

### Fixed
- Changed the return type for getEbayDetails($detailName) from array to simpleXml due to some data loss when converting
 to an array.
 
### Removed
- Testing section. No testing setup at this time.

### Security
- Nothing.
