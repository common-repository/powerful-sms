<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://felipepeixoto.tecnologia.ws/
 * @since      1.0.0
 *
 * @package    Powerful_Sms_Wp
 * @subpackage Powerful_Sms_Wp/admin/partials
 */
?>
<div class="wrap psms-wrap">
    <h2>Powerful SMS</h2>
    <h3>Integração com serviço de SMS</h3>
    <form action="#" method="post">
	    <div id="disparopro-postbox" class="postbox">
            <h3>Disparo Pro</h3>
	        <p>
	            <label for="full-chave">Token:
	            <input value="<?php echo esc_html($disparopro_token); ?>" id="psms-disparopro-token" name="psms-disparopro-token" type="text" size="50" required="required">  <br />
                <a style="text-decoration: none;" target="_blank" href="https://sistema.disparopro.com.br/customer/api">Não tem o token? Crie um no painel da Disparo Pro</a>
	            </label> 
	        </p>
	    </div>
	    <p>
	    	<input type="submit" name="salvar" value="Salvar Chaves" class="button-primary">
	    </p>
    </form>
</div>