<?php
@ini_set('session.save_path', __DIR__ . '/../tmp');
if(!is_dir(__DIR__ . '/../tmp')){
    @mkdir(__DIR__ . '/../tmp', 0775, true);
}
require_once __DIR__ . '/../app/init.php';

class TestRunner {
    private $passed = 0;
    private $failed = 0;
    private $failures = [];

    public function assertTrue($cond, $msg){
        if($cond){
            $this->passed++;
            return;
        }
        $this->failed++;
        $this->failures[] = $msg;
    }

    public function assertEquals($a, $b, $msg){
        $this->assertTrue($a === $b, $msg);
    }

    public function report(){
        $total = $this->passed + $this->failed;
        echo "Tests: {$total}, Passed: {$this->passed}, Failed: {$this->failed}\n";
        if($this->failed){
            foreach($this->failures as $f){
                echo "- {$f}\n";
            }
            exit(1);
        }
        exit(0);
    }
}

$runner = new TestRunner();

$tests = glob(__DIR__ . '/test_*.php');
sort($tests);
foreach($tests as $file){
    require $file;
}

$runner->report();
