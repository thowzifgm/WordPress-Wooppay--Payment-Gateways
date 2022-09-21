# WordPress Wooppay Payment Gateways
WordPress Woopkassa payment gateway plugin.

## Requirements

The PHP SOAP extension must be installed and enabled for the module to work.

## Installation

1. Go to the administrator section.
2. Install the plugins wooppay-1.1.5/wooppay-1.1.5 mobile depending on the selected payment acceptance tool: go to the Plugins page, click Add New -> Upload Plugin, upload the unpacked module in .zip format.
3. Activate plugins in WooCommerce -> Settings -> Payments.
4. In the settings of each of them, enter your data.
````
Example:

API URL: http://www.test.wooppay.com/api/wsdl

API Username: test_merch

API Password: A12345678a

Order prefix: mobile

Service name: test_merch_invoice
````
You can get the WSDL link in the merchant's account, in the section Online payment acceptance -> WSDL.

Go to the store and make payment.