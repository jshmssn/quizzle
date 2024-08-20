<?php

class quiz_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function save_question($questionText, $answers, $correctAnswerIndex, $roomId, $time, $imagePath, $isFill) {
        // Insert the question
        $data = array(
            'question_text' => $questionText,
            'room_id' => $roomId,
            'time' => $time,
            'isFill' => $isFill,
            'image_path' => $imagePath // Add image_path to the data array
        );
        $this->db->insert('questions', $data);
    
        if ($this->db->affected_rows() > 0) {
            $questionId = $this->db->insert_id();
            
            // Insert answers
            foreach ($answers as $index => $answerText) {
                $isCorrect = ($index === $correctAnswerIndex) ? 1 : 0;
                $this->db->insert('answers', array(
                    'question_id' => $questionId,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect
                ));
            }
            
            // Check if answers were inserted successfully
            return ($this->db->affected_rows() > 0);
        }
    
        return false;
    }
    
    public function save_room($roomId, $pin) {
        // Insert room_id, PIN, and isValid into rooms table
        $data = array(
            'room_id' => $roomId,
            'pin' => $pin,
            'isValid' => 1,
            'created_at' => NOW(),
            'updated_at' => NOW()
        );
    
        $this->db->insert('rooms', $data);
    
        return ($this->db->affected_rows() > 0);
    }    

    public function get_participants($room_pin) {
        // Check if room is valid
        if ($this->is_room_valid($room_pin)) {
            // Fetch participants from the database based on room_pin
            $this->db->where('room_pin', $room_pin);
            $query = $this->db->get('participants'); // Adjust table name and column names as needed
            
            return $query->result_array();
        } else {
            // Return an empty array or handle invalid room case
            return [];
        }
    }
    
    public function is_player_name_exists($name, $room_pin) {
        $this->db->where('name', $name);
        $this->db->where('room_pin', $room_pin);
        $query = $this->db->get('participants');
        
        return $query->num_rows() > 0;
    }
    
    public function process_join($name, $room_pin) {
        // Check if the name already exists for this room_pin
        $original_name = $name;
        $counter = 1;
        
        while ($this->is_player_name_exists($name, $room_pin)) {
            // Append the counter to the original name
            $name = $original_name . $counter;
            $counter++;
        }
        
        // Insert the unique player name into the 'participants' table
        $data = array(
            'name' => $name,
            'room_pin' => $room_pin
        );
        
        $this->db->insert('participants', $data);
        
        // Return the unique player name
        return $name;
    }    
    
    public function validate_room_pin($room_pin) {
        // Ensure the room_pin is properly sanitized/validated if necessary
        $this->db->where('pin', $room_pin);
        $query = $this->db->get('rooms');
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array(); // Get the row as an associative array
            return [
                'isValid' => $result['isValid'],
                'hasStarted' => $result['hasStarted']
            ];
        } else {
            return [
                'isValid' => 0,
                'hasStarted' => 0
            ];
        }
    }
    
    public function invalidate_room($roomId) {
        $data = array('isValid' => 0);
        $this->db->where('room_id', $roomId);
        $this->db->update('rooms', $data);
    
        return ($this->db->affected_rows() > 0);
    }
    
    public function left_participant($playerName, $roomPin) {
        $this->db->where('name', $playerName);
        $this->db->where('room_pin', $roomPin);
        $this->db->delete('participants');
    }

    public function exit_all_participants($roomPin){
        $this->db->where('room_pin', $roomPin);
        $this->db->delete('participants');
    }

    public function delete_room_questions($roomId) {
        $this->db->where('room_id', $roomId);
        $this->db->delete('questions');
    }

    public function is_room_valid($roomPin) {
        $this->db->select('isValid');
        $this->db->where('pin', $roomPin);
        $query = $this->db->get('rooms');
        $result = $query->row_array();
    
        return isset($result['isValid']) ? $result['isValid'] : null;
    }

    // Fetch a players
    public function get_players() {
        $this->db->select('name');
        $query = $this->db->get('participants');
        return $query->row_array();
    }

    // Fetch Room id by Room Pin
    public function get_room_id_by_pin($roomPin) {
        $this->db->select('room_id');
        $this->db->from('rooms');
        $this->db->where('pin', $roomPin);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->room_id; // Return the room ID
        } else {
            return null; // No matching room pin found
        }
    }

    // Fetch all question based on room_id
    public function get_questions_by_room($room_id) {
        $this->db->select('id, question_text, time, isFill');
        $this->db->where('room_id', $room_id);
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('questions');
        return $query->result_array();
    }

    // Fetch answers for a given question
    public function get_answers_by_question($question_id) {
        $this->db->select('id, answer_text, is_correct');
        $this->db->where('question_id', $question_id);
        $this->db->order_by('RAND()');
        $query = $this->db->get('answers');
        return $query->result_array();
    }    

    // Fetch correct answer for a given question
    public function get_correct_answers($question_id) {
        $this->db->select('answer_text');
        $this->db->where('question_id', $question_id);
        $this->db->where('is_correct', 1);
        $query = $this->db->get('answers');
        return $query->result_array();
    }   

    public function get_image_path($questId) {
        // Example query to get the image path
        $this->db->select('image_path');
        $this->db->from('questions');
        $this->db->where('id', $questId);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row()->image_path; // Return the image path
        }
        
        return ''; // Return empty if no path found
    }
}
?>
