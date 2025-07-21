<?php
defined('ABSPATH') || exit;
/**
 * Class CF7_Form_Finder_Data
 *
 * Provides utility methods for finding and analyzing Contact Form 7 shortcodes usage in WordPress posts and pages.
 *
 * Methods:
 * - fix_cf7_shortcode_ids(): Placeholder for future implementation to fix CF7 shortcode IDs.
 * - get_form_usage(): Scans published posts and pages for Contact Form 7 shortcodes, detects page builder usage, and returns an array of form usage details.
 * - detect_builder($post_id, $content): Detects which page builder (Elementor, WPBakery, or Classic/Other) is used in the given post.
 * - extract_cf7_info($content): Extracts Contact Form 7 shortcode information (ID and title) from post content, supporting both numeric IDs and hash-based IDs.
 * - get_cf7_hash_map(): Retrieves all published Contact Form 7 forms and builds a map of their hashes to IDs and titles using reflection.
 */

class CF7_Form_Finder_Data
{
    /**
     * Placeholder for future implementation to fix CF7 shortcode IDs in post content.
     *
     * @return void
     */
    public static function fix_cf7_shortcode_ids() {}

    /**
     * Scans all published posts and pages for Contact Form 7 shortcodes.
     * Detects which page builder is used and returns an array of form usage details.
     *
     * @return array Array of form usage details, each containing post ID, title, type, builder, form ID, form title, and URL.
     */
    public static function get_form_usage()
    {
        $results = [];

        // Query published pages and posts containing [cf-7-finder in content
        $args = [
            'post_type'      => ['post', 'page'],
            'post_status'    => 'publish',
            's'              => '[contact-form-7',
            'posts_per_page' => -1,
            'fields'         => 'ids',  // We only need IDs here
        ];

        $post_ids = get_posts($args);

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);

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
    /**
     * Scans the active theme for hardcoded Contact Form 7 shortcodes using do_shortcode().
     *
     * @return array List of files and shortcode instances found.
     */
    public static function scan_theme_for_hardcoded_cf7()
    {
        $shortcodes = [];
        // $cf7_info_array = [];
        $theme_dir = get_stylesheet_directory(); // Child theme or active theme path

        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($theme_dir));

        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;

            $content = file_get_contents($file->getPathname());

            if (preg_match_all('/do_shortcode\s*\(\s*[\'"]\[contact-form-7[^\]]*\][\'"]\s*\)/i', $content, $matches)) {
                // foreach ($matches[0] as $value) {
                    $cf7_info = self::extract_cf7_info($matches[0][0]);
                    $cf7_info_array = $cf7_info;
                // }
                $shortcodes[] = [
                    'file' => str_replace($theme_dir . '/', '', $file->getPathname()),
                    'matches' => $matches[0],
                    'cf7_info' => $cf7_info_array
                ];
            }
        }

        return $shortcodes;
    }


    /**
     * Detects which page builder is used in the given post.
     *
     * @param int    $post_id The post ID.
     * @param string $content The post content.
     * @return string The detected builder: 'Elementor', 'WPBakery', or 'Classic/Other'.
     */
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

    /**
     * Extracts Contact Form 7 shortcode information (ID and title) from post content.
     * Supports both numeric IDs and hash-based IDs.
     *
     * @param string $content The post content.
     * @return array Array of forms, each with 'id' and 'title'.
     */
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

    /**
     * Retrieves all published Contact Form 7 forms and builds a map of their hashes to IDs and titles.
     * Uses reflection to access the private 'hash' property of the form object.
     *
     * @return array Associative array mapping form hash => ['id' => form ID, 'title' => form title].
     */
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
