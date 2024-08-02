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
                    $this->session->set_flashdata('msg', 'Quiz questions and room has been created successfully!');
                    
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
    
        $this->load->view('host/host');
    }

    public function get_participants() {
        // Load the quiz model
        $this->load->model('quiz_model');
    
        // Assuming room_pin is passed via GET or is already available in the session
        $room_pin = $this->input->get('room_pin') ?? $this->session->userdata('roomPin');
    
        if ($room_pin) {
            // Fetch participants from the model
            $participants = $this->quiz_model->get_participants_by_room($room_pin);
    
            if (!empty($participants)) {
                // Return participants' names as a newline-separated string
                echo implode("\n", array_column($participants, 'name'));
            } else {
                echo "No participants yet.";
            }
        } else {
            echo "Room PIN not provided.";
        }
    }
    

    public function start_game() {
        // Your logic to start the game
        $room_pin = $this->input->post('room_pin');
        // Implement the game start logic here
        // ...
    }
}
?>
