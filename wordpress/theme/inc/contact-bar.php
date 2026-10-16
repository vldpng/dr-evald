<?php
if(!defined('ABSPATH'))exit;
function dre_social_icon($network){
 $icons=[
 'instagram'=>'<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/>',
 'facebook'=>'<path d="M14 21v-8h3l.5-4H14V7c0-1.2.4-2 2-2h2V1.5c-.5-.1-1.8-.2-3-.2-3 0-5 1.8-5 5.2V9H7v4h3v8"/>',
 'telegram'=>'<path d="m3 11 18-7-4 17-6-5-4 3 1-6 10-7-11 8-4-3Z"/><path d="m11 16 7-10"/>',
 'whatsapp'=>'<path d="m4 20 1-4a9 9 0 1 1 3 3l-4 1Z"/><path d="M9 7H7.7c-.5 0-.7.5-.7 1 0 4.8 3.3 8 8 8 .5 0 1-.2 1-.7V14l-2.5-1-1 1a9 9 0 0 1-4-4l1-1L9 7Z"/>'
 ];return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$network]??'').'</svg>';
}
function dre_contact_bar(){
 $lang=dre_lang();$labels=['ru'=>['Социальные сети и мессенджеры','Ссылка будет добавлена'],'lv'=>['Sociālie tīkli un saziņa','Saite tiks pievienota'],'en'=>['Social media and messaging','Link coming soon']][$lang];
 $networks=['instagram'=>['Instagram',''],'facebook'=>['Facebook','https://www.facebook.com/Dr.Evald'],'telegram'=>['Telegram',''],'whatsapp'=>['WhatsApp','https://wa.me/37122004040']];
 echo '<div class="contact-bar"><div class="container contact-bar-inner"><div class="contact-bar-details"><span class="contact-bar-item contact-bar-hours"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg>'.esc_html(dre_t('hours')).'</span><a class="contact-bar-item contact-bar-address" href="'.esc_url(dre_url('contacts')).'">'.dre_icon('pin').'<span>Brīvības gatve 234–75, Rīga</span></a><a class="contact-bar-item contact-bar-phone" href="tel:+37122004040">'.dre_icon('phone').'<span>+371 22 004 040</span></a></div><nav class="contact-bar-social" aria-label="'.esc_attr($labels[0]).'">';
 foreach($networks as $key=>[$name,$default]){$url=get_theme_mod('dre_social_'.$key,$default);if($url)echo '<a class="social-link" href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" aria-label="'.esc_attr($name).'" title="'.esc_attr($name).'">'.dre_social_icon($key).'</a>';else echo '<span class="social-link is-placeholder" role="link" aria-disabled="true" aria-label="'.esc_attr($name.' — '.$labels[1]).'" title="'.esc_attr($name.' — '.$labels[1]).'">'.dre_social_icon($key).'</span>';}
 echo '</nav></div></div>';
}
add_action('customize_register',function($customizer){$customizer->add_section('dre_social_links',['title'=>'Dr. Evald — социальные сети','priority'=>30]);foreach(['instagram'=>['Instagram',''],'facebook'=>['Facebook','https://www.facebook.com/Dr.Evald'],'telegram'=>['Telegram',''],'whatsapp'=>['WhatsApp','https://wa.me/37122004040']] as $key=>[$label,$default]){$customizer->add_setting('dre_social_'.$key,['default'=>$default,'sanitize_callback'=>'esc_url_raw']);$customizer->add_control('dre_social_'.$key,['label'=>$label,'section'=>'dre_social_links','type'=>'url','description'=>'Полный адрес страницы. Пустое поле показывает иконку-заглушку.']);}});
