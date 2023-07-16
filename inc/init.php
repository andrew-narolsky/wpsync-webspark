<?php

// Import Samples
add_action('admin_enqueue_scripts', function () {
    if (basename($_SERVER['PHP_SELF']) == 'admin.php' || (isset($_GET['page']) && $_GET['page'] == 'wpsync-webspark')) {
        wp_enqueue_script('wpsync-webspark-scripts', plugins_url('../js/scripts.js', __FILE__), array(), '20151604', true);
    }
}, 99);