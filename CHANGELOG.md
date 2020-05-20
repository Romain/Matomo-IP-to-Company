## Changelog

### 0.4.1

*[2020-05-20]*

- Fixed a bug that occured when we tried to get the company details based on an IP that is not a valid IP address.

### 0.4.0

*[2020-03-12]*

- Creation of a parameter to set the lifetime of the cache according to the preferences of the user.

### 0.3.5
### 0.3.4

*[2020-03-06]*

- Bumping the version.

### 0.3.3

*[2020-03-06]*

- The superusers should not received the report if no user (including them) have agreed to receive it.

### 0.3.2

*[2020-03-04]*

- Increased the plugin version for the publication to work on the marketplace

### 0.3.1

*[2020-03-04]*

- Use the date of yesterday for the email report

### 0.3.0

*[2020-03-03]*

- Sends an email on a daily basis with the list of companies that visited the website the day before.

### 0.2.8

*[2020-02-25]*

- Commit the Changelog and the plugin.json with the tag

### 0.2.7

*[2020-02-25]*

- Conditionally apply FILTER_SANITIZE_MAGIC_QUOTES or FILTER_SANITIZE_ADD_SLASHES filters based on PHP version

### 0.2.6

*[2020-01-07]*

- Commit the Changelog and the plugin.json with the tag

### 0.2.5

*[2020-01-07]*

- Fixed the bug introduced by Matomo 1.13.1 when we gathered the visits using Live plugin API
- Fixed the way we passed the data to the view (it was generating warnings)
- Fixed the way we called the Exception class

### 0.2.4

*[2020-01-07]*

- Fixed a bug in the primary key definition in the migration

### 0.2.3

*[2020-01-06]*

- Update the plugin number in the JSON

### 0.2.2

*[2020-01-06]*

- Update the plugin number in the JSON

### 0.2.1

*[2020-01-06]*

- Create the table during installation and not activation.

### 0.2.0

*[2020-01-06]*

- Creation of an API endpoint to get the content of the table `ip_to_company`.
- Cache the data gathered from IPInfo.io into the table during 1 week.
- Creation of private methods to gather the content either from the table or from IPInfo.io.
- Creation of a table `ip_to_company` upon activation.
- Creation of a setting to store IPinfo.io access token.

### 0.1.7

*[2020-01-06]*

- Removed the `filter_sort_column` to avoid warnings with Matomo 3.13.1-b2.

### 0.1.6

*[2020-01-02]*

- Changed the default requirements

### 0.1.5

*[2020-01-02]*

- Rollback on the name of the plugin

### 0.1.4

*[2020-01-02]*

- Changed the default requirements
- Changed the name of the plugin

### 0.1.3

*[2020-01-02]*

- Changed the default requirements

### 0.1.2

*[2020-01-02]*

- Fixed a bug in the plugin version in the JSON file

### 0.1.1

*[2020-01-02]*

- Improved the description of the plugin

### 0.1.0

*[2020-01-02]*

First working version of the plugin:

- creation of a report in the `Visitors` tab, called `Companies` with the list of IPs that visited the website, and the company name associated
- ability to add a widget to the dashboards with this report
