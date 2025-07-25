<?php
defined('ABSPATH') || exit;
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/Dev-Atmos/Finder-for-contact-form-7
 * @since      1.0.0
 *
 * @package    Finder_for_CF7
 * @subpackage Finder_for_CF7/admin/partials
 */






?>


<div>
    <h1>CF7 form Finder</h1>
    <ul class="nav nav-tabs" id="cf7ffTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="form-usage-tab" data-bs-toggle="tab" data-bs-target="#form-usage" type="button" role="tab" aria-controls="form-usage" aria-selected="true">
                Form Usage
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="hardcoded-tab" data-bs-toggle="tab" data-bs-target="#hardcoded" type="button" role="tab" aria-controls="hardcoded" aria-selected="false">
                Hardcoded Shortcodes
            </button>
        </li>
    </ul>

    <div class="tab-content" id="cf7ffTabContent" style="margin-top: 20px;">
        <div class="tab-pane fade show active" id="form-usage" role="tabpanel" aria-labelledby="form-usage-tab">

            <div class="wrap">



                <!-- <p>This plugin lists all published pages and posts using Contact Form 7 and shows which builder (Elementor/WPBakery) is used.</p> -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="cf7ff_export_csv">
                    <?php wp_nonce_field('cf7ff_export'); ?>
                    <input type="submit" class="button button-primary" value="Export to CSV">
                </form>
                <br>
                <form id="cf7ff-builder-filter-form" method="post">
                    <!-- <label for="cf7ff-builder-filter">Filter by Builder:</label>
        <select id="cf7ff-builder-filter" name="builder">
            <option value="">All Builders</option>
            <option value="Elementor">Elementor</option>
            <option value="WPBakery">WPBakery</option>
            <option value="Classic/Other">Classic/Other</option>
        </select> -->
                    <label for="cf7ff-form-id-filter">Filter by Form ID:</label>
                    <input type="number" id="cf7ff-form-id-filter" name="form_id" style="width: 100px;" />

                    <button type="submit" class="button button-secondary">Apply Filter</button>
                </form>
                <div id="cf7ff-loading" style="display:none; margin-bottom: 10px;">
                    <span class="spinner is-active" style="float: none;"></span> Loading...
                </div>


                <table class="table table-hover widefat fixed striped cf7ff-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="cf7ff-select-all"></th>
                            <th>Post Title</th>
                            <th>Post Type</th>
                            <th>Builder</th>
                            <th>Contact Form ID</th>
                            <th>Contact Form Title</th>
                            <th>View</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $data = CF7FF_Data::get_form_usage();

                        if (!empty($data)) :
                            foreach ($data as $row) :
                        ?>
                                <tr>
                                    <td><input type="checkbox" class="cf7ff-select-row" value="<?php echo esc_attr($row['form_id']); ?>"></td>

                                    <td><?php echo esc_html($row['title']); ?></td>
                                    <td><?php echo esc_html($row['type']); ?></td>
                                    <td><?php echo esc_html($row['builder']); ?></td>
                                    <td><?php echo esc_html($row['form_id']); ?></td>
                                    <td><?php echo esc_html($row['form_title']); ?></td>
                                    <td><a href="<?php echo esc_url($row['url']); ?>" target="_blank">View</a></td>
                                </tr>
                            <?php
                            endforeach;
                        else :
                            ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button id="cf7ff-view-report" class="button btn btn-primary">View Detailed Report</button>
                <!-- <button id="cf7ff-download-report" class="button">Download CSV</button> -->




            </div>
        </div>


        <div class="tab-pane fade" id="hardcoded" role="tabpanel" aria-labelledby="hardcoded-tab">
            <h2>Hardcoded Contact Form 7 Shortcodes in Theme Files</h2>
            <?php
            $hardcoded = CF7FF_Data::scan_theme_for_hardcoded_cf7();
            if (!empty($hardcoded)) :
            ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>File</th>
                            <th>Detected Shortcode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hardcoded as $item) : ?>
                            <tr>
                                <td><input type="checkbox" class="cf7ff-select-hardcoded-row" value="<?php echo esc_attr($item['cf7_info'][0]['id']); ?>"></td>
                                <td><?php echo esc_html($item['file']); ?></td>
                                <td>
                                    <ul class="mb-0 ps-3">

                                        <li>
                                            <div class="form-floating mb-4">
                                                <input type="text" class="form-control" id="form_id" placeholder="name@example.com" value="<?php echo esc_attr($item['cf7_info'][0]['id']) ?>" disabled>
                                                <label for="form_id" class="">Form ID</label>
                                            </div>
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="form_title" placeholder="name@example.com" value="<?php echo esc_attr($item['cf7_info'][0]['title']) ?>" disabled>
                                                <label for="form_title" class="">Form Title</label>
                                            </div>
                                        </li>

                                        <?php

                                        foreach ($item['matches'] as $match) : ?>
                                            <li><code><?php echo esc_html($match); ?></code></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="text-muted">No hardcoded shortcodes found in the current theme.</p>
            <?php endif; ?>
            <button id="cf7ff-view-hardcoded-report" class="button btn btn-primary">View Detailed Report</button>
        </div>
        <div id="cf7ff-modal" style="
                                    display: none;
                                    position: fixed;
                                    top: 10%;
                                    left: 50%;
                                    transform: translateX(-50%);
                                    width: 70%;
                                    max-height: 80%;
                                    background: #fff;
                                    padding: 20px;
                                    border: 1px solid #ccc;
                                    box-shadow: 0 0 10px #ccc;
                                    z-index: 1000;
                                    overflow-y: auto;
                                ">
            <h2>Form Usage Details</h2>
            <div id="cf7ff-modal-content"></div>
            <button id="cf7ff-modal-close" class="button">Close</button>
        </div>
        <div id="cf7ff-modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%;
                        height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999;"></div>
    </div>

</div>