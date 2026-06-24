<?php
class User {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function ensureRecruiterPipelineSchema(){
        $ddl = [
            "ALTER TABLE candidates ADD COLUMN email varchar(255) DEFAULT NULL",
            "ALTER TABLE candidates ADD COLUMN phone varchar(50) DEFAULT NULL",
            "ALTER TABLE candidates ADD COLUMN cv_url varchar(255) DEFAULT NULL",
            "ALTER TABLE candidates ADD COLUMN years_experience int(11) NOT NULL DEFAULT 0",
            "ALTER TABLE candidate_likes ADD COLUMN status varchar(32) NOT NULL DEFAULT 'postulacion_recibida'",
            "ALTER TABLE candidate_likes ADD COLUMN job_id int(11) DEFAULT NULL",
            "ALTER TABLE candidate_likes ADD COLUMN updated_at datetime DEFAULT NULL",
            "CREATE INDEX idx_candidate_likes_recruiter_status ON candidate_likes (recruiter_id, status)",
            "CREATE INDEX idx_candidate_likes_job ON candidate_likes (job_id)",
            "CREATE INDEX idx_candidates_exp ON candidates (years_experience)",
        ];

        foreach($ddl as $sql){
            try{
                $this->db->query($sql);
                $this->db->execute();
            } catch(PDOException $e){
                $code = (string)$e->getCode();
                if($code === '42S21' || $code === '42000'){
                    continue;
                }
                Logger::warning('schema_migration_failed', ['sql' => $sql, 'error' => $e->getMessage(), 'code' => $code]);
            }
        }

        try{
            $this->db->query("UPDATE candidate_likes SET status = 'postulacion_recibida' WHERE status IS NULL OR status = ''");
            $this->db->execute();
        } catch(PDOException $e){
        }
    }

    public function ensureJobApplicationSchema(){
        $ddl = [
            "ALTER TABLE job_likes ADD COLUMN status varchar(32) NOT NULL DEFAULT 'postulacion_recibida'",
            "ALTER TABLE job_likes ADD COLUMN updated_at datetime DEFAULT NULL",
            "CREATE INDEX idx_job_likes_job_status ON job_likes (job_id, status)",
        ];

        foreach($ddl as $sql){
            try{
                $this->db->query($sql);
                $this->db->execute();
            } catch(PDOException $e){
                $code = (string)$e->getCode();
                if($code === '42S21' || $code === '42000'){
                    continue;
                }
                Logger::warning('schema_migration_failed', ['sql' => $sql, 'error' => $e->getMessage(), 'code' => $code]);
            }
        }

        try{
            $this->db->query("UPDATE job_likes SET status = 'postulacion_recibida', updated_at = COALESCE(updated_at, created_at, NOW()) WHERE status IS NULL OR status = '' OR updated_at IS NULL");
            $this->db->execute();
        } catch(PDOException $e){
        }
    }

