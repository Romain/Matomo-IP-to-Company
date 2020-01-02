# Matomo IPtoCompany Plugin

## Description

This plugin is meant to be installed on Matomo. It provides you with the name of the company which holds the IP that visited your website.

## License

GPL v3 or later

## Installation

Install it via Matomo Marketplace.

## Where to find the report once the plugin is activated?

Once you've activated your plugin, you'll see in the `Visitors` tab of each website a new `Companies` subcategory. This is were your new report lies.

You can also add this report as a widget to your dashboards.

## How reliable is this data?

The collected company names are based on the PHP function `gethostbyaddr`. This function returns the name of the company provided by the proxy used by the user.

Most of the big companies have their own proxy set up with a real name configured. But SMBs may not and in this case, you could see the name of their ISP appear.

Therefore, this information is not 100% reliable but this is still an interesting information to check from time to time.
