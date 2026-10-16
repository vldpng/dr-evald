<?php
if(!defined('ABSPATH'))exit;
$inline=($args['context']??'modal')==='inline';$prefix=$inline?'cta-booking':'booking';$service=$args['service']??'';$doctor=$args['doctor']??'';
$doctor_label=['ru'=>'Специалист: ','lv'=>'Speciālists: ','en'=>'Specialist: '][dre_lang()];
?>
<form class="booking-form<?php if($inline)echo ' booking-form-inline';?>" id="<?php echo esc_attr($prefix);?>-form" data-booking-form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php'));?>" aria-labelledby="<?php echo $inline?'cta-heading':'booking-title';?>">
 <input type="hidden" name="action" value="dre_booking">
 <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('dre_booking'));?>">
 <input type="hidden" name="language" value="<?php echo esc_attr(dre_lang());?>">
 <input type="hidden" name="doctor" id="<?php echo esc_attr($prefix);?>-doctor" value="<?php echo esc_attr($doctor);?>">
 <div id="<?php echo esc_attr($prefix);?>-doctor-note" class="booking-doctor-note" <?php if(!$doctor)echo 'hidden';?>><?php if($doctor)echo esc_html($doctor_label.$doctor);?></div>
 <div class="booking-fields">
  <div class="booking-field"><label for="<?php echo esc_attr($prefix);?>-name"><?php echo esc_html(dre_t('name'));?></label><input id="<?php echo esc_attr($prefix);?>-name" name="name" type="text" required minlength="2" maxlength="100" autocomplete="given-name"></div>
  <div class="booking-field"><label for="<?php echo esc_attr($prefix);?>-phone"><?php echo esc_html(dre_t('phone'));?></label><input id="<?php echo esc_attr($prefix);?>-phone" name="phone" type="tel" required maxlength="30" minlength="8" autocomplete="tel" placeholder="+371"></div>
  <div class="booking-field booking-field-service"><label for="<?php echo esc_attr($prefix);?>-service"><?php echo esc_html(dre_t('service'));?></label><select id="<?php echo esc_attr($prefix);?>-service" name="service"><option value=""><?php echo esc_html(dre_t('service_optional'));?></option><?php foreach(dre_services() as $s){$title=get_the_title($s);echo '<option value="'.esc_attr($title).'" '.selected($service,$title,false).'>'.esc_html($title).'</option>';}?></select></div>
 </div>
 <div class="honey" aria-hidden="true"><label for="<?php echo esc_attr($prefix);?>-website">Website</label><input id="<?php echo esc_attr($prefix);?>-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>
 <label class="consent"><input name="consent" type="checkbox" required value="1"><span><a href="<?php echo esc_url(dre_url('privacy'));?>" target="_blank" rel="noopener"><?php echo esc_html(dre_t('privacy_consent'));?></a></span></label>
 <button type="submit" class="button"><?php echo esc_html(dre_t('send'));echo dre_icon();?></button>
 <div class="form-result" role="status" aria-live="polite" aria-atomic="true"></div>
</form>
