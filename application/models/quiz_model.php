<?php

class quiz_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function save_question($questionText, $answers, $correctAnswerIndex) {
        // Insert the question
        $data = array(
            'question_text' => $questionText
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
        // Insert room_id and PIN into rooms table
        $data = array(
            'room_id' => $roomId,
            'pin' => $pin
        );

        $this->db->insert('rooms', $data);

        return ($this->db->affected_rows() > 0);
    }

    public function get_participants($room_pin) {
        // Fetch participants from the database based on room_pin
        $this->db->where('room_pin', $room_pin);
        $query = $this->db->get('participants'); // Adjust table name and column names as needed
        
        return $query->result_array();
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
    }
    
    
    public function validate_room_pin($room_pin) {
        // Query the database to check if the room_pin exists
        $this->db->where('pin', $room_pin);
        $query = $this->db->get('rooms');
        
        return $query->num_rows() > 0;
    }
    
    public function invalidate_room($roomPin) {
        $data = array('isValid' => 0);
        $this->db->where('pin', $roomPin);
        $this->db->update('rooms', $data);
    }

    public function left_participant($playerName, $roomPin) {
        // Delete the participant based on the player name and room PIN
        $this->db->where('name', $playerName);
        $this->db->where('room_pin', $roomPin);
        $this->db->delete('participants'); // Adjust table name if different
    }
    
}
?>
