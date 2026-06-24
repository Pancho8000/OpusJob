<?php
class Job {
    private $db;

    public function __construct() {
        $this->db = new Database;
        $this->ensureJobSchema();
    }

    private function ensureJobSchema() {
        // Añadir columnas necesarias si no existen
        $cols = [
            'status' => "ENUM('draft', 'published') DEFAULT 'published'",
            'requirements_tech' => "TEXT",
            'requirements_soft' => "TEXT",
            'benefits' => "TEXT",
            'deadline' => "DATE",
            'recruiter_id' => "INT(11) NULL",
            'updated_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];

        foreach($cols as $col => $definition){
            try {
                $this->db->query("SELECT $col FROM jobs LIMIT 1");
                $this->db->execute();
            } catch (Exception $e) {
                // Si falla es porque no existe la columna
                $this->db->query("ALTER TABLE jobs ADD COLUMN $col $definition");
                $this->db->execute();
            }
        }
    }

    private function normalizeJob($job){
        if(!$job){
            return $job;
        }
        $fields = ['title','company','location','salary','type','description','requirements_tech','requirements_soft','benefits'];
        foreach($fields as $f){
            if(isset($job->$f) && is_string($job->$f)){
                $job->$f = html_entity_decode($job->$f, ENT_QUOTES, 'UTF-8');
            }
        }
        return $job;
    }

    public function getJobs($location = null, $limit = null, $offset = null) {
        $sql = "SELECT * FROM jobs WHERE status = 'published'";

        if($location){
            $location = trim((string)$location);
        }

        if ($location && $location === 'Remoto') {
            $sql .= " ORDER BY (location LIKE '%Remoto%') DESC, created_at DESC";
        } elseif ($location) {
            $sql .= " ORDER BY ((location LIKE :location) OR (location LIKE '%Remoto%')) DESC, created_at DESC";
        } else {
            $sql .= " ORDER BY created_at DESC";
        }

        if($limit !== null){
            $sql .= " LIMIT :limit";
            if($offset !== null){
                $sql .= " OFFSET :offset";
            }
        }
        $this->db->query($sql);
        if ($location && $location !== 'Remoto') {
            $this->db->bind(':location', '%' . $location . '%');
        }
        if($limit !== null){
            $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            if($offset !== null){
                $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        $rows = $this->db->resultSet();
        foreach($rows as $r){
            $this->normalizeJob($r);
        }
        return $rows;
    }

    public function getJobsForUser($userId, $location = null, $limit = null, $offset = null) {
        $sql = "
            SELECT j.*
            FROM jobs j
            WHERE j.status = 'published'
              AND NOT EXISTS (
                  SELECT 1
                  FROM job_likes jl
                  WHERE jl.job_id = j.id AND jl.user_id = :user_id
              )
        ";

        if($location){
            $location = trim((string)$location);
        }

        if ($location && $location === 'Remoto') {
            $sql .= " ORDER BY (j.location LIKE '%Remoto%') DESC, j.created_at DESC";
        } elseif ($location) {
            $sql .= " ORDER BY ((j.location LIKE :location) OR (j.location LIKE '%Remoto%')) DESC, j.created_at DESC";
        } else {
            $sql .= " ORDER BY j.created_at DESC";
        }

        if($limit !== null){
            $sql .= " LIMIT :limit";
            if($offset !== null){
                $sql .= " OFFSET :offset";
            }
        }

        $this->db->query($sql);
        $this->db->bind(':user_id', (int)$userId);
        if ($location && $location !== 'Remoto') {
            $this->db->bind(':location', '%' . $location . '%');
        }
        if($limit !== null){
            $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            if($offset !== null){
                $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        $rows = $this->db->resultSet();
        foreach($rows as $r){
            $this->normalizeJob($r);
        }
        return $rows;
    }

    public function createJob($data) {
        $this->db->query('INSERT INTO jobs (title, company, location, salary, type, description, requirements_tech, requirements_soft, benefits, deadline, status, recruiter_id) 
                          VALUES (:title, :company, :location, :salary, :type, :description, :requirements_tech, :requirements_soft, :benefits, :deadline, :status, :recruiter_id)');
        
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':company', $data['company']);
        $this->db->bind(':location', $data['location']);
        $this->db->bind(':salary', $data['salary']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':requirements_tech', $data['requirements_tech']);
        $this->db->bind(':requirements_soft', $data['requirements_soft']);
        $this->db->bind(':benefits', $data['benefits']);
        $this->db->bind(':deadline', $data['deadline']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':recruiter_id', $data['recruiter_id']);

        return $this->db->execute();
    }

    public function updateJob($data) {
        $this->db->query('UPDATE jobs SET title = :title, location = :location, salary = :salary, type = :type, 
                          description = :description, requirements_tech = :requirements_tech, requirements_soft = :requirements_soft, 
                          benefits = :benefits, deadline = :deadline, status = :status 
                          WHERE id = :id AND recruiter_id = :recruiter_id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':recruiter_id', $data['recruiter_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':location', $data['location']);
        $this->db->bind(':salary', $data['salary']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':requirements_tech', $data['requirements_tech']);
        $this->db->bind(':requirements_soft', $data['requirements_soft']);
        $this->db->bind(':benefits', $data['benefits']);
        $this->db->bind(':deadline', $data['deadline']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function getRecruiterJobs($recruiterId) {
        $this->db->query("SELECT * FROM jobs WHERE recruiter_id = :recruiter_id ORDER BY created_at DESC");
        $this->db->bind(':recruiter_id', $recruiterId);
        $rows = $this->db->resultSet();
        foreach($rows as $r){
            $this->normalizeJob($r);
        }
        return $rows;
    }

    public function getRecruiterJobsWithStats($recruiterId) {
        $this->db->query("
            SELECT
                j.*,
                COALESCE(a.postulations_count, 0) AS postulations_count
            FROM jobs j
            LEFT JOIN (
                SELECT job_id, COUNT(*) AS postulations_count
                FROM job_likes
                GROUP BY job_id
            ) a ON a.job_id = j.id
            WHERE j.recruiter_id = :recruiter_id
            ORDER BY j.created_at DESC
        ");
        $this->db->bind(':recruiter_id', (int)$recruiterId);
        $rows = $this->db->resultSet();
        foreach($rows as $r){
            $this->normalizeJob($r);
        }
        return $rows;
    }

    public function getAccountJobsWithStats($userId){
        $this->db->query("
            SELECT
                j.*,
                COALESCE(a.postulations_count, 0) AS postulations_count
            FROM jobs j
            LEFT JOIN (
                SELECT job_id, COUNT(*) AS postulations_count
                FROM job_likes
                GROUP BY job_id
            ) a ON a.job_id = j.id
            WHERE j.recruiter_id = :user_id
            ORDER BY j.created_at DESC
        ");
        $this->db->bind(':user_id', (int)$userId);
        $rows = $this->db->resultSet();
        foreach($rows as $r){
            $this->normalizeJob($r);
        }
        return $rows;
    }

    public function claimOrphanJobsForAccount($userId, $companyName){
        $companyName = trim((string)$companyName);
        if($userId <= 0 || $companyName === ''){
            return 0;
        }
        $companyEncoded = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
        $this->db->query("
            UPDATE jobs
            SET recruiter_id = :user_id
            WHERE (recruiter_id IS NULL OR recruiter_id = 0)
              AND (company = :company OR company = :company_encoded)
        ");
        $this->db->bind(':user_id', (int)$userId);
        $this->db->bind(':company', $companyName);
        $this->db->bind(':company_encoded', $companyEncoded);
        $this->db->execute();
        return (int)$this->db->rowCount();
    }

    public function getRecruiterJobById($recruiterId, $jobId){
        $this->db->query("SELECT * FROM jobs WHERE id = :id AND recruiter_id = :recruiter_id");
        $this->db->bind(':id', (int)$jobId);
        $this->db->bind(':recruiter_id', (int)$recruiterId);
        return $this->normalizeJob($this->db->single());
    }

    public function setRecruiterJobStatus($recruiterId, $jobId, $status){
        $this->db->query("UPDATE jobs SET status = :status, updated_at = NOW() WHERE id = :id AND recruiter_id = :recruiter_id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', (int)$jobId);
        $this->db->bind(':recruiter_id', (int)$recruiterId);
        return $this->db->execute();
    }

    public function deleteRecruiterJob($recruiterId, $jobId){
        $this->db->query("DELETE FROM jobs WHERE id = :id AND recruiter_id = :recruiter_id");
        $this->db->bind(':id', (int)$jobId);
        $this->db->bind(':recruiter_id', (int)$recruiterId);
        return $this->db->execute();
    }

    public function getPublicRecruiterJobsWithStats($recruiterId){
        $this->db->query("
            SELECT
                j.*,
                COALESCE(a.postulations_count, 0) AS postulations_count
            FROM jobs j
            LEFT JOIN (
                SELECT job_id, COUNT(*) AS postulations_count
                FROM job_likes
                GROUP BY job_id
            ) a ON a.job_id = j.id
            WHERE j.recruiter_id = :recruiter_id AND j.status = 'published'
            ORDER BY j.created_at DESC
        ");
        $this->db->bind(':recruiter_id', (int)$recruiterId);
        $rows = $this->db->resultSet();
        foreach($rows as $r){
            $this->normalizeJob($r);
        }
        return $rows;
    }

    public function getJobById($id) {
        $this->db->query("SELECT * FROM jobs WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->normalizeJob($this->db->single());
    }
}
