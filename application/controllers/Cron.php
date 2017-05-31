<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');

class Cron extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('main_model', '', TRUE);
    }

    // Map view
    public function index($token = false)
    {
      // Use hash equals function to prevent timing attack
      $auth = auth();
      if (!$token) {
        return false;
      }
      if (!hash_equals($auth->token, $token)) {
        return false;
      }

      // Check if it's time to run
      $crontab = '* * * * *'; // Every minute
      $now = date('Y-m-d H:i:s');
      $run_crontab = parse_crontab($now, $crontab);
      if (!$run_crontab) {
          echo 'Start of Cron - ' . time() . '<br>';
        return false;
      }

      // Run cron
      echo 'Start of Cron - ' . time() . '<br>';

      echo 'End of Cron - ' . time() . '<br>';
    }

}