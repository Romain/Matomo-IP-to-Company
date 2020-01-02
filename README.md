# Matomo IPtoCompany Plugin

## Description

This plugin is meant to be installed on Matomo. It provides you with the name of the company which holds the IP that visited your website.

## Installation

To install this plugin, there are some simple steps to follow:

1. copy this repository on your machine
2. create a ZIP file of the folder
3. upload this ZIP file into your Matomo installation via the interface to install this plugin

## How reliable are these data

The collected company names are based on the PHP function `gethostbyaddr`. This function returns the name of the company provided by the proxy used by the user.

Most of the big companies have their own proxy set up with a real name configured. But SMBs may not and in this case, you could see the name of their ISP appear.

Therefore, this information is not 100% reliable but this is still an interesting information to check from time to time.
