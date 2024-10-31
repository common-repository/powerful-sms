<?php
class Powerful_Sms_Wp_Woocommerce {
	public $triggers = array(
		'after_checkout' => array(
			'ativo' => '',
			'titulo' => 'Assim que o pedido for feito', 
			'descricao' => '',
			'action' => 'woocommerce_checkout_order_processed',
			'modelo' => '',
		),
		'processing_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for pago! (Processando)', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_processing',
			'modelo' => '',
		),
		'now_hold_order' => array(
			'ativo' => '',
			'titulo' => 'Na hora que o pedido mudar para status aguardando', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_on-hold',
			'modelo' => '',
		),
		'hold_order' => array(
			'ativo' => '',
			'titulo' => 'Lembretes de quando estiver em aguardando ou pendente', 
			'descricao' => 'Configure até 4 gatilhos para enviar mensagens quando o pedido estiver ainda em "aguardando" ou "pendente". Escolha o intervalo de cada mensagem baseado na data da criação do pedido.',
			'action' => 'reminder',
			'intervalo' => array('off','off','off','off'),
			'modelo' => array('','','',''),
		),
		'cancel_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for cancelado', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_cancelled',
			'modelo' => '',
		),
		'failed_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido falhou', 
			'descricao' => 'Durante a tentativa de pagamento, a operação pode falhar.',
			'action' => 'woocommerce_order_status_failed',
			'modelo' => '',
		),
		'completed_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for concluido!', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_completed',
			'modelo' => '',
		),
		'refunded_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for reembolsado', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_refunded',
			'modelo' => '',
		), 
	);

	function __construct() {
		$this->load_triggers();
		$args = array( true );
        if (! wp_next_scheduled ( 'psms_reminder_action', $args )) {
            wp_schedule_event( time(), 'hourly', 'psms_reminder_action', $args );
        }
		add_filter( 'psms_replace_modelo', array($this,'tracking_filter' ), 10, 2 );
		if(isset($_GET['psms_do_reminder'])){
			$this->do_reminder();
			exit();
		}
	}

	public function load_triggers(){
		$this->load_custom_status();
		$settings = get_option( 'psms_woo_triggers', '' );
		if (!empty($settings)) {
			$settings = json_decode($settings);
			foreach ($this->triggers as $tKey => $tValue) {
				$this->triggers[$tKey]['ativo'] = isset($settings->$tKey->ativo) ? $settings->$tKey->ativo : '';
				if (is_array($this->triggers[$tKey]['modelo']) ) {
					if (isset($settings->$tKey->modelo) and is_array($settings->$tKey->modelo) ) {
						$this->triggers[$tKey]['modelo'] = $settings->$tKey->modelo;
					}					
				} else {
					$this->triggers[$tKey]['modelo'] = isset($settings->$tKey->modelo) ? $settings->$tKey->modelo : $this->triggers[$tKey]['modelo'];		
				}

				if (isset($this->triggers[$tKey]['file'])) {
					$this->triggers[$tKey]['file'] = isset($settings->$tKey->file) ? $settings->$tKey->file : $this->triggers[$tKey]['file'];
				}
				
				if (isset($this->triggers[$tKey]['intervalo'])) {
					$this->triggers[$tKey]['intervalo'] = isset($settings->$tKey->intervalo) ? $settings->$tKey->intervalo : $this->triggers[$tKey]['intervalo'];
				}
			}	
		}
	}

	public function load_custom_status(){
		$custom_status = wc_get_order_statuses();
		$statusPadrao = array( 'wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed');
		foreach ($custom_status as $key => $status) {
			if (in_array($key, $statusPadrao)) { continue;	}
			$this->triggers['custom_status_'.$key] = array(
				'ativo' => '',
				'titulo' => 'Status customizado: '.$status,
				'descricao' => '',
				'action' => $key,
				'modelo' => '',
			);
		}
	}

	public function save_triggers ($dados){
		if (!empty($dados)) {
			$saveTriggers = array();
			foreach ($this->triggers as $tKey => $tValue) {
				if (isset($dados[$tKey.'_modelo']) and count($dados[$tKey.'_modelo'])==1) {
					$saveTriggers[$tKey]['modelo'] = $dados[$tKey.'_modelo'][0];
				} else{
					$saveTriggers[$tKey]['modelo'] = $dados[$tKey.'_modelo'];		
				}
				if (isset($dados[$tKey.'_intervalo'])){
					if (count($dados[$tKey.'_intervalo'])==1) {
						$saveTriggers[$tKey]['intervalo'] = $dados[$tKey.'_intervalo'][0];
					} else{
						$saveTriggers[$tKey]['intervalo'] = $dados[$tKey.'_intervalo'];		
					}
				}
				$saveTriggers[$tKey]['action'] = $this->triggers[$tKey]['action'];
				$saveTriggers[$tKey]['ativo'] = isset($dados[$tKey.'_ativo']) ? $dados[$tKey.'_ativo'] : '';
			}
			$saveTriggers = json_encode($saveTriggers);
			$up = update_option('psms_woo_triggers', $saveTriggers,FALSE);
			if (!$up) {
				$up = add_option('psms_woo_triggers', $saveTriggers);
			}

			
			$up = update_option('psms_pac_fail', isset($dados['pac-fail']),FALSE);
			return true;
		}
		return false;
	}

	public function option_field($checkout) {
		 echo '<div id="psms_check"><h2>' . __('Notificação por SMS') . '</h2>';
		 woocommerce_form_field( 'psms_notify', array(
	        'type'          => 'checkbox',
	        'class'         => array('input-checkbox'),
	        'label'         => __('Aceito ser notificado por SMS para este pedido'),
	        'required' => false,
	        ), 1);
		echo '</div>';

	}
	public function save_option_field( $order_id ) {
	    if ( ! empty( $_POST['psms_notify'] ) ) {
	        update_post_meta( $order_id, 'psms_notify', sanitize_text_field( $_POST['psms_notify'] ) );
	    }
	}

	public function do_action($order_id,$trigger) {

		$notify = get_post_meta($order_id, 'psms_notify', true);
		if (empty($notify)) {
			return false;
		}
		$modelo = $trigger->modelo;
		$rData = array();
		$order = wc_get_order($order_id);

		$target_phone = $order->get_billing_phone();
		$modelo  = $this->replace_modelo($order_id, $modelo);

		//SEND MESSAGE
		$res = Powerful_Sms_Wp_Disparopro::sendSMS($target_phone,$modelo);
	}

	public function do_reminder (){
		$settings = $this->triggers;
		$intervalos = $settings['hold_order']['intervalo'];
		if (!isset($settings['hold_order']) or $settings['hold_order']['ativo'] != 1) {
			return false;
		}

		$orders = wc_get_orders(array(
			'orderby' => 'date',
			'order' => 'DESC',
		    'post_status'=> array('on-hold','pending'),
		    'meta_key' => 'psms_notify',
		    'meta_compare' => '==',
		    'meta_value' =>  1,
			'limit' => 9999
	    ));
	    
	    foreach ($orders as $order) { 	
	    	$dataCriado = $order->order_date;
	    	$keySend = '';
	    	foreach ($intervalos as $key => $value) {
	    		if ($value!='off') {
	    			$dataProg = date('YmdHi', strtotime('+'.$value, strtotime($dataCriado)));
	    			$dataGap = date("YmdHi", strtotime('+2 hours', strtotime("now")));
	    			if ($dataProg<=$dataGap and $dataProg>=date("YmdHi")) {
	    				$keySend = $key;
	    			}
	    		}
	    	}

	    	if ($keySend!=='') {
		    	$modelo =  $settings['hold_order']['modelo'][$keySend];
		    	//REPLACE MODELO
				$modelo  = $this->replace_modelo($order->id, $modelo);
				//GET PHONE
				$target_phone = $order->get_billing_phone();
				if ($target_phone !== FALSE) {
					//SEND MESSAGE
					$res = Powerful_Sms_Wp_Disparopro::sendSMS($target_phone,$modelo);
				}
	    	}
		}
	}

	public function replace_modelo($order_id,$modelo) {

		//BASICS 
		$order = wc_get_order($order_id);
		$rData['order_id'] = $order_id;
		$rData['site_url'] = get_site_url();
		$rData['order_comments'] = $order->get_customer_note();
		$rData['order_date_created'] = $order->get_date_created();		
		
		//Metas
		$metas = get_post_meta($order_id);
		foreach ($metas as $key => $value) {
			if (substr($key, 0,1) == '_') {	$key = substr($key, 1); }
			if (is_array($value)) {	$value = $value[0];	}
			$rData[$key] = $value;
		}
		if (!empty($rData['order_total'])) {
			$rData['order_total'] = 'R$ '.number_format($rData['order_total'], 2, ',', ' ');
		}


		//itens
		$rData['products_name'] = '';
		foreach ($order->get_items() as $item_key => $item_values){
			$rData['products_name'] .= '- '.$item_values->get_name()."\n";
		}

	
		//payment
		$payment_data = $this->get_payment_data($order);
		foreach ($payment_data as $key => $value) {
    		$rData[$key] = $value;
		}
		



		//Filters
		$rData = apply_filters( 'psms_replace_modelo', $rData, $order );

		//Replace Model
		foreach ($rData as $key => $value) {
    		$modelo = str_replace('{'.$key.'}', $value, $modelo);
		}

		return $modelo;
	}

	public function tracking_filter($rData,$order){
		$rData['universal_tracking_code'] = '';
		$rData['universal_tracking_url'] = '';

		//Notificação de rastreio por transportadora
		if (psms_is_plugin_active('wc-any-shipping-notify/wc-any-shipping-notify.php')) {
			if (isset($rData['wc_any_shipping_notify_tracking_code'])) {
				$codigos = unserialize ( $rData['wc_any_shipping_notify_tracking_code'] );
				$companies = get_option('wc_any_shipping_notify_available_companies', '');
				$urls = array();
				foreach ($codigos as $key => $v) {
					if (isset( $companies[$v] )) {
						$url = str_replace('{tracking_code}', $key, $companies[$v]['url']);
						if (isset( $rData['billing_cpf'])) {
							$url = str_replace('{cpf}', $rData['billing_cpf'], $url);
						}
						$urls[] = $url;
					}
				}
				$rData['universal_tracking_url'] = implode(' - ',$urls );
				$rData['universal_tracking_code'] =  implode(' - ',array_keys( $codigos ) );

				
			}
		}

		//Claudio Sanches - Correios for WooCommerce
		if (psms_is_plugin_active('woocommerce-correios/woocommerce-correios.php')) {
			if (isset($rData['correios_tracking_code'])) {
				$rData['universal_tracking_code'] =  $rData['correios_tracking_code'];
				$rData['universal_tracking_url'] = 'https://linketrack.com/track?codigo='.$rData['correios_tracking_code'];
			}
		}

		//Advanced Shipment Tracking for WooCommerce
		if (psms_is_plugin_active('woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php')) {
			if (isset($rData['wc_shipment_tracking_items'])) {
				$data = @unserialize($rData['wc_shipment_tracking_items']);
				if ($data !== false and isset($data[0]['tracking_number'])) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'woo_shippment_provider';
					$field_name = 'provider_url';
					$rData['universal_tracking_code'] =  $data[0]['tracking_number'];
					$rData['universal_tracking_url'] = $wpdb->get_col ( $wpdb->prepare( "SELECT {$field_name} FROM {$table_name} WHERE  ts_slug = %s", $data[0]['tracking_provider'] ));
					$rData['universal_tracking_url'] = str_replace('%number%', $rData['universal_tracking_code'], $rData['universal_tracking_url']);
					$rData['universal_tracking_url'] = $rData['universal_tracking_url'][0]; 
				}
			}
		}
		
		return $rData;
	}

	public function get_payment_data( $order = false ) {
		if ( ! $order && ! $this->order ) {
			return false;
		} elseif ( ! $order && $this->order ) {
			$order = $this->order;
		}

	    $defaults = array(
	    	'method_name' => $order->get_payment_method_title(),
	    	'payment_url' => $order->needs_payment() ? $order->get_checkout_payment_url() : $order->get_view_order_url(),
	    	'payment_data' => '',
	    );

	    if ( 'pagseguro' === $order->get_payment_method() && 'Boleto' === $order->get_meta( 'Tipo de pagamento' ) ) {
	      $args = array(
	        'payment_url' => $order->get_meta( 'URL de pagamento.' ),
	      );
	    } elseif ( 'pix_gateway' === $order->get_payment_method() && class_exists( 'WC_Pix_Gateway' ) ) {
	      
	      $pix = new WC_Pix_Gateway;
	      $dados = $pix->generate_pix( $order->get_id() );
	      $args = array(
	        'payment_url' => $dados['instructions'] .' '. $dados['link']
	        
	      );
	    } elseif ( 'itau-shopline' === $order->get_payment_method() && class_exists( 'WC_Itau_Shopline' ) ) {
	      $args = array(
	        'payment_url' => WC_Itau_Shopline::get_payment_url( $order->get_order_key() ),
	        'expiry_time' => $order->get_meta( '_wc_itau_shopline_expiry_time' ),
	      );
	    } elseif ( 'woo-mercado-pago-ticket' === $order->get_payment_method() ) {
	      $args = array(
	        'payment_url' => $order->get_meta( '_transaction_details_ticket' )
	      );
	    } elseif ( 'woo-mercado-pago-pix' === $order->get_payment_method() ) {
	      $args = array(
	        'payment_url' => 'Código PIX para pagamento: ' . $order->get_meta( 'mp_pix_qr_code' )
	      );
	    } elseif ( 'bcash' === $order->get_payment_method() ) {
	      $args = array(
	        'payment_url' => add_query_arg( array( 'order_id' => $order->get_id() ), untrailingslashit( WC()->api_request_url( 'bcash_boleto_reminder' ) ) )
	      );
	    } elseif ( 'pagarme-banking-ticket' === $order->get_payment_method() ) {
	      $pagarme = get_post_meta( $order->get_id(), '_wc_pagarme_transaction_data', true );
	      $args = array(
	        'payment_url' => isset( $pagarme['boleto_url'] ) ? $pagarme['boleto_url'] : $defaults['payment_url']
	      );
	    } elseif ( 'wc_pagarme_pix_payment_geteway' === $order->get_payment_method() ) { //Pix Automático com Pagarme para WooCommerce
	      $pix = get_post_meta( $order->get_id(), '_wc_pagarme_pix_payment_qr_code', true );
	      $args = array(
	        'payment_url' => empty( $pix ) ? $defaults['payment_url'] : $pix
	      );
	    } elseif ( 'woo-moip-official' === $order->get_payment_method() && 'payBoleto' === $order->get_meta( '_moip_payment_type' ) ) {
	      $moip = get_post_meta( $order->get_id(), '_moip_payment_links', true );
	      $moip = maybe_unserialize( $moip );
	      $args = array(
	        'payment_url' => isset( $moip->payBoleto->printHref ) ? $moip->payBoleto->printHref : $defaults['payment_url']
	      );
	    } elseif ( in_array($order->get_payment_method(), ['paghiper', 'paghiper_billet', 'paghiper_pix'])) {
	      $paghiper = get_post_meta( $order->get_id(), 'wc_paghiper_data', true );
	      $paghiper = maybe_unserialize( $paghiper );
	      $url = '';
	      $url = isset( $paghiper['url_slip_pdf'] ) ? $paghiper['url_slip_pdf'] : $url;
	      $url = isset( $paghiper['pix_url'] ) ? $paghiper['pix_url'] : $url;
	      $args = array(
	        'payment_url' => $url!='' ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'mundipagg-banking-ticket' === $order->get_payment_method() ) {
	      $mundipagg = get_post_meta( $order->get_id(), '_mundipagg_banking_ticket_data', true );
	      $mundipagg = maybe_unserialize( $mundipagg );
	      $args = array(
	        'payment_url' => isset( $mundipagg['url'] ) ? $mundipagg['url'] : $defaults['payment_url']
	      );
	    } elseif ( 'jrossetto_woo_cielo_webservice_boleto' === $order->get_payment_method() ) {
	      $cielo = get_post_meta( $order->get_id(), '_transacao_boletoURL', true );
	      $args = array(
	        'payment_url' => $cielo ? $cielo : $defaults['payment_url']
	      );
	    } elseif ( 'boletofacil' === $order->get_payment_method() ) {
	      $url  = get_post_meta( $order->get_id(), 'boletofacil_url', true );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'loja5_woo_itau_shopline' === $order->get_payment_method() ) {
	      $url  = get_post_meta( $order->get_id(), 'loja5_woo_itau_shopline_link_boleto', true );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'loja5_woo_bradesco_api_boleto' === $order->get_payment_method() ) {
	      $data = get_post_meta( $order->get_id(), 'loja5_woo_bradesco_api_boleto_dados', true );
	      $args = array(
	        'payment_url' => isset( $data['link_boleto'] ) ? $data['link_boleto'] : $defaults['payment_url']
	      );
	    } elseif ( 'juno-pix' === $order->get_payment_method() ) {
	      $data = $order->get_meta( 'juno_qrcode_payload_base64' );
	      $args = array(
	        'payment_data' => empty($data) ? $defaults['payment_url'] : 'Pix Copia e Cola: '.base64_decode($data),
	      );
	    } elseif ( 'juno-bank-slip' === $order->get_payment_method() ) {
	      $data = $order->get_meta( '_juno_payment_response' );
	      $args = array(
	        'payment_url' => isset( $data->charges[0]->installmentLink ) ? $data->charges[0]->installmentLink : $defaults['payment_url'],
	        'payment_data' => $order->get_meta( 'juno_billet_barcode' )
	      );
	    } elseif ( 'widepay' === $order->get_payment_method() ) {
	      $url  = $order->get_meta( 'URLpagamento' );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'asaas-ticket' === $order->get_payment_method() ) {
	      $data = $order->get_meta( '__ASAAS_ORDER' );
	      $data = json_decode( $data );
	      $args = array(
	        'payment_url' => isset( $data->bankSlipUrl ) ? $data->bankSlipUrl : $defaults['payment_url']
	      );
	    } elseif ( 'loja5_woo_mercadopago_boleto' === $order->get_payment_method() ) {
	      $data = $order->get_meta( '_mercadopago_transacao' );
	      $args = array(
	        'payment_url' => isset( $data['transaction_details']['external_resource_url'] ) ? $data['transaction_details']['external_resource_url'] : $defaults['payment_url']
	      );
	    } elseif ( 'vindi-bank-slip' === $order->get_payment_method() ) {
	      $url = $order->get_meta( 'vindi_wc_invoice_download_url' );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } else {
	      // se o método não está integrado, retorna false
	      return $defaults;
	    }

	    $args = wp_parse_args( $args, $defaults );
	    return $args;
	  }

}