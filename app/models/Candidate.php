<?php
class Candidate {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getCandidates($limit = null, $offset = null) {
        $sql = "SELECT * FROM candidates ORDER BY created_at DESC";
        if($limit !== null){
            $sql .= " LIMIT :limit";
            if($offset !== null){
                $sql .= " OFFSET :offset";
            }
        }
        $this->db->query($sql);
        if($limit !== null){
            $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            if($offset !== null){
                $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        return $this->db->resultSet();
    }

    public function getCandidateById($id) {
        $this->db->query("SELECT * FROM candidates WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
