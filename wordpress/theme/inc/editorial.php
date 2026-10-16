<?php
if(!defined('ABSPATH'))exit;
function dre_e($key){$texts=[
'service_illustration'=>['Иллюстрация услуги','Pakalpojuma ilustrācija','Service illustration'],
'archive_illustration'=>['Фотография из архива клиники','Foto no klīnikas arhīva','A photograph from the clinic archive'],
'profile'=>['О специалисте','Par speciālistu','View profile'],
'choose_doctor'=>['Выбрать специалиста','Izvēlēties speciālistu','Choose a specialist'],
'photo_pending'=>['Фотография готовится','Foto tiek gatavots','Photograph coming soon'],
'visit_at'=>['Приём в клинике','Pieņemšana klīnikā','At the clinic'],
'education'=>['Образование','Izglītība','Education'],
'training'=>['Повышение квалификации','Profesionālā pilnveide','Further training'],
'approach'=>['Подход к работе','Pieeja darbam','Approach to care'],
'languages'=>['Языки общения','Saziņas valodas','Languages'],
'focus'=>['Направления работы','Darba jomas','Areas of practice'],
'experience'=>['Опыт работы','Darba pieredze','Professional experience'],
'about_doctor'=>['О специалисте','Par speciālistu','About the specialist'],
'cases'=>['Клинические случаи','Klīniskie gadījumi','Clinical cases'],
'before'=>['До лечения','Pirms ārstēšanas','Before treatment'],
'after'=>['После лечения','Pēc ārstēšanas','After treatment'],
'price_intro'=>['Выберите направление или найдите услугу по названию. Все цены указаны в евро.','Izvēlieties jomu vai meklējiet pakalpojumu pēc nosaukuma. Visas cenas norādītas eiro.','Choose a category or search for a treatment. All prices are in euros.'],
'price_notice'=>['Состав и окончательная стоимость лечения зависят от клинической ситуации. Перед началом врач обсудит с вами план и необходимые процедуры.','Ārstēšanas apjoms un gala izmaksas atkarīgas no klīniskās situācijas. Pirms sākuma ārsts pārrunās plānu un nepieciešamās procedūras.','Treatment and final costs depend on your clinical situation. Before treatment, your dentist will explain the plan and required procedures.'],
'items'=>['Позиций','Pozīcijas','Items'],
'price_service'=>['Услуга','Pakalpojums','Treatment'],
'price_amount'=>['Стоимость, €','Cena, €','Price, €'],
'expand'=>['Развернуть всё','Atvērt visu','Expand all'],
'collapse'=>['Свернуть всё','Aizvērt visu','Collapse all'],
'no_results'=>['Ничего не найдено. Попробуйте другое название или позвоните нам — поможем сориентироваться.','Nekas nav atrasts. Izmēģiniet citu nosaukumu vai zvaniet mums.','No results. Try another treatment name or call us for help.'],
'found'=>['Найдено','Atrasts','Found'],
'clear'=>['Очистить','Notīrīt','Clear'],
'no_details'=>['Время приёма и запись согласует администратор клиники.','Par pieņemšanas laiku vienosieties ar klīnikas administratoru.','Our administrator will help arrange an appointment.']
];$l=dre_lang();$i=['ru'=>0,'lv'=>1,'en'=>2][$l]??0;return $texts[$key][$i]??$key;}
function dre_doctor_portrait($doctor,$size='large'){
    if(has_post_thumbnail($doctor)){echo get_the_post_thumbnail($doctor,$size,['alt'=>get_the_title($doctor),'loading'=>'lazy']);return;}
    echo '<div class="portrait-placeholder"><svg viewBox="0 0 120 140" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><circle cx="60" cy="44" r="24"/><path d="M18 135v-18c0-26 18-44 42-44s42 18 42 44v18M46 76l14 20 14-20"/></svg><span>'.esc_html(dre_e('photo_pending')).'</span></div>';
}
function dre_render_doctor_card($doctor){
    $name=get_the_title($doctor);$role=dre_doctor_role($doctor->ID);$url=get_permalink($doctor);
    echo '<article class="doctor-card doctor-card-v2"><a class="doctor-card-image" href="'.esc_url($url).'" aria-label="'.esc_attr($name).'">';dre_doctor_portrait($doctor);echo '<span class="portrait-tag">Dr. Evald</span></a><div class="doctor-card-info"><a href="'.esc_url($url).'"><h3>'.esc_html($name).'</h3></a><p class="doctor-specialty">'.esc_html($role).'</p><div class="doctor-card-actions"><a class="doctor-profile-link" href="'.esc_url($url).'">'.esc_html(dre_e('profile')).dre_icon().'</a>';
    if(get_post_meta($doctor->ID,'_dre_group',true)!=='administration')echo '<button class="doctor-book-button" type="button" data-book data-doctor="'.esc_attr($name).'" aria-label="'.esc_attr(dre_t('book').' — '.$name).'">'.esc_html(dre_t('book')).'</button>';
    echo '</div></div></article>';
}
function dre_price_groups($content,$lang){
    $labels=['consultation'=>['Консультация и диагностика','Konsultācija un diagnostika','Consultation and diagnostics'],'therapy'=>['Лечение зубов','Zobu ārstēšana','Dental treatment'],'endodontics'=>['Лечение корневых каналов','Sakņu kanālu ārstēšana','Root canal treatment'],'hygiene'=>['Профессиональная гигиена','Profesionālā higiēna','Professional hygiene'],'surgery'=>['Хирургия','Ķirurģija','Dental surgery'],'implants'=>['Имплантация','Implantācija','Dental implants'],'children'=>['Детская стоматология','Bērnu zobārstniecība','Children’s dentistry'],'prosthetics'=>['Протезирование и каппы','Protezēšana un kapes','Prosthetics and guards']];
    $names=['therapy'=>['терапия','therapy','terapija'],'endodontics'=>['эндодонтия','endodontics','endodontija'],'hygiene'=>['гигиена','hygiene','higiēna'],'surgery'=>['хирургия','surgery','ķirurģija'],'implants'=>['имплантанты','implants','implanti'],'children'=>['детская стоматология','children`s dental treatment','children’s dental treatment','bērnu zobu ārstēšana'],'prosthetics'=>['ортопедическое лечение','ortopedical treatment','ortopēdiskā ārstēšana']];
    $dom=new DOMDocument();$old=libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8">'.$content);libxml_clear_errors();libxml_use_internal_errors($old);$xpath=new DOMXPath($dom);$groups=[];$current='consultation';$context='';$i=['ru'=>0,'lv'=>1,'en'=>2][$lang]??0;
    foreach($xpath->query('//table//tr') as $row){$cells=$xpath->query('./td|./th',$row);if(!$cells->length)continue;$values=[];foreach($cells as $cell)$values[]=trim(preg_replace('/\s+/u',' ',$cell->textContent));$title=$values[0];$price=count($values)>1?end($values):'';if(!$title)continue;$lower=mb_strtolower(trim($title,": \t\n\r\0\x0B"));$matched=null;foreach($names as $key=>$list)if(in_array($lower,$list,true)){$matched=$key;break;}
        if($matched){$current=$matched;$context='';continue;}
        $is_heading=$xpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6',$row)->length;
        if(!preg_match('/\d/',$price)){if($current==='consultation'&&($is_heading||stripos($price,'EUR')!==false))continue;$context=rtrim($title,':');continue;}
        if(!isset($groups[$current]))$groups[$current]=['key'=>$current,'title'=>$labels[$current][$i],'items'=>[]];
        $groups[$current]['items'][]=['name'=>$title,'price'=>$price,'context'=>$context];
    }
    return array_values($groups);
}
function dre_price_display($price){$text=preg_replace('/(\d+)\.00\b/','$1',$price);$text=str_replace(['-','–'], '–',$text);return $text.' €';}
function dre_render_cases($service_key=null,$doctor_id=null){
    $query=['post_type'=>'dre_case','post_status'=>'publish','posts_per_page'=>6,'meta_query'=>[['key'=>'_dre_case_approved','value'=>'1'],['key'=>'_dre_case_permission','value'=>'1'],['key'=>'_dre_case_lang','value'=>dre_lang()],['key'=>'_dre_case_before','value'=>0,'compare'=>'>','type'=>'NUMERIC'],['key'=>'_dre_case_after','value'=>0,'compare'=>'>','type'=>'NUMERIC']]];
    if($service_key)$query['meta_query'][]=['key'=>'_dre_case_service','value'=>$service_key];
    if($doctor_id)$query['meta_query'][]=['key'=>'_dre_case_doctor','value'=>(string)$doctor_id];
    $cases=get_posts($query);if(!$cases)return;
    echo '<section class="clinical-cases"><h2>'.esc_html(dre_e('cases')).'</h2>';
    foreach($cases as $case){$before=absint(get_post_meta($case->ID,'_dre_case_before',true));$after=absint(get_post_meta($case->ID,'_dre_case_after',true));if(!$before||!$after)continue;echo '<article><h3>'.esc_html($case->post_title).'</h3><div class="case-photos"><figure>'.wp_get_attachment_image($before,'large').'<figcaption>'.esc_html(dre_e('before')).'</figcaption></figure><figure>'.wp_get_attachment_image($after,'large').'<figcaption>'.esc_html(dre_e('after')).'</figcaption></figure></div><div class="prose">'.wp_kses_post(wpautop($case->post_content)).'</div></article>';}
    echo '</section>';
}
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('dre-editorial',get_template_directory_uri().'/assets/editorial.css',['drevald'],filemtime(get_template_directory().'/assets/editorial.css'));wp_enqueue_script('dre-editorial',get_template_directory_uri().'/assets/editorial.js',['drevald'],filemtime(get_template_directory().'/assets/editorial.js'),true);wp_localize_script('dre-editorial','dreEditorial',['found'=>dre_e('found'),'chosenDoctor'=>['ru'=>'Специалист: ','lv'=>'Speciālists: ','en'=>'Specialist: '][dre_lang()]]);},20);
