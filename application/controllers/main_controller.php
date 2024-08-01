<?php

class main_controller extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model("quiz_model");
        $this->load->library("session");
    }

    public function index() {
        $this->load->view('welcome');
    }

    public function create() {
        $this->load->view('../views/create/createquiz');
    }

    public function creator()
	{
		$this->load->view('../views/create/quiz_creator');
        
	}
    public function submit() {
        $questions = $this->input->post('questions');
        
        if (!empty($questions)) {
            $success = true;
    
            // Generate room_id and PIN
            $roomId = uniqid(); // You might want to use a different method to generate unique room IDs
            $pin = rand(1000, 9999); // Generate a 4-digit PIN
    
            // Save the questions
            foreach ($questions as $question) {
                $questionText = $this->security->xss_clean($question['text']);
                $answers = array_map([$this->security, 'xss_clean'], $question['answers']);
                $correctAnswerIndex = (int) $question['correct'];
    
                if (!$this->quiz_model->save_question($questionText, $answers, $correctAnswerIndex)) {
                    $success = false;
                    break;
                }
            }
    
            if ($success) {
                // Save room_id and PIN to the rooms table
                if ($this->quiz_model->save_room($roomId, $pin)) {
                    $this->session->set_flashdata('status', 'success');
                    $this->session->set_flashdata('msg', 'Quiz questions submitted successfully and room created!');
                    
                    // Store roomId and roomPin in regular session data
                    $this->session->set_userdata('roomId', $roomId);
                    $this->session->set_userdata('roomPin', $pin);
                } else {
                    $this->session->set_flashdata('status', 'error');
                    $this->session->set_flashdata('msg', 'Failed to save room details.');
                }
            } else {
                $this->session->set_flashdata('status', 'error');
                $this->session->set_flashdata('msg', 'Failed to submit quiz questions.');
            }
        } else {
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'No quiz questions provided.');
        }
    
        redirect('main_controller/hostgame');
    }
    
    public function hostgame(){
        $this->load->view('host/host');
    }

    public function get_participants() {
        // Ensure roomPin is available
        $roomPin = $this->session->userdata('roomPin');
    
        if ($roomPin) {
            // Fetch participants for the room
            $participants = $this->quiz_model->get_participants_by_room_pin($roomPin);
    
            // Prepare participants data for the view
            $data['participants'] = $participants;
            
            // Load the view and pass participants data
            $this->load->view('host/host', $data);
        } else {
            // Handle case where roomPin is not set
            $data['participants'] = [];
            $this->load->view('host/host', $data);
        }
    }
}
?>
