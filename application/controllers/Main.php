<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        // Uncomment models after database created
        // $this->load->model('main_model', '', TRUE);
        // $this->load->model('user_model', '', TRUE);

        // $user_flag = 0;
        // $user_key = 0;
        // $api_flag = 0;
        // $this->main_model->insert_request($user_flag, $user_key, $api_flag);
    }

    public function index()
    {
        // Authentication
        $log_check = $data['log_check'] = $data['user_id'] = false;
        if ($this->session->userdata('logged_in')) {
            $log_check = $data['log_check'] = true;
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
            $data['user'] = $this->user_model->get_user($user_id);
            if (! isset($data['user']['username']) ) {
                redirect('user/logout', 'refresh');
                return false;
            }
        }

        // Validation errors
        $data['validation_errors'] = $this->session->flashdata('validation_errors');
        $data['failed_form'] = $this->session->flashdata('failed_form');
        $data['just_registered'] = $this->session->flashdata('just_registered');

        // Load view
        $data['page_title'] = site_name();
        $this->load->view('templates/header', $data);
        $this->load->view('main', $data);
        $this->load->view('templates/footer', $data);
    }
}
