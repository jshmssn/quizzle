<?php
class ApiController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('My_curl'); // Ensure this library exists or adjust as needed
    }

    public function fetch_from_node() {
        $url = 'http://localhost:3000/api/testserver';
    
        // Initialize cURL session
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Execute cURL request
        $response = curl_exec($ch);
    
        // Check for cURL errors
        if (curl_errno($ch)) {
            show_error('cURL error: ' . curl_error($ch));
        }
    
        curl_close($ch);
    
        // Error handling for JSON decode
        if ($response === FALSE) {
            show_error('Failed to retrieve data from Node.js server.');
        }
    
        $data = json_decode($response, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            show_error('Failed to decode JSON response from Node.js server.');
        }
    
        $this->load->view('node_data_view', ['data' => $data]);
    }
    
}

?>