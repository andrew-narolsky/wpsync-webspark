<?php
/*
Plugin Name: WpSync Webspark
Plugin URI: https://siteforyou.org/
Description: Goods Synchronizer from API
Author: SiteForYou
Author URI: https://siteforyou.org/
*/

$currentFile = __FILE__;
$currentFolder = dirname($currentFile);

//file folders
define('DRC_DIR', plugin_basename( __FILE__ ));
define('DRC_INC', $currentFolder . '/inc');

//include  files
require_once DRC_INC . '/init.php';
require_once DRC_INC . '/function.php';

register_activation_hook(DRC_DIR,'goods_synchronizer_install');
register_deactivation_hook(DRC_DIR, 'goods_synchronizer_deactivation');
register_uninstall_hook(DRC_DIR, 'goods_synchronizer_delete');

function goods_synchronizer_install () {
    /*register function*/
}

function goods_synchronizer_deactivation () {
    /*unregister function*/
}

function goods_synchronizer_delete () {
    /*delete function*/
}
