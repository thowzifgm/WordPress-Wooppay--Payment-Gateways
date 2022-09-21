<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2012-2015 Wooppay
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Wooppay Payment Gateways
 * Plugin URI:
 * Description:       Add Wooppay Payment Gateways for WooCommerce.
 * Version:           1.0
 * Author:            Abdullah Thowzif Hameed
 * License:           The MIT License (MIT)
 *
 */

function woocommerce_cpg_fallback_notice()
{
	echo '<div class="error"><p>' . sprintf(__('WooCommerce Wooppay Gateways depends on the last version of %s to work!', 'wooppay'), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>') . '</p></div>';
}

function custom_payment_gateway_load()
{
	if (!class_exists('WC_Payment_Gateway')) {
		add_action('admin_notices', 'woocommerce_cpg_fallback_notice');
		return;
	}

	function wc_Custom_add_gateway($methods)
	{
		$methods[] = 'WC_Gateway_Wooppay';
		return $methods;
	}

	add_filter('woocommerce_payment_gateways', 'wc_Custom_add_gateway');


	require_once plugin_dir_path(__FILE__) . 'class.wooppay.php';
}

add_action('plugins_loaded', 'custom_payment_gateway_load', 0);

function wcCpg_action_links($links)
{
	$settings = array(
		'settings' => sprintf(
			'<a href="%s">%s</a>',
			admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_wooppay'),
			__('Payment Gateways', 'wooppay')
		)
	);

	return array_merge($settings, $links);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wcCpg_action_links');


?>