Simple and straightforward.

## Installation
1. Download the plugin, extract the plugin files at `{wordpress_project}/wp-content/plugins/`.

2. Go to **WordPress Admin** > **Installed Plugin** to activate the plugin.

<img width="1279" alt="Screen Shot 2563-07-20 at 05 13 54" src="https://user-images.githubusercontent.com/2154669/87886621-dcdabc00-ca48-11ea-99e9-f71bfcaccc34.png">

3. After activated the plugin, you may click a link from the sidebar menu, **Omise Order Handler**.

4. At **Omise Order Handler** page, enter a specific time to run a script.

<img width="1279" alt="Screen Shot 2563-07-20 at 05 14 16" src="https://user-images.githubusercontent.com/2154669/87886509-e283d200-ca47-11ea-8691-60a29969d2df.png">

> Note, this script is basically a replica of `woocommerce/includes/wc-order-functions.php::wc_cancel_unpaid_orders()`.
> However, it will only do query `unpaid` orders, then update to `cancelled`, without touching on the product-stock.
