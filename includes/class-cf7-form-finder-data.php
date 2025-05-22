<?php
class CF7_Form_Finder_Data
{
    public static function fix_cf7_shortcode_ids() {}

    public static function get_form_usage()
    {
        global $wpdb;

        $results = [];



        // Get all published pages and posts
        $posts = $wpdb->get_results("
            SELECT ID, post_title, post_type, post_content
            FROM {$wpdb->prefix}posts
            WHERE post_status = 'publish'
              AND post_type IN ('page', 'post')
              AND post_content LIKE '%[contact-form-7%'
        ");


        foreach ($posts as $post) {
            $builder = self::detect_builder($post->ID, $post->post_content);
            $cf7_info = self::extract_cf7_info($post->post_content);
            // print_r($post->post_content);
            foreach ($cf7_info as $cf7) {
                $results[] = [
                    'ID'         => $post->ID,
                    'title'      => $post->post_title,
                    'type'       => $post->post_type,
                    'builder'    => $builder,
                    'form_id'    => $cf7['id'],
                    'form_title' => $cf7['title'],
                    'url'        => get_permalink($post->ID),
                ];
            }
        }

        return $results;
    }

    private static function detect_builder($post_id, $content)
    {
        if (get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder') {
            return 'Elementor';
        } elseif (strpos($content, 'vc_row') !== false) {
            return 'WPBakery';
        } else {
            return 'Classic/Other';
        }
    }

    private static function extract_cf7_info($content)
    {
        $matches = [];
        // preg_match_all('/\[contact-form-7\s+id=["\']?(\d+)["\']?.*?\]/', $content, $matches);
        // preg_match_all('/\[contact-form-7\s+[^]]*id=["\']?(\d+)["\']?[^]]*\]/', $content, $matches);
        preg_match_all('/\[contact-form-7\s+[^]]*id=["\']?([a-zA-Z0-9_-]+)["\']?[^]]*\]/', $content, $matches);


        $forms = [];

        if (!empty($matches[1])) {
            // var_dump($matches[1]);
            $hash_map = self::get_cf7_hash_map();
            // var_dump($hash_map);

            foreach ($matches[1] as $form_id) {

                if (is_numeric($form_id)) {
                    $title = get_the_title($form_id);
                    $forms[] = [
                        'id' => $form_id,
                        'title' => $title ? $title : '(unknown)',
                    ];
                } else {
                    foreach ($hash_map as $hash => $form) {
                        // var_dump($hash);

                        if (str_contains($hash, $form_id)) {
                            $forms[] = [
                                'id'    => $form['id'],
                                'title' => $form['title'],
                            ];
                        }
                    }
                }
            }
        }

        return $forms;
    }

    private static function get_cf7_hash_map()
    {
        $forms = get_posts([
            'post_type'      => 'wpcf7_contact_form',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);

        $map = [];

        foreach ($forms as $form) {
            $cf7 = wpcf7_contact_form($form->ID);

            // Use reflection to access private 'hash' property
            $ref = new ReflectionClass($cf7);
            if ($ref->hasProperty('hash')) {
                $prop = $ref->getProperty('hash');
                $prop->setAccessible(true);
                $hash = $prop->getValue($cf7);

                $map[$hash] = [
                    'id'    => $form->ID,
                    'title' => $form->post_title,
                ];
            }
        }

        return $map;
    }
}
