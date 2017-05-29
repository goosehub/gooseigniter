<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    function __construct()
    {
        parent::__construct();

        // Uncomment models after database created
        $this->load->model('main_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);

        $this->main_model->record_result();
    }

    public function index()
    {
        // Authentication
        $data['user'] = $this->user_model->get_this_user();

        // A/B testing
        $ab_array = array('', '');
        $data['ab_test'] = $ab_array[array_rand($ab_array)];

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
