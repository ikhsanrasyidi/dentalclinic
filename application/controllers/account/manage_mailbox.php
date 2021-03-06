<?php
/*
 * Manage_roles Controller
 */
class Manage_mailbox extends CI_Controller {

  /**
   * Constructor
   */
  function __construct()
  {
    parent::__construct();

    // Load the necessary stuff...
    $this->load->config('account/account');
    $this->load->helper(array('date', 'language', 'account/ssl', 'url', 'photo', 'mailbox'));
    $this->load->library(array('account/authentication', 'account/authorization', 'form_validation'));
    $this->load->model(array('account/mailbox_model','account/account_model', 'account/account_details_model', 'account/acl_permission_model', 'account/acl_role_model', 'account/rel_account_permission_model', 'account/rel_account_role_model', 'account/rel_role_permission_model'));
    $this->load->language(array('general', 'account/manage_mailbox', 'account/manage_roles', 'account/account_settings', 'account/account_profile', 'account/sign_up', 'account/account_password'));
  }

  /**
   * Manage Roles
   */
  function index()
  {
    // Enable SSL?
    maintain_ssl($this->config->item("ssl_enabled"));

    // Redirect unauthenticated users to signin page
    if ( ! $this->authentication->is_signed_in())
    {
      redirect('account/sign_in/?continue='.urlencode(base_url().'account/manage_mailbox'));
    }

    // Redirect unauthorized users to account profile page
    if ( ! $this->authorization->is_permitted('manage_mailbox'))
    {
      redirect('account/account_profile');
    }
	 
    $data['adminpanel'] = true;
	$data['managemailbox'] = true;
	
	if($this->authorization->is_permitted('manage_mailbox')){
		$this->load->helper('mailbox');
		$data['mailinfo'] = mailInfo();
	}
			
	// Retrieve sign in user
	$data['account'] = $this->account_model->get_by_id($this->session->userdata('account_id'));
	$data['account_details'] = $this->account_details_model->get_by_account_id($this->session->userdata('account_id'));
	
	$data['mailbox'] = $this->mailbox_model->get();
		
    // Get all permossions, roles, and role_permissions
    $roles = $this->acl_role_model->get();
    $permissions = $this->acl_permission_model->get();
    $role_permissions = $this->rel_role_permission_model->get();
	
	$this->form_validation->set_rules(
      array(
        array(
          'field' => 'mailbox_name',
          'label' => 'lang:mailbox_name',
          'rules' => 'trim|required'),
		array(
          'field' => 'mailbox_email',
          'label' => 'lang:mailbox_email',
          'rules' => 'trim|required|valid_email'),
		array(
          'field' => 'mailbox_password',
          'label' => 'lang:mailbox_password',
          'rules' => 'trim|required'),
		array(
          'field' => 'mailbox_mail_server',
          'label' => 'lang:mailbox_mail_server',
          'rules' => 'trim|required'),
		 array(
          'field' => 'mailbox_mailbox',
          'label' => 'lang:mailbox_mailbox',
          'rules' => 'trim|required'),
 
      ));

    // Run form validation
    if ($this->form_validation->run())
    {
	  if(empty($data['mailbox']))
      { 
        $this->mailbox_model->create($this->input->post('mailbox_name', TRUE),$this->input->post('mailbox_email', TRUE),($this->input->post('mailbox_password', TRUE)),$this->input->post('mailbox_mail_server', TRUE),$this->input->post('mailbox_mailbox', TRUE));
      }else
	  {
		$this->mailbox_model->update($this->input->post('mailbox_name', TRUE),$this->input->post('mailbox_email', TRUE),($this->input->post('mailbox_password', TRUE)),$this->input->post('mailbox_mail_server', TRUE),$this->input->post('mailbox_mailbox', TRUE));
	  }
    }
    // Load manage roles view
    $this->load->view('account/manage_mailbox', $data);
  }
  
  /**
   * Test connection to the IMAP server.
   *
   * @return True / False
   *
   * @throws Exception when IMAP can't reconnect.
   */
  function testConnection() {
	$url = parse_url($_SERVER['REQUEST_URI']);
	
	if (isset($url['query'])){
		parse_str($url['query'], $this->get);
	}
	
	if($this->is_connected()){	
		if ($inbox  = imap_open("{".urldecode(explode(';',$this->get['query'])[0])."}".urldecode(explode(';',$this->get['query'])[1]), urldecode(explode(';',$this->get['query'])[2]), urldecode(explode(';',$this->get['query'])[3]))){
		  $data['status'] = "Connection Succeed";
		  $data['error'] = "";
		}else{
		  $data['status'] = "Connection Failure";
		  $data['error'] = imap_last_error();
		}
	}else{
		$data['status'] = "Connection Failure";
		$data['error'] = "No internet connection";
	}
	echo json_encode($data, JSON_PRETTY_PRINT);
  }
  
	function is_connected(){
		$connected = @fsockopen("www.google.com", 80); 
		//website, port  (try 80 or 443)
		if ($connected){
			$is_conn = true; //action when connected
			fclose($connected);
		}else{
			$is_conn = false; //action in connection failure
		}
		return $is_conn;
	}
  
}


/* End of file manage_mailbox.php */
/* Location: ./application/account/controllers/manage_mailbox.php */
