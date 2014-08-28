<?php

// +----------------------------------------------------------------------+
// | Copyright Incsub (http://incsub.com/)                                |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+

/**
 *
 * @category JobsExperts
 * @package  Shorcode
 *
 * @since    1.0.0
 */
class JobsExperts_Core_Shortcode_JobSingle extends JobsExperts_Shortcode
{
    const NAME = __CLASS__;

    public function __construct()
    {
        $this->_add_shortcode('jbp-job-single-page', 'shortcode');
    }

    private function load_assets()
    {
        wp_enqueue_style('jobs-single-shortcode');
    }

    public function shortcode($atts)
    {
        $this->load_assets();
        global $jbp_component_uploader;
        $jbp_component_uploader->load_scripts();
        //get plugin instance
        $plugin = JobsExperts_Plugin::instance();
        $page_module = $plugin->page_module();

        $model = JobsExperts_Core_Models_Job::instance()->get_one(get_the_ID());
        //add view count
        $model->add_view_count();
        ob_start();
        ?>
        <div class="hn-container">
            <?php echo do_shortcode('<p style="text-align: center">[jbp-expert-post-btn][jbp-job-post-btn][jbp-expert-browse-btn][jbp-job-browse-btn][jbp-expert-profile-btn][jbp-my-job-btn]</p>'); ?>

            <div class="jbp-job-single">
                <div class="row hn-border hn-border-round jobs-meta">
                    <div class="col-md-3">
                        <h5><?php esc_html_e('Job Budget', JBP_TEXT_DOMAIN); ?></h5>
                        <small class="text-warning"><?php $model->render_prices() ?></small>
                    </div>
                    <div class="col-md-3">
                        <h5><?php esc_html_e('This job open for', JBP_TEXT_DOMAIN) ?></h5>
                        <small class="text-warning"><?php echo $model->get_due_day() ?></small>
                    </div>
                    <div class="col-md-3">
                        <h5><?php _e('Must be complete by', JBP_TEXT_DOMAIN) ?></h5>
                        <?php if (strtotime($model->dead_line)): ?>
                            <small
                                class="text-warning"><?php echo date_i18n(get_option('date_format'), strtotime($model->dead_line)); ?></small>
                        <?php else: ?>
                            <small class="text-warning"><?php _e('N/A', JBP_TEXT_DOMAIN) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <?php if (strtolower($model->get_due_day()) != 'expired'): ?>
                            <a class="btn btn-info btn-sm" href="<?php echo add_query_arg(array(
                                'contact' => get_post()->post_name
                            ), get_permalink($page_module->page($page_module::JOB_CONTACT))) ?>"><?php _e('Contact', JBP_TEXT_DOMAIN) ?></a>
                            <?php else: ?>
                            <a disabled class="btn btn-info btn-sm" href="#"><?php _e('Contact', JBP_TEXT_DOMAIN) ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="row job-content">
                    <div class="col-md-12">
                        <?php echo wpautop($model->description) ?>
                    </div>
                    <div class="col-md-12">
                        <?php
                        $skills = $model->find_terms('jbp_skills_tag');
                        if (!empty($skills)): ?>
                            <div class="job_skills">
                                <?php
                                echo get_the_term_list(get_the_ID(), 'jbp_skills_tag', __('<h4>You will need to have these skills:', JBP_TEXT_DOMAIN) . '</h4><ul><li>', '</li><li>', '</li></ul>')
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        $files = array_unique(array_filter(explode(',', $model->portfolios)));
                        if (!empty($files)): ?>
                            <div class="row-fluid full">
                                <label><?php _e('Sample Files', JBP_TEXT_DOMAIN) ?></label>
                                <?php
                                global $jbp_component_uploader;
                                $jbp_component_uploader->show_on_front($model->id, $files);
                                ?>
                                <div class="clearfix"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?php if ($model->is_current_owner()): ?>
                            <br/>
                        <?php $post = get_post(get_the_ID());
                        $var = $post->post_status == 'publish' ? $post->post_name : $post->ID;
                        ?>
                            <a class="btn btn-primary"
                               href="<?php echo add_query_arg(array('job' => $var), get_permalink($page_module->page($page_module::JOB_EDIT))) ?>">
                                <?php _e('Edit', JBP_TEXT_DOMAIN) ?>
                            </a>
                            <form class="frm-delete" method="post" style="display: inline-block">
                                <input name="job_id" type="hidden" value="<?php echo $model->id ?>">
                                <?php wp_nonce_field('delete_job_' . $model->id) ?>
                                <button name="delete_job" class="btn btn-danger"
                                        type="submit"><?php _e('Trash', JBP_TEXT_DOMAIN) ?></button>
                            </form>
                            <script type="text/javascript">
                                jQuery(document).ready(function ($) {
                                    $('.frm-delete').submit(function () {
                                        if (confirm('<?php _e('Are you sure?',JBP_TEXT_DOMAIN) ?>')) {

                                        } else {
                                            return false;
                                        }
                                    })
                                })
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new JobsExperts_Core_Shortcode_JobSingle();