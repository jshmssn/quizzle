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

    public function creator() {
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
    
    public function hostgame() {
        $roomPin = $this->session->userdata('roomPin');
    
        // Fetch participants using the updated model method
        $data['participants'] = $this->quiz_model->get_participants($roomPin);
    
        $this->load->view('host/host', $data);
    }
    
    public function get_players() {
        $roomPin = $this->session->userdata('roomPin');
    
        if ($roomPin) {
            // Fetch participants using the model
            $participants = $this->quiz_model->get_participants($roomPin);
    
            // Return participants as JSON
            echo json_encode(['players' => $participants]);
        } else {
            echo json_encode(['players' => []]);
        }
    }

    public function join() {
        // Get form data
        $name = $this->input->post('name');
        $room_pin = $this->input->post('room_pin');
    
        // Validate the input (e.g., check if the room_pin exists)
        if ($this->quiz_model->validate_room_pin($room_pin)) {
            // Store the player's name and room_pin in session data
            $this->session->set_userdata('player_name', $name);
            $this->session->set_userdata('room_pin', $room_pin);
    
            // Process the join logic
            $this->quiz_model->process_join($name, $room_pin);
    
            // Redirect to the room
            redirect('main_controller/room');
        } else {
            // Redirect back with an error message
            $this->session->set_flashdata('error', 'Invalid Room PIN.');
            redirect('main_controller');
        }
    }    
    
    public function room() {
        // Check if player is logged in by checking session data
        if (!$this->session->userdata('player_name') || !$this->session->userdata('room_pin')) {
            // If not, redirect to a different page (e.g., main page)
            redirect('main_controller');
        }
    
        // Load the confirmation view
        $this->load->view('player/room');
    }
    
    public function quitroom() {    
        // Get the room pin from session data
        $roomPin = $this->session->userdata('roomPin');
    
        // Check if the room pin exists in the session
        if ($roomPin) {
            // Update the room's validity status
            $this->quiz_model->invalidate_room($roomPin);
    
            // Unset the roomPin session data
            $this->session->unset_userdata('roomPin');
    
            // Set a flash message for success
            $this->session->set_flashdata('status', 'success');
            $this->session->set_flashdata('msg', 'You have left the room successfully.');
        } else {
            // Set a flash message for error
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'Room PIN could not be found.');
        }
    
        // Redirect to the index page
        redirect('welcome');
    }
    
    public function leftroom() {
        // Get the player name and room PIN from session data
        $playerName = $this->session->userdata('player_name');
        $roomPin = $this->session->userdata('room_pin');
    
        // Check if player name and room PIN exist in session
        if ($playerName && $roomPin) {
            // Call the model method to delete the participant
            $this->quiz_model->left_participant($playerName, $roomPin);
    
            // Unset the player name and room PIN from session data
            $this->session->unset_userdata('player_name');
            $this->session->unset_userdata('room_pin');
        }
    
        // Redirect to the welcome page
        redirect('welcome');
    }
    
}

?>
