<?php
class CF7_Form_Finder_Data {

    public static function get_form_usage() {
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

    private static function detect_builder($post_id, $content) {
        if (get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder') {
            return 'Elementor';
        } elseif (strpos($content, 'vc_row') !== false) {
            return 'WPBakery';
        } else {
            return 'Classic/Other';
        }
    }

    private static function extract_cf7_info($content) {
        $matches = [];
        preg_match_all('/\[contact-form-7\s+id=["\']?(\d+)["\']?.*?\]/', $content, $matches);

        $forms = [];
        if (!empty($matches[1])) {
            foreach ($matches[1] as $form_id) {
                $title = get_the_title($form_id);
                $forms[] = [
                    'id' => $form_id,
                    'title' => $title ? $title : '(unknown)',
                ];
            }
        }

        return $forms;
    }
}
