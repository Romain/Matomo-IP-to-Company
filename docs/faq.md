## FAQ

__How to install this plugin?__

Install it via Matomo Marketplace.

__How reliable is this data?__

The collected company names are based on the PHP function `gethostbyaddr`. This function returns the name of the company provided by the proxy used by the user.

Most of the big companies have their own proxy set up with a real name configured. But SMBs may not and in this case, you could see the name of their ISP appear.

Therefore, this information is not 100% reliable but this is still an interesting information to check from time to time.

__Where to find the report once the plugin is activated?__

Once you've activated your plugin, you'll see in the `Visitors` tab of each website a new `Companies` subcategory. This is were your new report lies.

You can also add this report as a widget to your dashboards.

__Which Matomo versions are compatible with this plugin?__

Your Matomo version should be between 3.9.0 and below 4.0.0.
