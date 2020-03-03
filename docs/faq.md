## FAQ

__How to install this plugin?__

Install it via Matomo Marketplace.

__How reliable is this data?__

The collected company names are based on the PHP function `gethostbyaddr`. This function returns the name of the company provided by the proxy used by the user.

Most of the big companies have their own proxy set up with a real name configured. But SMBs may not and in this case, you could see the name of their ISP appear.

Therefore, this information is not 100% reliable but this is still an interesting information to check from time to time.

If you have an access token set up for [IPInfo.io](https://ipinfo.io/), this plugin will use this data in the first place, before falling back to `gethostbyaddr`;

__Where do I find the report once the plugin is activated?__

Once you've activated your plugin, you'll see in the `Visitors` tab of each website a new `Companies` subcategory. This is were your new report lies.

You can also add this report as a widget to your dashboards.

__Which Matomo versions are compatible with this plugin?__

Your Matomo version should be between 3.11.0 and below 4.0.0.

__Can I receive this report by email?__

Yes! As of version 0.3.0, you can receive this report by email. You just have to go to Settings > Personal Settings and check the checkbox located in the IPtoCompany section which asks you if you want to subscribe to this report.

This report will then be sent to you once a day, for each site that you have access to, with the list of companies that visited your website the day before.

![Image of Yaktocat](https://github.com/Romain/Matomo-IP-to-Company/tree/master/screenshots/user-setting-subscribe-to-email-report.png)
