<?php
$base = $argv[1] ?? 'http://localhost/pegaTinder';
$path = $argv[2] ?? '/empleos';
$concurrency = (int)($argv[3] ?? 10);
$requests = (int)($argv[4] ?? 50);

if($concurrency < 1) $concurrency = 1;
if($requests < 1) $requests = 1;

$url = rtrim($base, '/') . $path;

function makeHandle($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    return $ch;
}

$mh = curl_multi_init();
$inflight = 0;
$done = 0;
$ok = 0;
$fail = 0;
$times = [];

$queue = [];
for($i=0;$i<$requests;$i++){
    $queue[] = $url;
}

while($done < $requests){
    while($inflight < $concurrency && count($queue) > 0){
        $u = array_shift($queue);
        $ch = makeHandle($u);
        curl_setopt($ch, CURLOPT_PRIVATE, (string)microtime(true));
        curl_multi_add_handle($mh, $ch);
        $inflight++;
    }

    do {
        $status = curl_multi_exec($mh, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM);

    while($info = curl_multi_info_read($mh)){
        $ch = $info['handle'];
        $start = (float)curl_getinfo($ch, CURLINFO_PRIVATE);
        $elapsed = (microtime(true) - $start) * 1000.0;
        $times[] = $elapsed;

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($code >= 200 && $code < 400){
            $ok++;
        } else {
            $fail++;
        }

        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        $inflight--;
        $done++;
    }

    if($active){
        curl_multi_select($mh, 0.2);
    }
}

curl_multi_close($mh);

sort($times);
$count = count($times);
$p50 = $times[(int)floor($count * 0.50)] ?? 0;
$p95 = $times[(int)floor($count * 0.95)] ?? 0;
$avg = $count ? array_sum($times) / $count : 0;

echo "URL: {$url}\n";
echo "Requests: {$requests}, Concurrency: {$concurrency}\n";
echo "OK: {$ok}, Fail: {$fail}\n";
echo "Avg ms: " . (int)$avg . ", P50 ms: " . (int)$p50 . ", P95 ms: " . (int)$p95 . "\n";

