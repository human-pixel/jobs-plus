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
 * Front end module.
 * This module will hold all the page url information.
 *
 * @category JobsExperts
 * @package  Module
 *
 * @since    1.0.0
 */
class JobsExperts_Core_Backend extends JobsExperts_Framework_Module {

	const NAME = __CLASS__;

	public $jobs_menu_page;
	public $pros_menu_page;

	public $plugin;
	public $page_module;

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 *
	 * @param JobsExperts_Plugin $plugin The plugin instance.
	 */
	public function __construct( JobsExperts_Plugin $plugin ) {
		parent::__construct( $plugin );
		//init some shortcut class

		$this->_add_action( 'init', 'backend_init', 11 );
	}

	/**
	 * Do the inition for backend
	 */
	function backend_init() {
		$this->plugin      = JobsExperts_Plugin::instance();
		$this->page_module = $this->plugin->page_module();

		//create virtual pages
		$this->_add_action( 'init', 'register_virtual_pages', 20 );
		$this->_add_action( 'init', 'map_cap', 11 );

		$this->_add_action( 'admin_enqueue_scripts', 'scripts' );
		$this->_add_action( 'admin_init', 'process_settings' );
		$this->_add_action( 'admin_menu', 'admin_menu' );
		$this->_add_action( 'save_post', 'always_virtual_core' );

		$this->_add_filter( 'custom_menu_order', 'enable_custom_menu' );
		$this->_add_filter( 'menu_order', 'reorder_menu' );

		//setting pages
		$this->_add_action( 'jbp_setting_content', 'setting_tabs', 10, 2 );

		$this->_add_action( 'admin_notices', 'check_permalink_format' );
	}

	function hide_row_actions( $actions, $post ) {
		if ( current_user_can( 'manage_options' ) ) {
			return $actions;
		}
		$page_module = $this->page_module;
		if ( $post->post_type == 'jbp_job' ) {
			//remove quick edit
			unset( $actions['inline hide-if-no-js'] );
			if ( isset( $actions['edit'] ) ) {
				$edit_link = $actions['edit'];
				$dom       = new DOMDocument();

				$dom->loadHTML( $edit_link );
				$href = $dom->getElementsByTagName( 'a' )->item( 0 );
				$var  = $post->post_status == 'publish' ? $post->post_name : $post->ID;
				$href->setAttribute( 'href', add_query_arg( array( 'job' => $var ), get_permalink( $page_module->page( $page_module::JOB_EDIT ) ) ) );
				$actions['edit'] = $dom->saveHTML();
			}
		}

		return $actions;
	}

	function filter_add_new_link( $url, $path, $blog_id ) {
		$page_module = $this->page_module;
		parse_str( parse_url( $url, PHP_URL_QUERY ), $params );
		if ( is_array( $params ) && isset( $params['post_type'] ) && $params['post_type'] == 'jbp_job' ) {
			return get_permalink( $page_module->page( $page_module::JOB_ADD ) );
		}

		return $url;
	}

	function hide_views_link_wp_table( $views ) {
		if ( current_user_can( 'manage_options' ) ) {
			return $views;
		}
		if ( isset( $views['mine'] ) ) {
			return array( $views['mine'] );
		}
	}

	function check_permalink_format() {
		if ( get_option( 'permalink_structure' ) ) {
			return false;
		}
		echo '<div class="error"><p>' .
			sprintf(
				__( 'You must must update your permalink structure to something other than default to use Jobs and Experts. <a href="%s">You can do so here.</a>', JBP_TEXT_DOMAIN ),
				admin_url( 'options-permalink.php' )
			) .
			'</p></div>';
	}

