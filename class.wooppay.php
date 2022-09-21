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
 */

class WC_Gateway_Wooppay extends WC_Payment_Gateway
{
public $debug = 'yes';
	public function __construct()
	{
		$this->id = 'wooppay';
		$this->icon = apply_filters('woocommerce_wooppay_icon', plugins_url() . '/wooppay-1.1.5/assets/images/wooppay.png');
		$this->has_fields = false;
		$this->method_title = __('WOOPPAY', 'Wooppay');
		$this->init_form_fields();
		$this->init_settings();
		$this->title = $this->settings['title'];
		$this->description = $this->settings['description'];
		$this->instructions = $this->get_option('instructions');
		$this->enable_for_methods = $this->get_option('enable_for_methods', array());

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
		add_action('woocommerce_api_wc_gateway_wooppay', array($this, 'check_response'));
	}

	public function check_response()
	{
		if (isset($_REQUEST['id_order']) && isset($_REQUEST['key'])) {
			$order = wc_get_order((int)$_REQUEST['id_order']);
			if ($order && $order->key_is_valid($_REQUEST['key'])) {
				try {
					include_once('WooppaySoapClient.php');
					$client = new WooppaySoapClient($this->get_option('api_url'));
					if ($client->login($this->get_option('api_username'), $this->get_option('api_password'))) {
						$orderPrefix = $this->get_option('order_prefix');
						$serviceName = $this->get_option('service_name');
						$orderId = $order->get_id();
                        $orderId = $orderPrefix . '_' . $orderId;

						$invoice = $client->createInvoice($orderId, '', '', $order->order_total, $serviceName);
						$status = $client->getOperationData((int)$invoice->response->operationId)->response->records[0]->status;

						if ($status == WooppayOperationStatus::OPERATION_STATUS_DONE || $status == WooppayOperationStatus::OPERATION_STATUS_WAITING) {
							$order->update_status('completed', __('Payment completed.', 'woocommerce'));
							die('{"data":1}');
						}
					}
				} catch (Exception $e) {
					$this->add_log($e->getMessage());
					wc_add_notice(__('Wooppay error:', 'woocommerce') . $e->getMessage() . print_r($order, true), 'error');
				}
			} else
				$this->add_log('Error order key: ' . print_r($_REQUEST, true));
		} else
			$this->add_log('Error call back: ' . print_r($_REQUEST, true));
		die('{"data":1}');
	}

	/* Admin Panel Options.*/
	public function admin_options()
	{
		?>
		<h3><?php _e('Wooppay', 'wooppay'); ?></h3>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table> <?php
	}

	/* Initialise Gateway Settings Form Fields. */
	public function init_form_fields()
	{
		global $woocommerce;

		$shipping_methods = array();

		if (is_admin())
			foreach ($woocommerce->shipping->load_shipping_methods() as $method) {
				$shipping_methods[$method->id] = $method->get_title();
			}

		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'wooppay'),
				'type' => 'checkbox',
				'label' => __('Enable Wooppay Gateway', 'wooppay'),
				'default' => 'no'
			),
			'title' => array(
				'title' => __('Title', 'wooppay'),
				'type' => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'wooppay'),
				'desc_tip' => true,
				'default' => __('Wooppay Gateway', 'wooppay')
			),
			'description' => array(
				'title' => __('Description', 'wooppay'),
				'type' => 'textarea',
				'description' => __('This controls the description which the user sees during checkout.', 'wooppay'),
				'default' => __('Оплата с помощью кредитной карты или кошелька Wooppay', 'wooppay')
			),
			'instructions' => array(
				'title' => __('Instructions', 'wooppay'),
				'type' => 'textarea',
				'description' => __('Instructions that will be added to the thank you page.', 'wooppay'),
				'default' => __('Введите все необходимые данные и вас перенаправит на портал Wooppay для оплаты', 'wooppay')
			),
			'api_details' => array(
				'title' => __('API Credentials', 'wooppay'),
				'type' => 'title',
			),
			'api_url' => array(
				'title' => __('API URL', 'wooppay'),
				'type' => 'text',
				'description' => __('Get your API credentials from Wooppay.', 'wooppay'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => __('Optional', 'wooppay')
			),
			'api_username' => array(
				'title' => __('API Username', 'wooppay'),
				'type' => 'text',
				'description' => __('Get your API credentials from Wooppay.', 'wooppay'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => __('Optional', 'wooppay')
			),
			'api_password' => array(
				'title' => __('API Password', 'wooppay'),
				'type' => 'text',
				'description' => __('Get your API credentials from Wooppay.', 'wooppay'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => __('Optional', 'wooppay')
			),
			'order_prefix' => array(
				'title' => __('Order prefix', 'wooppay'),
				'type' => 'text',
				'description' => __('Order prefix', 'wooppay'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => __('Optional', 'wooppay')
			),
			'service_name' => array(
				'title' => __('Service name', 'wooppay'),
				'type' => 'text',
				'description' => __('Service name', 'wooppay'),
				'default' => '',
				'desc_tip' => true,
				'placeholder' => __('Optional', 'wooppay')
			),
		);

	}

	function process_payment($order_id)
	{
		include_once('WooppaySoapClient.php');
		global $woocommerce;
		$order = new WC_Order($order_id);
		try {
			$client = new WooppaySoapClient($this->get_option('api_url'));
			if ($client->login($this->get_option('api_username'), $this->get_option('api_password'))) {
				$requestUrl = WC()->api_request_url('WC_Gateway_Wooppay') . '?id_order=' . $order_id . '&key=' . $order->order_key;
				$backUrl = $requestUrl;
				$orderPrefix = $this->get_option('order_prefix');
				$serviceName = $this->get_option('service_name');
				$invoice = $client->createInvoice($orderPrefix . '_' . $order->get_id(), $backUrl, $requestUrl, $order->order_total, $serviceName, 'Оплата заказа №' . $order->get_id(), '', '', $order->billing_email, $order->billing_phone);
				$woocommerce->cart->empty_cart();
				$order->update_status('pending', __('Payment Pending.', 'woocommerce'));
				//$order->payment_complete($invoice->response->operationId);
				return array(
					'result' => 'success',
					'redirect' => $invoice->response->operationUrl
				);
			}
		} catch (Exception $e) {
			$this->add_log($e->getMessage());
			wc_add_notice(__('Wooppay error:', 'woocommerce') . $e->getCode(), 'error');
		}
	}

	function thankyou()
	{
		echo $this->instructions != '' ? wpautop($this->instructions) : '';
	}

	function add_log($message) {
		if ($this->debug == 'yes') {
			if (empty($this->log))
				$this->log = new WC_Logger();
			$this->log->add('Wooppay', $message);
		}
	}
}
