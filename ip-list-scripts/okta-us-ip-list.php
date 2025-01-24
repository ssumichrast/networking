<?php

# Configuration
$cache_time = 24 * 60; // Cache time in seconds
$cache_file = "okta-ip-list.txt"; // Cache file name
$source_url = "https://s3.amazonaws.com/okta-ip-ranges/ip_ranges.json"; // JSON source URL

# Check if the cache file needs updating
if (!file_exists($cache_file) || (time() - filemtime($cache_file) >= $cache_time)) {
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
        $result = json_decode(curl_exec($ch), true);

        // Filter keys matching 'us_cell_*' and collect IP ranges
        $cacheOutput = [];
        foreach ($result as $key => $value) {
            if (strpos($key, 'us_cell_') === 0) {
                $cacheOutput = array_merge($cacheOutput, $value['ip_ranges']);
            }
        }

        // Write the IP ranges to the cache file
        file_put_contents($cache_file, implode("\n", $cacheOutput));
    } catch (Exception $e) {
        // Handle errors (optional logging can be added here)
    }
}
?>
