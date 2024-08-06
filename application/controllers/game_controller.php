<?php

class game_controller extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model("quiz_model");
        $this->load->library("session");
    }
    
    public function start_game() {
        // Get a random question
        $question = $this->quiz_model->get_question();
        $question_text = $question['question_text'];
        $question_id = $question['id'];
    
        // Get answers for the question
        $answers = $this->quiz_model->get_answers($question_id);
    
        // Shuffle the answers array to randomize the order
        shuffle($answers);
    
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
        $data['answers'] = $answers; // Pass the shuffled answers
        $data['correct_answer'] = $correct_answer; // Include the correct answer in the data
        $data['players'] = $players;
    
        // Load the view with data
        $this->load->view('player/quiz_view', $data);
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
        $data['answers'] = array_map(function($answer) {
            return $answer['answer_text'];
        }, $answers);
        $data['correct_answer'] = $correct_answer; // Include the correct answer in the data
        $data['players'] = $players;
    
        // Example: Fetch the room PIN from the session or input
        $roomPin = $this->session->userdata('room_pin'); // Ensure this matches how you're storing the room PIN
    
        if ($roomPin) {
            // Update the hasStarted field to 1 where the pin matches
            $this->db->where('pin', $roomPin);
            $this->db->set('hasStarted', 1);
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
    
        // Load the view with data
        $this->load->view('host/quiz_view_host', $data);
    }
}

?>
