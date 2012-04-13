<?php
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class Settings extends Fuel_base_controller
{
	public $view_location = 'blog';
	public $nav_selected = 'blog/settings';

	function __construct()
	{
		parent::__construct();
		$this->config->module_load('blog', 'blog');
		$this->_validate_user('blog/settings');
	}

	function index()
	{
		$this->js_controller_params['method'] = 'add_edit';
		
		$blog_config = $this->config->item('blog');
		$fields = $blog_config['settings'];
		
		if ($this->fuel->settings->process('fuel_blog', $fields))
		{
			$this->fuel->blog->remove_cache();
			$this->session->set_flashdata('success', lang('data_saved'));
			redirect($this->uri->uri_string());
		}
		$field_values = $this->fuel->settings->get('fuel_blog');
		$fields['title']['after_html'] = '&nbsp;&nbsp;<strong>' . fuel_settings('fuel_blog', 'title') . '</strong>';
		
		$this->load->library('form_builder');
		
		//$this->form_builder->id = 'form';
		$this->form_builder->label_layout = 'left';
		$this->form_builder->form->validator = $this->fuel->settings->get_validation();
		//$this->form_builder->submit_value = null;
		$this->form_builder->use_form_tag = FALSE;
		$this->form_builder->set_fields($fields);
		$this->form_builder->display_errors = FALSE;
		$this->form_builder->name_array = 'settings';
		$this->form_builder->submit_value = 'Save';
		$this->form_builder->set_field_values($field_values);
		
		$vars = array();
		$vars['form'] = $this->form_builder->render();
		$vars['warn_using_config'] = !$this->config->item('blog_use_db_table_settings');
		
		$crumbs = array(lang('module_blog_settings'));
		$this->fuel->admin->set_titlebar($crumbs, 'ico_blog_settings');
		
		$this->fuel->admin->render('_admin/settings', $vars, Fuel_admin::DISPLAY_NO_ACTION);
	}
}