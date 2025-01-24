<?php

# Configuration
$cache_time = 24 * 60; // Cache time in seconds
$cache_file = "zscaler-pva-ip-list.txt"; // Cache file name
$source_url = "https://config.zscaler.com/api/private.zscaler.com/zpa/json"; // JSON file source

# Check if the cache file needs updating
if ((!file_exists($cache_file)) || (time() - filemtime($cache_file) >= $cache_time )) {
	updateCache($source_url, $cache_file);
}

# Output the cache file if it exists
if (file_exists($cache_file)) {
	readfile($cache_file);
}

function updateCache($source_url, $cache_file) {
    try {
        // Fetch and decode JSON
        $ch = curl_init($source_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = json_decode(curl_exec($ch), true)['content'] ?? [];

        // Filter and store IPs
        $cacheOutput = array_reduce($result, function ($carry, $entry) {
            foreach ($entry['IPs'] as $IP) {
                if (!str_contains($IP, ":")) { // Exclude IPv6 addresses
                    $carry[] = $IP;
                }
            }
            return $carry;
        }, []);

        file_put_contents($cache_file, implode("\r\n", $cacheOutput));
    } catch (Exception $e) {
        exit;
    }
}
?>
