<?php
/** Run with WP-CLI on a fresh WordPress installation, after copying theme/plugin/media. */
if (!defined('WP_CLI') || !WP_CLI) exit("Use wp eval-file restore.php\n");
if (get_option('dre_content_routes')) WP_CLI::error('Import is for a fresh installation only. Existing Dr. Evald content was not changed.');
if (get_stylesheet() !== 'drevald-atelier' || !post_type_exists('dre_doctor')) WP_CLI::error('Activate drevald-atelier and drevald-core before importing.');
$data=json_decode(file_get_contents(__DIR__.'/public-content.json'),true,512,JSON_THROW_ON_ERROR);
if (($data['format']??0)!==1 || count($data['records']??[])!==96) WP_CLI::error('Unexpected content export.');
$uploads=wp_upload_dir();
foreach ($data['media'] as $media) {
    $file=realpath($uploads['basedir'].'/'.$media['file']);
    if (!$file || !str_starts_with(wp_normalize_path($file),trailingslashit(wp_normalize_path(realpath($uploads['basedir']))))) WP_CLI::error('Copy public/wp-content/uploads first. Missing media: '.$media['file']);
}
function dre_restore_value($value) {
    if (is_string($value)) return str_replace('https://example.invalid',untrailingslashit(home_url()),$value);
    if (is_array($value)) return array_map('dre_restore_value',$value);
    return $value;
}
$media_map=[]; $post_map=[];
foreach ($data['media'] as $media) {
    $id=wp_insert_attachment(['post_title'=>$media['title'],'post_mime_type'=>$media['mime'],'post_status'=>'inherit','guid'=>$uploads['baseurl'].'/'.$media['file']],$uploads['basedir'].'/'.$media['file'],0,true);
    if (is_wp_error($id)) WP_CLI::error($id->get_error_message());
    update_post_meta($id,'_wp_attached_file',$media['file']);
    if ($media['metadata']) wp_update_attachment_metadata($id,$media['metadata']);
    update_post_meta($id,'_wp_attachment_image_alt',$media['alt']);
    $media_map[$media['ID']]=$id;
}
foreach ($data['records'] as $record) {
    $post=dre_restore_value($record['post']); $old_id=$post['ID']; unset($post['ID']);
    $post['post_status']='publish'; $post['comment_status']='closed'; $post['ping_status']='closed';
    $id=wp_insert_post(wp_slash($post),true);
    if (is_wp_error($id)) WP_CLI::error($id->get_error_message());
    $post_map[$old_id]=$id;
    foreach ($record['meta'] as $key=>$values) {
        foreach ($values as $value) {
            if ($key==='_thumbnail_id') $value=$media_map[(int)$value]??0;
            add_post_meta($id,$key,wp_slash(dre_restore_value($value)));
        }
    }
}
foreach ($data['options'] as $key=>$value) {
    if (in_array($key,['page_on_front','wp_page_for_privacy_policy'],true)) $value=$post_map[(int)$value]??0;
    elseif ($key==='site_icon') $value=$media_map[(int)$value]??0;
    elseif ($key==='dre_content_routes') foreach ($value as &$route) $route['id']=$post_map[$route['id']];
    unset($route);
    update_option($key,dre_restore_value($value));
}
foreach ($data['theme_mods'] as $key=>$value) set_theme_mod($key,dre_restore_value($value));
update_option('show_on_front','page'); update_option('blog_public',0);
update_option('default_comment_status','closed'); update_option('default_ping_status','closed');
flush_rewrite_rules(false);
WP_CLI::success('Imported '.count($post_map).' public records and '.count($media_map).' media records. No users or bookings imported. Site remains noindex.');
