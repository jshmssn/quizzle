<?php

class quiz_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function getParticipants(){
        $this->db->where("isValid",1);        
        $result = $this->db->get("participants");
        return $result->result_array();
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
}
?>
