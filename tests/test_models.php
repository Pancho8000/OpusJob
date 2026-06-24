<?php
if(!class_exists('Database', false)){
    class Database {
        public $sql;
        public $bindings = [];

        public function query($sql){
            $this->sql = $sql;
        }

        public function bind($param, $value, $type = null){
            $this->bindings[$param] = [$value, $type];
        }

        public function resultSet(){
            return [];
        }

        public function single(){
            return null;
        }

        public function execute(){
            return true;
        }
    }
}

require_once __DIR__ . '/../app/models/Job.php';
require_once __DIR__ . '/../app/models/Candidate.php';

$job = new Job();
$job->getJobs('Santiago', 10, 5);
$jobRef = new ReflectionObject($job);
$jobDbProp = $jobRef->getProperty('db');
$jobDbProp->setAccessible(true);
$jobDb = $jobDbProp->getValue($job);
$runner->assertTrue(strpos($jobDb->sql ?? '', "WHERE status = 'published'") !== false, 'Job::getJobs filtra por status published');
$runner->assertTrue(strpos($jobDb->sql ?? '', 'AND (location LIKE') === false, 'Job::getJobs no filtra por ubicación (solo ordena por relevancia)');
$runner->assertTrue(strpos($jobDb->sql ?? '', 'ORDER BY') !== false, 'Job::getJobs aplica ORDER BY');
$runner->assertTrue(isset($jobDb->bindings[':location']), 'Job::getJobs bindea location cuando corresponde');
$runner->assertTrue(strpos($jobDb->sql ?? '', 'LIMIT') !== false, 'Job::getJobs aplica LIMIT');
$runner->assertTrue(isset($jobDb->bindings[':limit']), 'Job::getJobs bindea limit');
$runner->assertTrue(isset($jobDb->bindings[':offset']), 'Job::getJobs bindea offset');

$job2 = new Job();
$job2->getJobs('Remoto', 10, 0);
$jobRef2 = new ReflectionObject($job2);
$jobDbProp2 = $jobRef2->getProperty('db');
$jobDbProp2->setAccessible(true);
$jobDb = $jobDbProp2->getValue($job2);
$runner->assertTrue(!isset($jobDb->bindings[':location']), 'Job::getJobs no bindea location cuando la ubicación es Remoto');

$job3 = new Job();
$job3->getJobsForUser(15, 'Santiago', 10, 0);
$jobRef3 = new ReflectionObject($job3);
$jobDbProp3 = $jobRef3->getProperty('db');
$jobDbProp3->setAccessible(true);
$jobDb = $jobDbProp3->getValue($job3);
$runner->assertTrue(strpos($jobDb->sql ?? '', 'NOT EXISTS') !== false, 'Job::getJobsForUser excluye jobs ya matcheados');
$runner->assertTrue(isset($jobDb->bindings[':user_id']), 'Job::getJobsForUser bindea user_id');

$candidate = new Candidate();
$candidate->getCandidates(10, 0);
$candRef = new ReflectionObject($candidate);
$candDbProp = $candRef->getProperty('db');
$candDbProp->setAccessible(true);
$candDb = $candDbProp->getValue($candidate);
$runner->assertTrue(strpos($candDb->sql ?? '', 'LIMIT') !== false, 'Candidate::getCandidates aplica LIMIT');
$runner->assertTrue(isset($candDb->bindings[':limit']), 'Candidate::getCandidates bindea limit');
