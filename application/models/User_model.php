<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class user_model extends CI_Model
{
    // Get all users
    function get_all_users()
    {
        $this->db->select('id, username, created');
        $this->db->from('user');
        $query = $this->db->get();
        return $query->result_array();
    }
    function get_this_user()
    {
        if ($this->session->userdata('user_session')) {
            $session_data = $this->session->userdata('user_session');
            $user = $this->user_model->get_user_by_id($session_data['id']);
            if (!isset($user['username'])) {
                redirect('user/logout', 'refresh');
                die();
                return false;
            }
            $this->user_loaded($user['id']);
            return $user;
        }
        return false;
    }
    function get_user_by_id($user_id)
    {
        $this->db->select('id, username, created');
        $this->db->from('user');
        $this->db->where('id', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    }
    function get_user_and_password($username)
    {
        $this->db->select('id, username, password');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result = $query->result_array();
            return isset($result[0]) ? $result[0] : false;
        }
        else {
            return false;
        }
    }
    function register($username, $password, $auth_token, $email, $ip, $register_ip_frequency_limit_minutes, $ab_test)
    {
        // Check for excessive IPs registers
        $this->db->select('id');
        $this->db->from('user');
        $this->db->where('ip', $ip);
        $this->db->where('created > NOW() - INTERVAL ' . $register_ip_frequency_limit_minutes . ' MINUTE');
        $this->db->limit(1);
        $query = $this->db->get();

        // Failed register ip frequency limit
        if (!is_dev() && $query->num_rows() > 0) {
            return 'ip_fail';
        }

        // Check for existing username
        $this->db->select('username');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->limit(1);
        $query = $this->db->get();

        // Username already exists
        if ($query->num_rows() > 0) {
            return false;
        }
        // Register
        else {
            // Insert user into user
            $data = array(
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'auth_token' => $auth_token,
            'email' => $email,
            'ip' => $ip,
            'ab_test' => $ab_test,
            );
            $this->db->insert('user', $data);

            // Return user id
            $this->db->select_max('id');
            $this->db->from('user');
            $this->db->limit(1);
            $query = $this->db->get()->row();
            $user_id = $query->id;
            return $user_id;
        }
    }
    function user_loaded($user_id)
    {
        $data = array(
            'last_load' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $user_id);
        $this->db->update('user', $data);
        return true;
    }

}
?>