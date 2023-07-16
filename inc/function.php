<?php

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

const API_URL = 'https://wp.webspark.dev/wp-api/products';

add_action('admin_menu', function () {
    add_menu_page('WpSync Webspark', 'WpSync Webspark', 'manage_options', 'wpsync-webspark', 'add_my_setting', 'dashicons-update-alt', 85);
});

function add_my_setting(): void {
    ?>
    <div class="wrap">
        <h2><?php echo get_admin_page_title() ?></h2>
        <div class="card import_samples">
            <h2 class="title">Synchronize your products</h2>
            <form action="" id="start_synchronize">
                <input type="hidden" name="admin-url" value="<?php echo admin_url('admin-ajax.php'); ?>">
                <input type="hidden" name="action" value="synchronize">
                <button type="submit"
                        class="button button-primary button-large" style="display: block; margin-top: 10px; margin-bottom: 10px;">Start synchronizer
                </button>
            </form>
            <div class="error-message" style="display: none; margin-top: 10px;"></div>
            <div class="success-message" style="color: green; display: none; margin-top: 10px;"></div>
        </div>
    </div>
    <?php
}

/**
 *  Synchronize product
 */
function start_synchronizer_callback(): void {

    $products = get_products();

    if (!$products->error) {

        foreach ($products->data as $key => $item) {
            WC()->queue()->add('my_job_action', [$item]);
        }
    }
}

add_action('wp_ajax_synchronize', 'start_synchronizer_callback');

/**
 *  Save product
 */
function my_job_queue($item): void {

    $product_id = wc_get_product_id_by_sku($item['sku']);

    if (!$product_id) {
        // Create
        $product = new WC_Product_Simple();
        if ($item['name']) {
            $product->set_name($item['name']);
        }
        if ($item['sku']) {
            $product->set_sku($item['sku']);
        }
        if ($item['price']) {
            $product->set_regular_price(substr($item['price'], 1));
        }
        if ($item['description']) {
            $product->set_short_description($item['description']);
        }
        if ($item['in_stock']) {
            $product->set_stock_status('instock');
            $product->set_manage_stock(true);
            $product->set_stock_quantity($item['in_stock']);
        }

    } else {
        // Update
        $product = wc_get_product_object('simple', $product_id);
        if ($item['name'] !== $product->get_name()) {
            $product->set_name($item['name']);
        }
        if ($item['sku'] !== $product->get_sku()) {
            $product->set_sku($item['sku']);
        }
        if (substr($item['price'], 1) !== $product->get_regular_price()) {
            $product->set_regular_price(substr($item['price'], 1));
        }
        if ($item['description'] !== $product->get_short_description()) {
            $product->set_short_description($item['description']);
        }
        if ($item['in_stock'] && ($item['in_stock'] !== $product->get_stock_quantity())) {
            $product->set_stock_status('instock');
            $product->set_manage_stock(true);
            $product->set_stock_quantity($item['in_stock']);
        } else if (!$item['in_stock']) {
            $product->set_stock_status('outofstock');
            $product->set_manage_stock(false);
            $product->set_stock_quantity(0);
        }
    }

    $url = 'https://loremflickr.com/json/640/480/abstract';
    $image_id = media_sideload_image(get_image_url($url), 0, '', 'id');

    if (!is_wp_error($image_id)) {
        $product->set_image_id($image_id);
    }

    $product->set_status('publish');
    $product->save();
}

add_action('my_job_action', 'my_job_queue', 10, 2);

/**
 *  Get product image link
 */
function get_image_url($url): string {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Cookie: PHPSESSID=f1282e5baacced56fd15610df99bba18'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response)->file;
}

/**
 *  Get products from API
 */
function get_products(): object {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response);
}

/**
 *  Start cron
 */
function sfy_add_my_cron_event(): void {
    if (!wp_next_scheduled('sfy_hourly_event')) {
        wp_schedule_event(time(), 'hourly', 'sfy_hourly_event');
    }
}

add_action('wp', 'sfy_add_my_cron_event');
add_action('sfy_hourly_event', 'start_synchronizer_callback');

//wp_clear_scheduled_hook('sfy_hourly_event');
