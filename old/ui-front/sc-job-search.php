<?php if (!defined('ABSPATH')) die('No direct access allowed!');
/**
* @package Jobs +
* @author Arnold Bailey
* @since version 1.0
* @license GPL2+
*/

$phrase = (empty($_GET['s']) ) ? '' : $_GET['s'];


$sort_latest = (empty($_GET['prj_sort']) || $_GET['prj_sort'] == 'latest' ? 'active-sort' : 'inactive-sort');
$sort_ending = (!empty($_GET['prj_sort']) && $_GET['prj_sort'] == 'ending' ? 'active-sort' : 'inactive-sort');

$job_min_price = intval(empty($_GET['job_min_price']) ? 0 : $_GET['job_min_price'] );
$job_max_price = intval( empty($_GET['job_max_price']) ? $this->get_max_budget() : $_GET['job_max_price'] );

wp_enqueue_style('jobs-plus-custom');
?>

<section class="jobs-search-form <?php echo $class; ?> group">
	<form class="search-form group" method="GET" action="<?php echo get_post_type_archive_link('jbp_job'); ?>" >
		<div class="job-search-wrap group"  data-eq-pts=" break: 560" >
			<ul class="job-search group">
				<li>
					<span class="divider">Sort by</span>
				</li>
				<li>
					<span class="sort-by-latest <?php echo $sort_latest; ?> divider">
						<a href="<?php echo add_query_arg( array('prj_sort' => 'latest', ), get_post_type_archive_link('jbp_job') );?>" ><?php esc_html_e('Latest', $this->text_domain ); ?></a>
					</span>
				</li>
				<li class="right-border">
					<span class="sort-by-end <?php echo $sort_ending; ?>">
						<a href="<?php echo add_query_arg( array('prj_sort' => 'ending', ), get_post_type_archive_link('jbp_job') );?>" ><?php esc_html_e('About to End', $this->text_domain ); ?></a>
					</span>
				</li>
				<li>
					<input type="hidden" class="job_min_price" name="job_min_price" value="<?php echo $job_min_price; ?>" />
					<input type="hidden" class="job_max_price" name="job_max_price" value="<?php echo $job_max_price; ?>" />
					<input type="text" class="job-search-input" name="s" value="<?php echo esc_attr($phrase); ?>" autocomplete="off" placeholder="<?php echo $text; ?>" />

					<button type="submit" class="job-submit-search <?php echo $class; ?>" value="">
						<div class="div-img">&nbsp;</div>
					</button>
				</li>
			</ul>
		</div>
	</form>
</section>