	function map_cap() {
		if ( current_user_can( 'manage_options' ) ) {
			$caps = array(
				//"edit_{singular_base}"           => array( 'administrator', 'subscriber' ),
				"read_{singular_base}"           => array( 'administrator', 'subscriber' ),
				//"delete_{singular_base}"         => array( 'administrator', 'subscriber' ),
				"create_{plural_base}"           => array( 'administrator', 'subscriber' ),
				"edit_{plural_base}"             => array( 'administrator', 'subscriber' ),
				"edit_others_{plural_base}"      => array( 'administrator' ),
				"edit_private_{plural_base}"     => array( 'administrator', ),
				"edit_published_{plural_base}"   => array( 'administrator', 'subscriber' ),
				"publish_{plural_base}"          => array( 'administrator', 'subscriber' ),
				"read_private_{plural_base}"     => array( 'administrator' ),
				"delete_{plural_base}"           => array( 'administrator', 'subscriber' ),
				"delete_private_{plural_base}"   => array( 'administrator' ),
				"delete_published_{plural_base}" => array( 'administrator', 'subscriber' ),
				"delete_other_{plural_base}"     => array( 'administrator' )
			);

			$post_types = array(
				'jbp_job', 'jbp_pro'
			);

			foreach ( $post_types as $pt ) {
				$obj           = get_post_type_object( $pt );
				$singular_base = $obj->capability_type;
				$plural_base   = $singular_base . 's';

				foreach ( $caps as $key => $roles ) {
					foreach ( $roles as $role_name ) {
						$role = get_role( $role_name );

						$cap = str_replace( '{singular_base}', $singular_base, $key );
						$cap = str_replace( '{plural_base}', $plural_base, $cap );

						$role->add_cap( $cap );
					}
				}
			}
		}
	}


	function always_virtual_core( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		$page_module = $this->page_module;
		$post        = get_post( $post_id );
		if ( is_object( $post ) && $post->post_type == 'jbp_job' || $post->post_type == 'jbp_pro' ) {
			//check is core
			if ( $page_module->is_core_page( $post_id ) && $post->post_status != 'virtual' ) {
				wp_update_post( array(
					'ID'          => $post->ID,
					'post_status' => 'virtual'
				) );
			}
		}
	}

	function register_virtual_pages() {
		$page_module = $this->page_module;
		$page_module->init();
	}

