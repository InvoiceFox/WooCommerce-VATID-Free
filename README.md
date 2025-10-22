# WooCommerce VATID FREE

A simple WordPress plugin that adds a VAT Number field to WooCommerce checkout (both Classic and Block checkout types).

## Features

- VAT Number field on Classic and Block checkout
- Stores VAT number in order meta as `vat_number`
- Displays VAT number in WooCommerce admin order view
- Includes VAT number in order confirmation emails
- Debug logging support
- Translation ready

## Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/woocommerce-vatid-free/` directory
3. Activate the plugin through the WordPress admin 'Plugins' menu

## Requirements

- WordPress 5.0+
- WooCommerce 6.0+
- For Block checkout: WooCommerce Blocks extension

## Usage

Once activated, the VAT Number field will automatically appear on your WooCommerce checkout pages. The field is optional by default and will be saved with each order for reference.

## Debug Logging

Enable WordPress debug mode (`WP_DEBUG = true`) to see plugin activity in your error logs.

## License

GPL2
