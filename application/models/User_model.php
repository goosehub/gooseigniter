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
 // Get user
 function get_user($user_id)
 {
    $this->db->select('id, username, created');
    $this->db->from('user');
    $this->db->where('id', $user_id);
    $this->db->limit(1);
    $query = $this->db->get();
    $result = $query->result_array();
    return isset($result[0]) ? $result[0] : false;
 }
 // Login
 function login($username, $password)
 {
    $this->db->select('*');
    $this->db->from('user');
    $this->db->where('username', $username);
    $this->db->limit(1);
    $query = $this->db->get();
    if ($query->num_rows() == 1) {
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    } else {
        return false;
    }
 }
 // Register
 function register($username, $password, $auth_token, $email, $ip, $ip_frequency_register, $ab_test)
 {
    // Check for excessive IPs registers
    $this->db->select('username');
    $this->db->from('user');
    $this->db->where('ip', $ip);
    $this->db->where('created > NOW() - INTERVAL ' . $ip_frequency_register . ' MINUTE');
    $this->db->limit(1);
    $query = $this->db->get();

    // Disabled for now
    if ($query->num_rows() > 0 && !is_dev()) {
        return 'ip_fail';
    }

    $this->db->select('username');
    $this->db->from('user');
    $this->db->where('username', $username);
    $this->db->limit(1);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return false;
    }
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

        // Find user id
        $this->db->select_max('id');
        $this->db->from('user');
        $this->db->limit(1);
        $query = $this->db->get()->row();
        $user_id = $query->id;
        return $user_id;
    }
 }
 // Update user last load
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