<?php

class main_controller extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model("quiz_model");
        $this->load->library("session");
    }

    public function index() {
        $items = array('player_name', 'room_pin');
        $this->session->unset_userdata($items);
        $this->load->view('welcome');
    }

    public function create() {
        $this->load->view('../views/create/createquiz');
    }

    public function creator() {
        $this->load->view('../views/create/quiz_creator');
    }

    public function submit() {
        $items = array('player_name', 'room_pin');
        $this->session->unset_userdata($items);
    
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
                $time = (int) $question['time']; // New line to get the time value
    
                if (!$this->quiz_model->save_question($questionText, $answers, $correctAnswerIndex, $pin, $time)) {
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
                    $this->session->set_userdata('room_pin', $pin);
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
    
        redirect('/hostgame');
    }
    
    public function hostgame() {
        $roomPin = $this->session->userdata('room_pin');
    
        // Fetch participants using the updated model method
        $data['participants'] = $this->quiz_model->get_participants($roomPin);
    
        $this->load->view('host/host', $data);
    }
    
    public function get_players() {
        $roomPin = $this->session->userdata('room_pin');
    
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
        $validation_result = $this->quiz_model->validate_room_pin($room_pin);

        // Check if the room is valid and has started
        if ($validation_result['isValid'] == '0' && $validation_result['hasStarted'] == '1') {
            // Set an error message in flashdata
            $this->session->set_flashdata("status", "error");
            $this->session->set_flashdata("msg", "The game has already started or the room is invalid.");
            
            // Redirect to an error page or previous page
            redirect('/error'); // Adjust the redirect URL as needed
        } elseif ($validation_result['isValid'] == '1' && $validation_result['hasStarted'] == '0') {
            // Process the join logic and get the unique player name
            $unique_name = $this->quiz_model->process_join($name, $room_pin);
            
            // Store the player's unique name and room_pin in session data
            $this->session->set_userdata('player_name', $unique_name);
            $this->session->set_userdata('room_pin', $room_pin);
            
            // Redirect to the room
            redirect('/room');
        } else {
            // Set an error message in flashdata
            $this->session->set_flashdata("status", "error");
            $this->session->set_flashdata("msg", "Invalid PIN");
            
            // Redirect back to the join page or previous page
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function room() {
        // Check if player is logged in by checking session data
        if (!$this->session->userdata('player_name') || !$this->session->userdata('room_pin')) {
            // If not, redirect to a different page (e.g., main page)
            redirect(base_url());
        }
    
        // Load the confirmation view
        $this->load->view('player/room');
    }
    
    public function quitroom() {    
        // Get the room pin from session data
        $roomPin = $this->session->userdata('room_pin');
    
        // Check if the room pin exists in the session
        if ($roomPin) {
            // Update the room's validity status
            $this->quiz_model->invalidate_room($roomPin);
            $this->quiz_model->exit_all_participants($roomPin);
            $this->quiz_model->delete_room_questions($roomPin);
    
            // Unset the roomPin session data
            $items = array('player_name', 'room_pin');
            $this->session->unset_userdata($items);
    
            // Set a flash message for success
            $this->session->set_flashdata('status', 'success');
            $this->session->set_flashdata('msg', 'You have left the room successfully.');
        } else {
            // Set a flash message for error
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'Room PIN could not be found.');
        }
    
        // Redirect to the index page
        redirect(base_url());
    }
    
    public function leftroom() {
        // Get the player name and room PIN from session data
        $playerName = $this->session->userdata('player_name');
        $roomPin = $this->session->userdata('room_pin');
    
        // Check if player name and room PIN exist in session
        if ($playerName && $roomPin) {
            // Call the model method to delete the participant
            $this->quiz_model->left_participant($playerName, $roomPin);
    
            // Unset the player name from session data
            $this->session->unset_userdata('player_name');
        }
    
        // Redirect to the welcome page
        redirect(base_url());
    }

    public function get_room_status() {
        // Retrieve the room PIN from the query parameters
        $roomPin = $this->input->get('pin');
    
        // Validate the room PIN
        if ($roomPin) {
            // Fetch the room status from the database
            $this->db->select('isValid, hasStarted');
            $this->db->where('pin', $roomPin);
            $query = $this->db->get('rooms');
    
            if ($query->num_rows() > 0) {
                $result = $query->row();
                $response = array(
                    'isValid' => $result->isValid,
                    'hasStarted' => $result->hasStarted
                );
            } else {
                $response = array(
                    'isValid' => 0,
                    'hasStarted' => 0
                );
            }
        } else {
            $response = array(
                'isValid' => 0,
                'hasStarted' => 0
            );
        }
    
        // Return the response in JSON format
        echo json_encode($response);
    }

    public function start_game() {
        // Load the quiz model
        $this->load->model('quiz_model');
        
        // Get a random question
        $question = $this->quiz_model->get_question();
        
        if (!$question) {
            // Handle case where no question is found
            show_error('No questions found.');
            return;
        }
        
        $question_text = $question['question_text'];
        $question_id = $question['id'];
        $question_time = $question['time']; // Fetch the question's time
    
        // Get answers for the question
        $answers = $this->quiz_model->get_answers($question_id);
        
        if (!$answers) {
            // Handle case where no answers are found for the question
            show_error('No answers found for the question.');
            return;
        }
        
        // Shuffle the answers array to randomize the order
        shuffle($answers);
        
        // Get all players and their scores
        $players = $this->quiz_model->get_players();
        
        // Find the correct answer
        $correct_answer = null;
        foreach ($answers as $answer) {
            if (isset($answer['is_correct']) && $answer['is_correct']) {
                $correct_answer = $answer['answer_text'];
                break;
            }
        }
        
        // Prepare data for the view
        $data = [
            'question' => $question_text,
            'question_id' => $question_id,
            'time' => $question_time, // Include the question's time
            'answers' => $answers, // Pass the shuffled answers
            'correct_answer' => $correct_answer, // Include the correct answer in the data
            'players' => $players
        ];
        
        // Load the view with data
        $this->load->view('player/quiz_view', $data);
    }

    public function start(){
        // Example: Fetch the room PIN from the input
        $roomPin = $this->input->post('room_pin');
    
        if ($roomPin) {
            // Update the hasStarted field to 1 where the pin matches
            $this->db->where('pin', $roomPin);
            $this->db->set('hasStarted', 1);
            $this->db->set('isValid', 0);
            $updateSuccess = $this->db->update('rooms');
    
            if (!$updateSuccess) {
                // Handle the error if the update failed
                $this->session->set_flashdata('status', 'error');
                $this->session->set_flashdata('msg', 'Failed to update room status.');
            }
        } else {
            // Handle the case where roomPin is not set or is invalid
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'Room PIN is not set.');
        }
    }
    
    public function start_game_host() {
        // Get a random question
        $question = $this->quiz_model->get_question();
        $question_text = $question['question_text'];
        $question_id = $question['id'];
    
        // Get answers for the question
        $answers = $this->quiz_model->get_answers($question_id);
    
        // Get all players and their scores
        $players = $this->quiz_model->get_players();
    
        // Find the correct answer
        $correct_answer = null;
        foreach ($answers as $answer) {
            if ($answer['is_correct']) {
                $correct_answer = $answer['answer_text'];
                break;
            }
        }
    
        // Prepare data for the view
        $data['question'] = $question_text;
        $data['question_id'] = $question_id; // Include the question ID
        $data['answers'] = array_map(function($answer) {
            return $answer['answer_text'];
        }, $answers);
        $data['correct_answer'] = $correct_answer; // Include the correct answer in the data
        $data['players'] = $players;
    
        // Load the view with data
        $this->load->view('host/quiz_view_host', $data);
    }
    
}

?>
