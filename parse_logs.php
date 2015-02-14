<?php
if (!isset($argv[1])) {
    throw new Exception('File to parse must be passed as an argument');
}

if (!file_exists($argv[1])) {
    throw new Exception('File does not exist: ' . $argv[1]);
}

$results = [
    'requestAmounts' => [
        'total' => 0,
        'success' => 0,
        'fail' => 0,
    ],
    'popularUrls' => [],
    'popularReferrers' => [],
    'popularUserAgents' => [],
];

$regex = '/^([^ ]+) ([^ ]+) ([^ ]+) \[([^\]]+)\] "([^ ]+) ([^ ]+) ([^ ]+)" ([0-9\-]+) ([0-9\-]+) "([^ ]+)" "([^\"]+)"/';
$lines = explode("\n", file_get_contents($argv[1]));
$partsMap = [ // Maps preg_match_all output to human friendly indexes
    'line',
    'ip',
    'identity',
    'username',
    'time',
    'method',
    'url',
    'protocol',
    'status',
    'length',
    'referrer',
    'user-agent',
];

foreach ($lines as $line) {
    $parts = [];
    preg_match_all($regex, $line, $parts);

    if (count($parts) !== 12) {
        throw new Exception("Badly formatted log line: $line");
    }

    // Convert indexes to human friendly format
    foreach ($partsMap as $index => $humanIndex) {
        if (!isset($parts[$index][0])) {
            throw new Exception("Badly formatted log line: $line");
        }
        $parts[$humanIndex] = $parts[$index][0];
        unset($parts[$index]);
    }

    // Request amounts for total, successful and failed requests
    $results['requestAmounts']['total']++;
    if ((int)$parts['status'] >= 500 && (int)$parts['status'] <= 599) { // 5xx status codes are considered failures
        $results['requestAmounts']['fail']++;
    } else {
        $results['requestAmounts']['success']++;
    }

    // Popular URLs
    if (!isset($results['popularUrls'][$parts['url']])) {
        $results['popularUrls'][$parts['url']] = 1;
    } else {
        $results['popularUrls'][$parts['url']]++;
    }

    // Popular referrers
    if (!isset($results['popularReferrers'][$parts['referrer']])) {
        $results['popularReferrers'][$parts['referrer']] = 1;
    } else {
        $results['popularReferrers'][$parts['referrer']]++;
    }

    // Popular user agents
    if (!isset($results['popularUserAgents'][$parts['user-agent']])) {
        $results['popularUserAgents'][$parts['user-agent']] = 1;
    } else {
        $results['popularUserAgents'][$parts['user-agent']]++;
    }
}

arsort($results['popularUrls'], SORT_NUMERIC);
arsort($results['popularReferrers'], SORT_NUMERIC);
arsort($results['popularUserAgents'], SORT_NUMERIC);

print_r($results);