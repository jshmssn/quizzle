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

    public function get_room_data() {
        // Example SQL to fetch room_pin and participants; adjust based on your schema
        $this->db->select('rooms.room_pin, participants.name');
        $this->db->from('rooms');
        $this->db->join('participants', 'participants.room_id = rooms.id');
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            // Assuming all participants belong to the same room, take room_pin from the first row
            $room_pin = $result[0]['room_pin'];
            $participants = array_column($result, 'name');
            return [
                'room_pin' => $room_pin,
                'participants' => $participants
            ];
        }
    
        return null; // No data found
    }
    
}
?>
