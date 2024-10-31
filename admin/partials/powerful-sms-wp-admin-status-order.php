<?php

/**
 * Área para configurar os gatilhos de pedidos
 *
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
    <div class="postbox">
        <h3>Status dos Pedidos</h3>
        <p style="padding: 10px">Preencha os modelos de texto para cada status de pedido disponível, consulte a legenda de shortcode (variáveis) para personalizar.</p>
        <form action="" method="post">
            <div id="painel" class="col s12">
                <div id="accordionTriggers">
                    <?php   foreach ($triggers as $tKey => $tValue): ?>
                    <?php       $tValue['modelo'] = is_array($tValue['modelo']) ? $tValue['modelo'] : array($tValue['modelo']); ?>
                    <?php       $i = 0; ?>
                    <h3>
                        <?php echo esc_html($tValue['titulo']) ?>
                        <div class="right"><label>Ativo <input name="<?php echo esc_attr($tKey) ?>_ativo" type="checkbox" value="1" <?php echo $tValue['ativo']==1 ? 'checked' : '' ?>></label></div>
                    </h3>
                    <div class="row">
                        <?php echo $tValue['descricao']!='' ? '<p>'.esc_html($tValue['descricao']).'</p>' : ''; ?>
                        <?php foreach ($tValue['modelo'] as $vModelo): ?>
                        <div class="col s12 m6">
                            <?php if ($tKey == 'hold_order'): ?>
                                <label>
                                    <strong>Intervalo:</strong>
                                    <select name="<?php echo esc_attr($tKey) ?>_intervalo[]">
                                        <option <?php echo $tValue['intervalo'][$i]=='off' ? 'selected' : '' ?> value="off">Desligado</option>
                                        <option <?php echo $tValue['intervalo'][$i]=='1 hour' ? 'selected' : '' ?> value="1 hour">1 hora após a data do pedido</option>
                                        <option <?php echo $tValue['intervalo'][$i]=='1 days' ? 'selected' : '' ?> value="1 days">1 dia após a data do pedido</option>
                                        <option <?php echo $tValue['intervalo'][$i]=='2 days' ? 'selected' : '' ?> value="2 days">2 dias após a data do pedido</option>
                                        <option <?php echo $tValue['intervalo'][$i]=='3 days' ? 'selected' : '' ?> value="3 days">3 dias após a data do pedido</option>
                                        <option <?php echo $tValue['intervalo'][$i]=='4 days' ? 'selected' : '' ?> value="4 days">4 dias após a data do pedido</option>
                                    </select> <br />
                                </label>
                            <?php endif ?>
                            <label>
                                <strong>Modelo de Mensagem:</strong> <br>
                                <textarea name="<?php echo esc_attr($tKey) ?>_modelo[]" cols="30" rows="10"><?php echo esc_textarea($vModelo) ?></textarea>
                            </label>
                        </div>
                        <?php       $i++; ?>
                        <?php endforeach ?>
                    </div>
                    <?php endforeach ?>
                </div>
                <?php if (is_plugin_active('powers-triggers-of-woo-to-chat/wc-whatsapp-powers.php')) { ?>
                <p>
                    <input type="checkbox" name="pac-fail" value="1" <?php echo get_option('psms_pac_fail', 0)==1 ? 'checked="checked"' : ''; ?>> Marque essa opção para acionar o gatillho somente se Powerful Auto Chat falhar.
                </p>
                <?php } ?>
                
                <input type="submit" class="margin-top-bottom15 button button-primary" value="Salvar Gatilhos de Pedido" />
            </div>
        </form>
    </div>
    <h2>Legenda de Shortcode</h2>
    <div class="row postbox">
        <div class="legenda-block col s12">
            <div class="col s2">
                <p><strong>Básico</strong></p>
                <ul>
                    <li>{site_url}</li>
                </ul>
                <?php if (is_plugin_active('woocommerce-correios/woocommerce-correios.php')): ?>    
                <p><strong>Claudio Sanches - Correios for WooCommerce</strong></p>
                <ul>
                    <li>{correios_tracking_code}</li>
                </ul>
                <?php endif ?>
            </div>
            <div class="col s2">
                <p><strong>Nome</strong></p>
                <ul>
                    <li>{billing_first_name}</li>
                    <li>{billing_last_name}</li>
                </ul>
            </div>
            <div class="col s2">
                <p><strong>Endereço de cobrança</strong></p>
                <ul>
                    <li>{billing_address_1}</li>
                    <li>{billing_address_2}</li>
                    <li>{billing_city}</li>
                    <li>{billing_state}</li>
                    <li>{billing_postcode}</li>
                    <li>{billing_country}</li>
                    <li>{billing_email}</li>
                    <li>{billing_phone}</li>
                    <li>{billing_company}</li>
                </ul>
            </div>
            <div class="col s2">
                <p><strong>Entrega</strong></p>
                <ul>
                    <li>{shipping_first_name}</li>
                    <li>{shipping_last_name}</li>
                    <li>{universal_tracking_code}</li>
                    <li>{universal_tracking_url}</li>
                </ul>
            </div>
            <div class="col s2">
                <p><strong>Endereço de entrega</strong></p>
                <ul>
                    <li>{shipping_address_1}</li>
                    <li>{shipping_address_2}</li>
                    <li>{shipping_city}</li>
                    <li>{shipping_state}</li>
                    <li>{shipping_postcode}</li>
                    <li>{shipping_company}</li>
                    <li>{shipping_country}</li>
                    <li>{order_comments}</li>
                </ul>
            </div>
            <div class="col s2">
                <p><strong>Pedido</strong></p>
                <ul>
                    <li>{order_id}</li>
                    <li>{products_name}</li>
                    <li>{order_date_created}</li>
                    <li>{order_total}</li>
                    <li>{payment_url}</li>
                </ul>
            </div>
        </div>
    </div>
</div>