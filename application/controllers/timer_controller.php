<?php

class timer_controller extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model("quiz_model");
        $this->load->library("session");
    }
    
    public function start_timer() {
        $questionId = $this->input->post('questionId');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/start-timer");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['questionId' => $questionId]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
            curl_close($ch);
            return;
        }
        
        curl_close($ch);
        
        $data = json_decode($response, true);
        $endTime = isset($data['endTime']) ? $data['endTime'] : 0;
        
        echo json_encode(['success' => true, 'endTime' => $endTime]);
    }
    
}

?>
