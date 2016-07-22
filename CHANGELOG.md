# Changelog

All Notable changes to `l5ebtapi` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - 2016-07-29

### Added
- Method: getEbayOfficialTime() - get the eBay official time API call.
- Method: getEbayDetails($detailName) - Get various eBay metadata. Example. get the eBay shipping service options.
- Method: uploadSiteHostedPictures($multiPartImageData, $image_name) - Upload an image to the eBay Picture Service.
- Method: addFixedPriceItem(array $attributes) - Add an eBay fixed priced item listing. Method works but is not complete. Does not support all the options.

### Deprecated
- Nothing.

### Fixed
- Fixed the Wiki documentation. Brought it up to date and also posted examples.
- Please note: The addFixedPriceItem API method is not complete and does not work fully. Some of the options are not
- currently supported like the sipping options for example. See my example on the Wiki as this will show you what is
- currently supported.

### Removed
- Testing section. No testing setup at this time.

### Security
- Nothing.
