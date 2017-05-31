<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');

class User extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('main_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);

        $this->main_model->record_result();
    }

    // Login
    public function login()
    {
        // Check if this is ip has logged in too many times
        $ip = $_SERVER['REMOTE_ADDR'];
        $timestamp = date('Y-m-d H:i:s', time() - LOGIN_LIMIT_WINDOW_MINUTES * 60);
        $route_url = 'user/login';
        $check_request_results = $this->main_model->check_request_route($ip, $route_url, $timestamp);
        if (count($check_request_results) > LOGIN_LIMIT_COUNT && !is_dev()) {
            echo 'Too many login attempts from this IP. Please wait ' . LOGIN_LIMIT_WINDOW_MINUTES . ' minutes.';
            die();
        }

        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|max_length[32]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|max_length[64]|callback_login_validation');
        
        // On fail set fail message and redirect to map
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('failed_form', 'login');
            $this->session->set_flashdata('validation_errors', validation_errors());
            redirect(base_url(), 'refresh');
            return false;
        }

        // Success
        redirect(base_url(), 'refresh');
    }

    // Validate Login Callback
    public function login_validation($password)
    {
        // Get other parameters
        $username = $this->input->post('username');

        // Compare to database
        $user = $this->user_model->get_user_and_password($username);

        // Username not found
        if (!$user) {
            $this->form_validation->set_message('login_validation', 'Invalid username or password');
            return false;
        }

        // Password does not match
        else if (!PASSWORD_OVERRIDE && !password_verify($password, $user['password'])) {
            $this->form_validation->set_message('login_validation', 'Invalid username or password');
            return false;
        }

        // Success, do login
        $sess_array = array(
            'id' => $user['id'],
            'username' => $user['username']
        );
        $this->session->set_userdata('logged_in', $sess_array);
        return true;
    }

    // Register
    public function register()
    {
        // Honey pot
        if ($this->input->post('bee_movie')) {
            redirect(base_url(), 'refresh');
            return false;
        }
        
        // Optional password (For /r/WebGames)
        $matches = 'matches[confirm]|';
        if (PASSWORD_OPTIONAL) {
            if (!isset($_POST['password']) || $_POST['password'] === '') {
                $random_password = mt_rand(10000000,99999999); ;
                $_POST['password'] = $random_password;
                $_POST['confirm'] = $random_password;
                $matches = '';
            }
        }

        // Validation
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[64]|' . $matches . 'callback_register_validation');
        $this->form_validation->set_rules('confirm', 'Confirm', 'trim|required');
        $this->form_validation->set_rules('ab_test', 'ab_test', 'trim|max_length[32]');

        // Fail
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('failed_form', 'register');
            $this->session->set_flashdata('validation_errors', validation_errors());
            redirect(base_url(), 'refresh');
            return false;
        }

        // Success
        $this->session->set_flashdata('just_registered', true);
        redirect(base_url(), 'refresh');
    }

    // Validate Register Callback
    public function register_validation($password)
    {
        // Set parameters
        $email = '';
        $username = $this->input->post('username');
        $ab_test = $this->input->post('ab_test');
        $ip = $_SERVER['REMOTE_ADDR'];
        $auth_token = $token = bin2hex(openssl_random_pseudo_bytes(16));
        $user_id = $this->user_model->register($username, $password, $auth_token, $email, $ip, REGISTER_IP_FREQUENCY_LIMIT_MINUTES, $ab_test);

        // Registered too recently
        if ($user_id === 'ip_fail') {
            $this->form_validation->set_message('register_validation', 'This IP has already registered in the last ' . REGISTER_IP_FREQUENCY_LIMIT_MINUTES . ' minutes');
            return false;
        }

        // Username taken
        if (!$user_id) {
            $this->form_validation->set_message('register_validation', 'Username already exists');
            return false;
        }

        // Login
        $sess_array = array();
        $sess_array = array(
            'id' => $user_id,
            'username' => $username
        );
        $this->session->set_userdata('logged_in', $sess_array);
        return true;
    }

    // Logout
    public function logout()
    {
        $this->session->unset_userdata('logged_in');
        redirect(base_url() . '?logged_out=true', 'refresh');
    }
}