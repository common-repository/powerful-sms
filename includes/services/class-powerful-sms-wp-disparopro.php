<?php

/**
 * Model estatica integrtar com Disparo Pro
 *
 * @link       http://felipepeixoto.tecnologia.ws/
 * @since      1.0.0
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/includes
 */

/**
 * Model estatica integrtar com Disparo Pro
 *
 * @since      1.0.0
 * @package    Full_Remote_Access
 * @subpackage Full_Remote_Access/includes
 * @author     Felipe Peixoto <peixoto152@gmail.com>
 */
class Powerful_Sms_Wp_Disparopro {

    private static $urlBase = 'https://apihttp.disparopro.com.br:8433/';
    private static $code = '55';

	/**
     * Testa token com o cadastrado.
     *
     * @since    1.0.0
     */
    public static function test_token($token = ''){
        $tokenCad = get_option('psms-disparopro-token', '');
        if ($tokenCad == $token and !empty($tokenCad)) {
            return true;
        }
        return false;
    }

    /**
	 * Pega o token cadastrado.
	 *
	 * @since    1.0.0
	 */
	public static function get_token(){
        $token = get_option('psms-disparopro-token', '');
        if (empty($token)) {
            return self::set_token();
        }
        return $token;
	}

    /**
     * Salva um token nova.
     *
     * @since    1.0.0
     */
    public static function set_token($token = ''){
    	if (empty($token)) {
    		return false;
    	}
        update_option('psms-disparopro-token',$token);
        return $token;
    }

    public static function clearMessage($message){
        if ( empty($message) ) { return FALSE; }
        return preg_replace('/[\{].*?[\}]/' , '', $message);
    }

    public static function sendSMS($phone_no = '', $message = ''){
        if (empty($phone_no) or empty($message)) { return FALSE; }
        $phone_no = self::phone_validation($phone_no);
        $message = self::clearMessage($message);
        if ($phone_no == FALSE ) { return FALSE; }
        $modo = 'mt';
        $parsArr = array('numero' => $phone_no,'servico' => 'short', 'mensagem' =>  $message);
        $send = self::sendCurl($modo, $parsArr);
        if (!empty($send[0]) or $send[1]['response']['code'] != 200 ) { 
            return FALSE;   
        }
        return TRUE;
    }


    public static function phone_validation($billing_phone){
        if (empty($billing_phone)) {
            return false;
        }
        $code_country = self::$code;
        $nom = trim($billing_phone);
        $nom = filter_var($nom, FILTER_SANITIZE_NUMBER_INT);
        $nom = str_replace("-","",$nom);
        $nom = str_replace("(","",$nom);
        $nom = str_replace(")","",$nom);
        $nom = str_replace(" ","",$nom);


        if (substr($nom, 0, strlen($code_country)) == $code_country) {
            $nom = '+'.$nom;
        }
        if (strpos($nom, '+'.$code_country) === FALSE) {
            $nom = '+'.$code_country.$nom;
        }
        
        return $nom;
    }

    private static function sendCurl($modo = 'mt', $pars = ''){
        if (!is_array($pars)) { return array('erro', 'Falta de parametros');  }
        $url = self::$urlBase.$modo;
        $args = array(
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer '.self::get_token(),
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode($pars)
        );
        $response = wp_remote_post($url,$args); 
        if ( !is_wp_error( $response ) && isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
            $error = '';
            $output = $response;
        } else {
            $error = '999';
            $output = 'Erro ao enviar SMS';
            if (is_wp_error( $response )) {
                $error = $response->get_error_code();
                $output = $response->get_error_message();
            }else{
                $error = isset( $response['response']['code'] ) ? $response['response']['code'] : '999';
            }
        }
        return array($error, $output);

    }

}
