<?php

$id = isset($_GET['id']) ? $_GET['id'] : '';

$url_for_domain = "http://xtv.ooo:8080/live/93753738393/98556373838/{$id}.ts";

$headers_for_domain = [
    "User-Agent: OTT Navigator/1.6.7.4 (Linux;Android 11) ExoPlayerLib/2.15.1",
    "Host: xtv.ooo:8080",
    "Connection: Keep-Alive",
    "Accept-Encoding: gzip"
];

$ch_for_domain = curl_init($url_for_domain);

curl_setopt($ch_for_domain, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_for_domain, CURLOPT_HEADER, true);  // Include headers in the output
curl_setopt($ch_for_domain, CURLOPT_FOLLOWLOCATION, false);  // Disable automatic redirection
curl_setopt($ch_for_domain, CURLOPT_HTTPHEADER, $headers_for_domain);

$response_for_domain = curl_exec($ch_for_domain);
$status_code_for_domain = curl_getinfo($ch_for_domain, CURLINFO_HTTP_CODE);
curl_close($ch_for_domain);

if ($status_code_for_domain == 302 && preg_match('/Location: (.+?)\n/', $response_for_domain, $matches_for_domain)) {
    $location_url = trim($matches_for_domain[1]);
    $domain = parse_url($location_url, PHP_URL_HOST);
} else {
    http_response_code(500);
    echo "Error extracting domain: $status_code_for_domain";
    exit;
}

$url_for_ts = "http://xtv.ooo:8080/live/93753738393/98556373838/{$id}.ts";

$headers_for_ts = [
    "User-Agent: OTT Navigator/1.6.7.4 (Linux;Android 11) ExoPlayerLib/2.15.1",
    "Host: xtv.ooo:8080",
    "Connection: Keep-Alive",
    "Accept-Encoding: gzip"
];

$ch_for_ts = curl_init($url_for_ts);

curl_setopt($ch_for_ts, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch_for_ts, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
curl_setopt($ch_for_ts, CURLOPT_HTTPHEADER, $headers_for_ts);

$response_for_ts = curl_exec($ch_for_ts);
$final_url_for_ts = curl_getinfo($ch_for_ts, CURLINFO_EFFECTIVE_URL);
$status_code_for_ts = curl_getinfo($ch_for_ts, CURLINFO_HTTP_CODE);
curl_close($ch_for_ts);

if ($status_code_for_ts == 200) {
    $modified_response_text_for_ts = str_replace("/hlsr/", "http://{$domain}/hlsr/", $response_for_ts);
    echo $modified_response_text_for_ts;
} else {
    if ($status_code_for_ts == 403) {
        http_response_code(403);
        echo "Error: 403 Forbidden";
    } else {
        http_response_code(500);
        echo "Error: $status_code_for_ts";
    }
}
?>