    // Registrar Usuario
    public function register($data){
        $this->db->query('INSERT INTO users (name, email, location, password, role) VALUES(:name, :email, :location, :password, :role)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':location', $data['location']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', $data['role']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    // Login Usuario
    public function login($email, $password){
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        $hashed_password = $row->password;
        if(password_verify($password, $hashed_password)){
            return $row;
        } else {
            return false;
        }
    }

    // Encontrar usuario por email
    public function findUserByEmail($email){
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($this->db->rowCount() > 0){
            return true;
        } else {
            return false;
        }
    }

    public function getUserById($id){
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateProfile($data){
        $fields = 'name = :name, location = :location';
        if(!empty($data['avatar'])){
            $fields .= ', avatar = :avatar';
        }
        if(!empty($data['password'])){
            $fields .= ', password = :password';
        }

        $this->db->query('UPDATE users SET ' . $fields . ' WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':location', $data['location']);
        if(!empty($data['avatar'])){
            $this->db->bind(':avatar', $data['avatar']);
        }
        if(!empty($data['password'])){
            $this->db->bind(':password', $data['password']);
        }
        return $this->db->execute();
    }

    public function likeJob($userId, $jobId){
        $this->ensureJobApplicationSchema();
        $this->db->query('INSERT IGNORE INTO job_likes (user_id, job_id) VALUES (:user_id, :job_id)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':job_id', $jobId);
        $ok = $this->db->execute();

        try{
            $this->db->query("UPDATE job_likes SET status = COALESCE(NULLIF(status, ''), 'postulacion_recibida'), updated_at = NOW() WHERE user_id = :user_id AND job_id = :job_id");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':job_id', $jobId);
            $this->db->execute();
        } catch(PDOException $e){
        }

        try{
            $this->db->query('SELECT recruiter_id, status FROM jobs WHERE id = :job_id');
            $this->db->bind(':job_id', (int)$jobId);
            $job = $this->db->single();
            Logger::info('job_application_created', ['user_id' => (int)$userId, 'job_id' => (int)$jobId, 'recruiter_id' => isset($job->recruiter_id) ? (int)$job->recruiter_id : null, 'status' => 'postulacion_recibida']);
        } catch(Exception $e){
        }
        return $ok;
    }

    public function clearLikedJobs($userId){
        $this->db->query('DELETE FROM job_likes WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function removeLikedJob($userId, $jobId){
        $this->db->query('DELETE FROM job_likes WHERE user_id = :user_id AND job_id = :job_id');
        $this->db->bind(':user_id', (int)$userId);
        $this->db->bind(':job_id', (int)$jobId);
        return $this->db->execute();
    }

    public function getLikedJobs($userId){
        $this->db->query('SELECT j.* FROM job_likes jl INNER JOIN jobs j ON jl.job_id = j.id WHERE jl.user_id = :user_id ORDER BY jl.created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function likeCandidate($recruiterId, $candidateId){
        $this->ensureRecruiterPipelineSchema();
        $this->db->query('INSERT IGNORE INTO candidate_likes (recruiter_id, candidate_id) VALUES (:recruiter_id, :candidate_id)');
        $this->db->bind(':recruiter_id', $recruiterId);
        $this->db->bind(':candidate_id', $candidateId);
        $ok = $this->db->execute();

        try{
            $this->db->query("UPDATE candidate_likes SET status = COALESCE(status, 'postulacion_recibida'), updated_at = NOW() WHERE recruiter_id = :recruiter_id AND candidate_id = :candidate_id");
            $this->db->bind(':recruiter_id', $recruiterId);
            $this->db->bind(':candidate_id', $candidateId);
            $this->db->execute();
        } catch(PDOException $e){
        }

        return $ok;
    }

    public function clearLikedCandidates($recruiterId){
        $this->db->query('DELETE FROM candidate_likes WHERE recruiter_id = :recruiter_id');
        $this->db->bind(':recruiter_id', $recruiterId);
        return $this->db->execute();
    }

    public function getLikedCandidates($recruiterId){
        $this->db->query('SELECT c.* FROM candidate_likes cl INNER JOIN candidates c ON cl.candidate_id = c.id WHERE cl.recruiter_id = :recruiter_id ORDER BY cl.created_at DESC');
        $this->db->bind(':recruiter_id', $recruiterId);
        return $this->db->resultSet();
    }

    public function getRecruiterPipeline($recruiterId, $filters = []){
        $this->ensureRecruiterPipelineSchema();
        $this->ensureJobApplicationSchema();

        $sql = "SELECT
            cl.candidate_id,
            'candidate' AS entry_type,
            cl.status,
            cl.job_id,
            cl.created_at,
            cl.updated_at,
            c.name,
            c.profession,
            c.location,
            c.skills,
            c.bio,
            c.image,
            c.email,
            c.phone,
            c.cv_url,
            c.years_experience,
            j.title AS job_title,
            j.company AS job_company
        FROM candidate_likes cl
        INNER JOIN candidates c ON cl.candidate_id = c.id
        LEFT JOIN jobs j ON cl.job_id = j.id
        WHERE cl.recruiter_id = :recruiter_id";

        $params = ['recruiter_id' => (int)$recruiterId];

        $jobId = $filters['job_id'] ?? null;
        if(is_int($jobId) && $jobId > 0){
            $sql .= " AND cl.job_id = :job_id";
            $params['job_id'] = (int)$jobId;
        }

        $status = (string)($filters['status'] ?? '');
        if($status !== ''){
            $sql .= " AND cl.status = :status";
            $params['status'] = $status;
        }

        $q = (string)($filters['q'] ?? '');
        if($q !== ''){
            $sql .= " AND (c.skills LIKE :q OR c.profession LIKE :q OR c.bio LIKE :q)";
            $params['q'] = '%' . $q . '%';
        }

        $minExp = $filters['min_exp'] ?? 0;
        if(is_int($minExp) && $minExp > 0){
            $sql .= " AND c.years_experience >= :min_exp";
            $params['min_exp'] = (int)$minExp;
        }

        $sql .= " ORDER BY COALESCE(cl.updated_at, cl.created_at) DESC";

        $this->db->query($sql);
        foreach($params as $k => $v){
            $this->db->bind(':' . $k, $v);
        }
        $rows = $this->db->resultSet();

        $appSql = "SELECT
            jl.user_id AS candidate_id,
            'job_applicant' AS entry_type,
            COALESCE(NULLIF(jl.status, ''), 'postulacion_recibida') AS status,
            jl.job_id,
            jl.created_at,
            jl.updated_at,
            u.name,
            'Postulante registrado' AS profession,
            COALESCE(u.location, '') AS location,
            '' AS skills,
            CONCAT('Postulacion enviada a ', j.title) AS bio,
            '' AS image,
            u.email,
            '' AS phone,
            '' AS cv_url,
            0 AS years_experience,
            j.title AS job_title,
            j.company AS job_company
        FROM job_likes jl
        INNER JOIN jobs j ON jl.job_id = j.id
        INNER JOIN users u ON jl.user_id = u.id
        WHERE j.recruiter_id = :recruiter_id";

        $appParams = ['recruiter_id' => (int)$recruiterId];

        if(is_int($jobId) && $jobId > 0){
            $appSql .= " AND jl.job_id = :job_id";
            $appParams['job_id'] = (int)$jobId;
        }

        if($status !== ''){
            $appSql .= " AND COALESCE(NULLIF(jl.status, ''), 'postulacion_recibida') = :status";
            $appParams['status'] = $status;
        }

        if($q !== ''){
            $appSql .= " AND (u.name LIKE :q OR u.email LIKE :q OR u.location LIKE :q OR j.title LIKE :q)";
            $appParams['q'] = '%' . $q . '%';
        }

        $appSql .= " ORDER BY COALESCE(jl.updated_at, jl.created_at) DESC";
        $this->db->query($appSql);
        foreach($appParams as $k => $v){
            $this->db->bind(':' . $k, $v);
        }
        $appRows = $this->db->resultSet();
        $rows = array_merge($appRows, $rows);
        usort($rows, function($a, $b){
            $ad = strtotime((string)($a->updated_at ?? $a->created_at ?? ''));
            $bd = strtotime((string)($b->updated_at ?? $b->created_at ?? ''));
            return $bd <=> $ad;
        });

        return $rows;
    }

    public function updateRecruiterPipeline($recruiterId, $candidateId, $status, $jobId, $entryType = 'candidate'){
        $this->ensureRecruiterPipelineSchema();
        $this->ensureJobApplicationSchema();

        if($entryType === 'job_applicant'){
            if($jobId === null || (int)$jobId <= 0){
                return false;
            }
            $this->db->query("UPDATE job_likes jl INNER JOIN jobs j ON jl.job_id = j.id SET jl.status = :status, jl.updated_at = NOW() WHERE j.recruiter_id = :recruiter_id AND jl.user_id = :candidate_id AND jl.job_id = :job_id");
            $this->db->bind(':status', $status);
            $this->db->bind(':recruiter_id', (int)$recruiterId);
            $this->db->bind(':candidate_id', (int)$candidateId);
            $this->db->bind(':job_id', (int)$jobId);
            return $this->db->execute();
        }

        $this->db->query("UPDATE candidate_likes SET status = :status, job_id = :job_id, updated_at = NOW() WHERE recruiter_id = :recruiter_id AND candidate_id = :candidate_id");
        $this->db->bind(':status', $status);
        if($jobId === null){
            $this->db->bind(':job_id', null, PDO::PARAM_NULL);
        } else {
            $this->db->bind(':job_id', (int)$jobId, PDO::PARAM_INT);
        }
        $this->db->bind(':recruiter_id', (int)$recruiterId);
        $this->db->bind(':candidate_id', (int)$candidateId);
        return $this->db->execute();
    }
}
