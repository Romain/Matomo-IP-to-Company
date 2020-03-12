# Matomo IPtoCompany Plugin

> New in version 0.3.0
> - Daily email report containing the list of companies that visited your website

## Description

This plugin is meant to be installed on Matomo. It provides you with the name of the company which holds the IP that visited your website.

You can also use [IPInfo.io](https://ipinfo.io/) to get a more reliable result if you have an account. You will just have to set your access token in the General Parameters of Matomo.

This plugin has first been developed for the needs of the company I've been working for, [Wipsim](https://www.wipsim.fr/?pk_campaign=MatomoPlugin-Link&pk_source=matomoplugin&pk_medium=matomo)

## License

GPL v3 or later

## Requirements

Your Matomo version should be between 3.9.0 and below 4.0.0.

## Installation

Install it via Matomo Marketplace.

## Where to find the report once the plugin is activated?

Once you've activated your plugin, you'll see in the `Visitors` tab of each website a new `Companies` subcategory. This is were your new report lies.

You can also add this report as a widget to your dashboards.

## How reliable is this data?

The collected company names are based on the PHP function `gethostbyaddr`. This function returns the name of the company provided by the proxy used by the user.

Most of the big companies have their own proxy set up with a real name configured. But SMBs may not and in this case, you could see the name of their ISP appear.

Therefore, this information is not 100% reliable but this is still an interesting information to check from time to time.

If you have an access token set up for [IPInfo.io](https://ipinfo.io/), this plugin will use this data in the first place, before falling back to `gethostbyaddr`;

## Can I receive this report by email?

Yes! As of version 0.3.0, you can receive this report by email. You just have to go to Settings > Personal Settings and check the checkbox located in the IPtoCompany section which asks you if you want to subscribe to this report.

This report will then be sent to you once a day, for each site that you have access to, with the list of companies that visited your website the day before.

![How to activate the email report](https://github.com/Romain/Matomo-IP-to-Company/blob/master/screenshots/user-setting-subscribe-to-email-report.png)

## Can I define the lifetime of the cache?

As of version 0.4.0, yes, you can. In the general settings section, you can set the number of weeks during which the results should be kept in cache. By default, we keep them 2 weeks, and the minimum is 1 week.