	function process_settings() {
		$is_active   = get_option( 'jbp_active' );
		$page_module = $this->page_module;
		if ( $is_active != 1 ) {
			update_option( 'jbp_active', 1 );
			wp_redirect( admin_url( 'plugins.php' ) );
		}

		/*if ( isset( $_GET['page'] ) && $_GET['page'] == 'jobs-plus-add-job' ) {
			wp_redirect( get_permalink( $page_module->page( $page_module::JOB_ADD ) ) );
			exit;
		}*/

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && ! empty( $_POST['jobs-plus-settings'] ) ) {
			check_admin_referer( 'jobs-plus-settings' );
			$params = stripslashes_deep( $_POST );
			if ( isset( $params['JobsExperts_Core_Models_Settings'] ) ) {
				$model = new JobsExperts_Core_Models_Settings();
				$model->load();
				$model->import( $params['JobsExperts_Core_Models_Settings'] );
				$model->save();
			}
			do_action( 'jbp_after_save_settings' );
		}
	}

	function scripts() {
		wp_enqueue_style( 'jobs-plus-admin-css', $this->plugin->_module_url . 'assets/css/jobs-plus-admin.css' );
		wp_enqueue_style( 'jobs-plus-bootstrap', $this->plugin->_module_url . 'assets/bootstrap/css/bootstrap-with-namespace.css' );
	}

	function reorder_menu( $menu_order ) {
		global $submenu, $menu;
		//var_dump($menu);
		if ( is_network_admin() ) {
			return $menu_order;
		}

		$job_menu = empty( $submenu['edit.php?post_type=jbp_job'] ) ? false : $submenu['edit.php?post_type=jbp_job'];

		if ( $job_menu ) {
			//Rearrange the menu
			array_splice( $job_menu, 0, 2 ); //Remove First Two item (post_type and add post_type)
			$temp = array_splice( $job_menu, - 4 ); //Remove Last 4 items to $temp (our menu)
			//All that's left is the taxonomies in $job_menu
			array_splice( $temp, - 1, 0, $job_menu );

			$submenu['edit.php?post_type=jbp_job'] = $temp;
		}

		$pro_menu = empty( $submenu['edit.php?post_type=jbp_pro'] ) ? false : $submenu['edit.php?post_type=jbp_pro'];

		if ( $pro_menu ) {
			array_splice( $pro_menu, 0, 2 ); //Remove First Two item (post_type and add post_type)
			$temp = array_splice( $pro_menu, - 4 ); //Remove Last 4 items to $temp (our menu)
			//All that's left is the taxonomies in $job_menu
			array_splice( $temp, - 1, 0, $pro_menu );

			$submenu['edit.php?post_type=jbp_pro'] = $temp;
		}

		//var_dump($submenu);

		return $menu_order;
	}

	function admin_menu() {
		//Order is important. See reorder_menu!
		if ( is_network_admin() ) {
			return;
		}

		$job_labels = JobsExperts_Plugin::instance()->get_job_type()->labels;
		$pro_labels = JobsExperts_Plugin::instance()->get_expert_type()->labels;

		add_submenu_page( 'edit.php?post_type=jbp_job',
			__( 'Getting Started', JBP_TEXT_DOMAIN ),
			__( 'Getting Started', JBP_TEXT_DOMAIN ),
			'manage_options',
			'jobs-plus-about',
			array( $this, 'getting_start' )
		);

		add_submenu_page( 'edit.php?post_type=jbp_job',
			sprintf( __( 'Manage %s', JBP_TEXT_DOMAIN ), $job_labels->name ),
			sprintf( __( 'Manage %s', JBP_TEXT_DOMAIN ), $job_labels->name ),
			//'edit_' . JobsExperts_Plugin::instance()->get_job_type()->capability_type . 's',
			'manage_options',
			'edit.php?post_type=jbp_job' );

		add_submenu_page( 'edit.php?post_type=jbp_job',
			sprintf( __( 'New %s', JBP_TEXT_DOMAIN ), $job_labels->singular_name ),
			sprintf( __( 'New %s', JBP_TEXT_DOMAIN ), $job_labels->singular_name ),
			'manage_options',
			'jobs-plus-add-job',
			array( $this, 'admin_menu_add_job' )
		);

		$this->jobs_menu_page = add_submenu_page( 'edit.php?post_type=jbp_job',
			__( 'Settings', JBP_TEXT_DOMAIN ),
			__( 'Settings', JBP_TEXT_DOMAIN ),
			'manage_options',
			'jobs-plus-menu',
			array( $this, 'backend_setting' )
		);

		//Pros
		add_submenu_page( 'edit.php?post_type=jbp_pro',
			sprintf( __( 'Manage %s', JBP_TEXT_DOMAIN ), $pro_labels->name ),
			sprintf( __( 'Manage %s', JBP_TEXT_DOMAIN ), $pro_labels->name ),
			'manage_options',
			'edit.php?post_type=jbp_pro'
		);

		add_submenu_page( 'edit.php?post_type=jbp_pro',
			sprintf( __( 'New %s', JBP_TEXT_DOMAIN ), $pro_labels->singular_name ),
			sprintf( __( 'New %s', JBP_TEXT_DOMAIN ), $pro_labels->singular_name ),
			'manage_options',
			'jobs-plus-add-pro',
			array( $this, 'admin_menu_add_pro' )
		);

		$this->pros_menu_page = add_submenu_page( 'edit.php?post_type=jbp_pro',
			__( 'Settings', JBP_TEXT_DOMAIN ),
			__( 'Settings', JBP_TEXT_DOMAIN ),
			'manage_options',
			'jobs-plus-menu',
			array( &$this, 'backend_setting' ) );


		//we dont want to display the manager menu in backend for normal user
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( 'edit.php?post_type=jbp_job' );
			remove_menu_page( 'edit.php?post_type=jbp_pro' );
		}
		//remove metabox
		remove_meta_box('postcustom', 'jbp_job', 'normal');
	}

	function getting_start() {
		$template = new JobsExperts_Core_Views_GettingStart();
		$template->render();

	}

	function backend_setting() {
		$template = new JobsExperts_Core_Views_Settings();
		$template->render();
	}

	function enable_custom_menu() {
		return true;
	}

	function setting_tabs( $form, $model ) {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		switch ( $tab ) {
			case 'general':
				$template = new JobsExperts_Core_Views_Settings_General( array(
					'form'  => $form,
					'model' => $model
				) );
				$template->render();
				break;
			case 'job':
				$template = new JobsExperts_Core_Views_Settings_Job( array(
					'form'  => $form,
					'model' => $model
				) );
				$template->render();
				break;
			case 'expert':
				$template = new JobsExperts_Core_Views_Settings_Expert( array(
					'form'  => $form,
					'model' => $model
				) );
				$template->render();
				break;
		}
	}
}