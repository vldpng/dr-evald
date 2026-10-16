<?php
/**
 * Plugin Name: Dr. Evald — Clinic Core
 * Description: Team profiles, private appointment requests, multilingual routes and migration SEO.
 * Version: 1.0.0
 */
if(!defined('ABSPATH'))exit;
function dre_add_content_rule($path,$id,$type){$query=$type==='page'?'page_id='.(int)$id:'p='.(int)$id.'&post_type='.sanitize_key($type);add_rewrite_rule('^'.preg_quote(trim($path,'/'),'#').'/?$','index.php?'.$query,'top');}
add_action('init',function(){
    register_post_type('dre_doctor',['labels'=>['name'=>'Команда клиники','singular_name'=>'Сотрудник','add_new_item'=>'Добавить сотрудника','edit_item'=>'Редактировать сотрудника'],'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-businessperson','supports'=>['title','editor','thumbnail','page-attributes','revisions'],'rewrite'=>['slug'=>'team','with_front'=>false],'has_archive'=>false]);
    register_post_type('dre_booking',['labels'=>['name'=>'Заявки на приём','singular_name'=>'Заявка'],'public'=>false,'publicly_queryable'=>false,'show_ui'=>true,'show_in_rest'=>false,'exclude_from_search'=>true,'menu_icon'=>'dashicons-calendar-alt','supports'=>['title','editor'],'capabilities'=>['edit_post'=>'manage_options','read_post'=>'manage_options','delete_post'=>'manage_options','edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options','read_private_posts'=>'manage_options','create_posts'=>'manage_options']]);
    $routes=get_option('dre_content_routes',[]);
    foreach($routes as $path=>$r){$regex='^'.preg_quote(trim($path,'/'),'#').'/?$';add_rewrite_rule($regex,'index.php?'.($r['type']==='page'?'page_id=':'p=').absint($r['id']).($r['type']==='page'?'':'&post_type='.sanitize_key($r['type'])),'top');}
});
add_filter('post_type_link',function($url,$post){$path=get_post_meta($post->ID,'_dre_path',true);return $path?home_url($path):$url;},10,2);
add_filter('post_link',function($url,$post){$path=get_post_meta($post->ID,'_dre_path',true);return $path?home_url($path):$url;},10,2);
add_filter('page_link',function($url,$id){$path=get_post_meta($id,'_dre_path',true);return $path?home_url($path):$url;},10,2);
add_action('template_redirect',function(){if(is_404()){$path=wp_parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);$aliases=get_option('dre_legacy_aliases',[]);if(isset($aliases[$path])){wp_safe_redirect(home_url($aliases[$path]),301);exit;}}});
add_action('wp_head',function(){
    if(!is_singular())return;
    $id=get_queried_object_id();$canonical=get_permalink($id);$title=get_post_meta($id,'_dre_seo_title',true)?:get_the_title($id).' — Dr. Evald';$description=get_post_meta($id,'_dre_description',true)?:wp_trim_words(wp_strip_all_tags(get_the_excerpt($id)),30,'');
    if($description)echo '<meta name="description" content="'.esc_attr($description).'">' . "\n";
    echo '<link rel="canonical" href="'.esc_url($canonical).'">' . "\n";
    $key=get_post_meta($id,'_dre_key',true);$routes=get_option('dre_routes',[]);
    foreach(['lv','ru','en'] as $l)if(isset($routes[$l][$key]))echo '<link rel="alternate" hreflang="'.$l.'" href="'.esc_url(home_url($routes[$l][$key])).'">' . "\n";
    if(isset($routes['lv'][$key]))echo '<link rel="alternate" hreflang="x-default" href="'.esc_url(home_url($routes['lv'][$key])).'">' . "\n";
    echo '<meta property="og:type" content="'.(get_post_type($id)==='post'?'article':'website').'"><meta property="og:title" content="'.esc_attr($title).'"><meta property="og:description" content="'.esc_attr($description).'"><meta property="og:url" content="'.esc_url($canonical).'"><meta property="og:site_name" content="Dr. Evald"><meta name="twitter:card" content="summary_large_image">' . "\n";
    $image=get_the_post_thumbnail_url($id,'large')?:get_theme_mod('dre_hero_image',get_template_directory_uri().'/assets/hero-doctor.webp');echo '<meta property="og:image" content="'.esc_url($image).'">' . "\n";
    $schema=['@context'=>'https://schema.org','@graph'=>[['@type'=>'Dentist','@id'=>home_url('/#clinic'),'name'=>'Dr. Evald','url'=>home_url('/'),'telephone'=>'+37122004040','email'=>'info@drevald.lv','address'=>['@type'=>'PostalAddress','streetAddress'=>'Brīvības gatve 234–75','addressLocality'=>'Rīga','postalCode'=>'LV-1039','addressCountry'=>'LV'],'openingHoursSpecification'=>[['@type'=>'OpeningHoursSpecification','dayOfWeek'=>['Monday','Tuesday','Wednesday','Thursday','Friday'],'opens'=>'09:00','closes'=>'20:00']],'foundingDate'=>'2009'],['@type'=>'WebPage','@id'=>$canonical.'#webpage','url'=>$canonical,'name'=>$title,'description'=>$description,'inLanguage'=>get_post_meta($id,'_dre_lang',true)?:'ru','isPartOf'=>['@id'=>home_url('/#website')]],['@type'=>'WebSite','@id'=>home_url('/#website'),'url'=>home_url('/'),'name'=>'Dr. Evald','publisher'=>['@id'=>home_url('/#clinic')]]]];
    echo '<script type="application/ld+json">'.wp_json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP).'</script>' . "\n";
},3);
remove_action('wp_head','rel_canonical');
add_filter('wp_robots',function($robots){if(wp_get_environment_type()==='local'){$robots['noindex']=true;unset($robots['index']);}return $robots;});
add_action('add_meta_boxes',function(){
    add_meta_box('dre-doctor-details','Данные сотрудника',function($post){wp_nonce_field('dre_doctor_meta','dre_doctor_nonce');$roles=get_post_meta($post->ID,'_dre_roles',true)?:[];$names=get_post_meta($post->ID,'_dre_names',true)?:[];foreach(['ru'=>'Русский','lv'=>'Latviešu','en'=>'English'] as $lang=>$label){echo '<p><strong>'.esc_html($label).'</strong><br><label>Имя <input style="width:100%" name="dre_names['.$lang.']" value="'.esc_attr($names[$lang]??'').'"></label><br><label>Специализация <input style="width:100%" name="dre_roles['.$lang.']" value="'.esc_attr($roles[$lang]??'').'"></label></p>';}echo '<label>Группа <select name="dre_group">';foreach(['dentist'=>'Стоматолог','hygienist'=>'Гигиенист','administration'=>'Администрация'] as $value=>$label)echo '<option value="'.$value.'" '.selected(get_post_meta($post->ID,'_dre_group',true),$value,false).'>'.$label.'</option>';echo '</select></label>';},'dre_doctor');
    add_meta_box('dre-seo','SEO и языковая версия',function($post){wp_nonce_field('dre_page_meta','dre_page_nonce');foreach(['_dre_seo_title'=>'SEO title','_dre_description'=>'Meta description','_dre_lang'=>'Язык (ru / lv / en)','_dre_key'=>'Группа переводов','_dre_path'=>'Адрес страницы (путь)'] as $key=>$label)echo '<p><label>'.esc_html($label).'<input style="width:100%" name="'.esc_attr($key).'" value="'.esc_attr(get_post_meta($post->ID,$key,true)).'"></label></p>';echo '<p>Исходник: '.esc_html(get_post_meta($post->ID,'_dre_source',true)).'</p>';},['page','post','dre_doctor'],'normal','low');
});
add_action('save_post',function($id){if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE||!current_user_can('edit_post',$id))return;
    if(isset($_POST['dre_doctor_nonce'])&&wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dre_doctor_nonce'])),'dre_doctor_meta')){foreach(['roles','names'] as $field){$values=[];foreach(['ru','lv','en'] as $lang)$values[$lang]=sanitize_text_field(wp_unslash($_POST['dre_'.$field][$lang]??''));update_post_meta($id,'_dre_'.$field,$values);}update_post_meta($id,'_dre_group',sanitize_key($_POST['dre_group']??'dentist'));}
    if(isset($_POST['dre_page_nonce'])&&wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dre_page_nonce'])),'dre_page_meta')){foreach(['_dre_seo_title','_dre_description','_dre_lang','_dre_key','_dre_path'] as $key)if(isset($_POST[$key]))update_post_meta($id,$key,sanitize_text_field(wp_unslash($_POST[$key])));$path=get_post_meta($id,'_dre_path',true);$routes=get_option('dre_content_routes',[]);foreach($routes as $old=>$r)if((int)$r['id']===$id)unset($routes[$old]);if($path)$routes[$path]=['id'=>$id,'type'=>get_post_type($id)];update_option('dre_content_routes',$routes);$lang=get_post_meta($id,'_dre_lang',true);$key=get_post_meta($id,'_dre_key',true);$map=get_option('dre_routes',[]);if($lang&&$key&&$path){$map[$lang][$key]=$path;update_option('dre_routes',$map);}flush_rewrite_rules(false);}
});
add_action('wp_ajax_dre_booking','dre_booking_handler');add_action('wp_ajax_nopriv_dre_booking','dre_booking_handler');
function dre_booking_handler(){
    $lang=sanitize_key($_POST['language']??'ru');$lang=in_array($lang,['ru','lv','en'],true)?$lang:'ru';
    $msg=['ru'=>['invalid'=>'Проверьте имя, телефон и подтверждение ознакомления с политикой конфиденциальности.','rate'=>'Заявка уже отправлена. Пожалуйста, подождите несколько минут.','success'=>'Спасибо! Заявка получена. Администратор свяжется с вами, чтобы подтвердить время визита.','error'=>'Не удалось сохранить заявку. Позвоните +371 22 004 040.'],'lv'=>['invalid'=>'Pārbaudiet vārdu, tālruni un privātuma politikas apstiprinājumu.','rate'=>'Pieteikums jau nosūtīts. Lūdzu, uzgaidiet dažas minūtes.','success'=>'Paldies! Pieteikums saņemts. Administrators sazināsies, lai apstiprinātu vizītes laiku.','error'=>'Neizdevās saglabāt pieteikumu. Zvaniet +371 22 004 040.'],'en'=>['invalid'=>'Please check your name, phone and privacy acknowledgement.','rate'=>'A request was recently sent. Please wait a few minutes.','success'=>'Thank you! Your request has been received. Our administrator will contact you to confirm a time.','error'=>'Unable to save your request. Please call +371 22 004 040.']][$lang];
    if(!check_ajax_referer('dre_booking','nonce',false))wp_send_json_error(['message'=>$msg['invalid']],403);
    if(!empty($_POST['website']))wp_send_json_error(['message'=>$msg['invalid']],400);
    $name=sanitize_text_field(wp_unslash($_POST['name']??''));$phone=sanitize_text_field(wp_unslash($_POST['phone']??''));$service=sanitize_text_field(wp_unslash($_POST['service']??''));$doctor=sanitize_text_field(wp_unslash($_POST['doctor']??''));$digits=preg_replace('/\D/','',$phone);
    if(mb_strlen($name)<2||mb_strlen($name)>100||mb_strlen($service)>150||mb_strlen($doctor)>150||strlen($phone)>30||strlen($digits)<8||strlen($digits)>15||!preg_match('/^[+0-9\s().-]+$/',$phone)||($_POST['consent']??'')!=='1')wp_send_json_error(['message'=>$msg['invalid']],400);
    $key='dre_rate_'.substr(hash_hmac('sha256',$_SERVER['REMOTE_ADDR']??'local',wp_salt('nonce')),0,32);if(get_transient($key))wp_send_json_error(['message'=>$msg['rate']],429);
    $id=wp_insert_post(['post_type'=>'dre_booking','post_status'=>'private','post_title'=>$name.' — '.current_time('d.m.Y H:i'),'post_content'=>"Имя: $name\nТелефон: $phone\nУслуга: $service\nСпециалист: $doctor\nЯзык: $lang\nСтатус: новая заявка. Время визита не подтверждено.\nОзнакомление с политикой конфиденциальности: да"],true);
    if(is_wp_error($id)||!$id)wp_send_json_error(['message'=>$msg['error']],500);
    update_post_meta($id,'_dre_doctor',$doctor);update_post_meta($id,'_dre_phone',$phone);update_post_meta($id,'_dre_service',$service);update_post_meta($id,'_dre_lang',$lang);update_post_meta($id,'_dre_consent_version','2026-10-local-draft');set_transient($key,1,120);
    wp_send_json_success(['message'=>$msg['success']]);
}
add_action('customize_register',function($customizer){$customizer->add_section('dre_home',['title'=>'Dr. Evald — первый экран','priority'=>25]);$customizer->add_setting('dre_hero_image',['default'=>get_template_directory_uri().'/assets/hero-doctor.webp','sanitize_callback'=>'esc_url_raw']);$customizer->add_control(new WP_Customize_Image_Control($customizer,'dre_hero_image',['label'=>'Портрет для первого экрана','section'=>'dre_home','description'=>'Замените иллюстрацию настоящей фотографией врача.']));foreach(['ru'=>'Русский','lv'=>'Latviešu','en'=>'English'] as $lang=>$label){foreach(['hero_line1'=>'Заголовок, строка 1','hero_line2'=>'Заголовок, строка 2','hero_copy'=>'Краткий текст','hero_label'=>'Подпись к изображению'] as $field=>$caption){$id='dre_'.$field.'_'.$lang;$customizer->add_setting($id,['sanitize_callback'=>'sanitize_text_field']);$customizer->add_control($id,['label'=>$label.' — '.$caption,'section'=>'dre_home','type'=>'text']);}}});
add_action('admin_notices',function(){if(!current_user_can('manage_options'))return;if(get_current_screen()->post_type==='dre_booking')echo '<div class="notice notice-info"><p>Заявки сохраняются в WordPress. Уведомления по email не подключены. Перед публичным запуском согласуйте политику обработки данных и срок хранения заявок.</p></div>';});
add_filter('the_title',function($title,$id){if(is_admin()||get_post_type($id)!=='dre_doctor'||!function_exists('dre_lang'))return $title;$names=get_post_meta($id,'_dre_names',true);return is_array($names)?($names[dre_lang()]??$title):$title;},10,2);
add_action('save_post',function($id,$post){
    if(!in_array($post->post_type,['post','dre_doctor'],true)||wp_is_post_revision($id)||wp_is_post_autosave($id)||$post->post_status==='auto-draft')return;
    $lang=get_post_meta($id,'_dre_lang',true)?:'ru';$kind=$post->post_type==='post'?'blog':'doctor';
    if(!get_post_meta($id,'_dre_lang',true))update_post_meta($id,'_dre_lang',$lang);
    if(!get_post_meta($id,'_dre_kind',true))update_post_meta($id,'_dre_kind',$kind);
    $key=get_post_meta($id,'_dre_key',true)?:$kind.'_'.$id;update_post_meta($id,'_dre_key',$key);
    $path=get_post_meta($id,'_dre_path',true);if(!$path){$slug=$post->post_name?:sanitize_title($post->post_title);$section=$kind==='blog'?'blog':(['ru'=>'vrachi','lv'=>'specialisti','en'=>'team'][$lang]??'team');$path='/'.$lang.'/'.$section.'/'.$slug.'/';update_post_meta($id,'_dre_path',$path);}
    if($kind==='doctor'&&!get_post_meta($id,'_dre_group',true))update_post_meta($id,'_dre_group','dentist');
    $content_routes=get_option('dre_content_routes',[]);$record=['id'=>$id,'type'=>$post->post_type];if(($content_routes[$path]??null)!==$record){$content_routes[$path]=$record;update_option('dre_content_routes',$content_routes);dre_add_content_rule($path,$id,$post->post_type);flush_rewrite_rules(false);}
    $routes=get_option('dre_routes',[]);$routes[$lang][$key]=$path;update_option('dre_routes',$routes);
},30,2);
add_action('save_post',function($id){
    if(!current_user_can('edit_post',$id)||!isset($_POST['dre_page_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['dre_page_nonce'])),'dre_page_meta'))return;
    $path=get_post_meta($id,'_dre_path',true);if($path){dre_add_content_rule($path,$id,get_post_type($id));flush_rewrite_rules(false);}
},35);

require_once __DIR__.'/editorial-admin.php';
