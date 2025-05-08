<?php
// Generate random token
function generateRandomToken($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $token;
}

// Generate random device model
function generateRandomDeviceModel() {
    $brands = ['Samsung', 'LG', 'Sony', 'Xiaomi', 'Huawei', 'Oppo', 'Vivo'];
    $models = ['A5', 'S21', 'Note', 'P30', 'Redmi', 'Mate', 'F1'];
    return $brands[rand(0, count($brands)-1)] . $models[rand(0, count($models)-1)] . rand(10, 99);
}

// Get the 'id' parameter from the URL
$id = isset($_GET['id']) ? $_GET['id'] : '';
$streamType = isset($_GET['type']) ? $_GET['type'] : 'm3u8';

// Generate unique token for this session
$uniqueToken = generateRandomToken();

// Determine the file extension and set headers accordingly
$allowedExtensions = ['m3u8', 'ts', 'mpegts'];
if (!in_array($streamType, $allowedExtensions)) {
    http_response_code(400);
    echo "Invalid stream type";
    exit;
}

// Get the file extension from the URL
$fileExtension = $streamType;
$filename = basename($_SERVER['REQUEST_URI']);
$filePath = "streams/" . $filename;

// Handle different stream types
switch ($fileExtension) {
    case 'm3u8':
        // Process HLS manifest file
        $url_for_domain = "http://opplex.tv:8080/live/93753738393/98556373838/$id.m3u8";
        $deviceModel = generateRandomDeviceModel();
        $headers_for_domain = [
            "User-Agent: OTT Navigator/1.6.7.4 (Linux;Android 11; " . $deviceModel . ") ExoPlayerLib/2.15.1",
            "Host: opplex.tv",
            "Connection: Keep-Alive",
            "Accept-Encoding: gzip",
            "X-Unique-Token: " . $uniqueToken
        ];

        $ch_for_domain = curl_init($url_for_domain);
        curl_setopt($ch_for_domain, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_for_domain, CURLOPT_HEADER, true);
        curl_setopt($ch_for_domain, CURLOPT_FOLLOWLOCATION, false);
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

        $url_for_m3u8 = "http://opplex.tv:8080/live/93753738393/98556373838/$id.m3u8";
        $headers_for_m3u8 = [
            "User-Agent: OTT Navigator/1.6.7.4 (Linux;Android 11; " . $deviceModel . ") ExoPlayerLib/2.15.1",
            "Host: opplex.tv",
            "Connection: Keep-Alive",
            "Accept-Encoding: gzip",
            "X-Unique-Token: " . $uniqueToken,
            "X-Request-ID: " . uniqid(),
            "X-Device-Model: " . $deviceModel
        ];

        $ch_for_m3u8 = curl_init($url_for_m3u8);
        curl_setopt($ch_for_m3u8, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_for_m3u8, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_for_m3u8, CURLOPT_HTTPHEADER, $headers_for_m3u8);
        $response_for_m3u8 = curl_exec($ch_for_m3u8);
        $status_code_for_m3u8 = curl_getinfo($ch_for_m3u8, CURLINFO_HTTP_CODE);
        curl_close($ch_for_m3u8);

        if ($status_code_for_m3u8 == 200) {
            $modified_response_text_for_m3u8 = str_replace("/hlsr/", "http://{$domain}:8080/hlsr/", $response_for_m3u8);
            $modified_response_text_for_m3u8 = str_replace("/hls/", "http://{$domain}:8080/hls/", $modified_response_text_for_m3u8);
            header("Content-Type: text/plain");
            echo $modified_response_text_for_m3u8;
        } else {
            if ($status_code_for_m3u8 == 403) {
                http_response_code(403);
                echo "Error: 403 Forbidden";
            } else {
                http_response_code(500);
                echo "Error: $status_code_for_m3u8";
            }
        }
        break;

    case 'ts':
    case 'mpegts':
        // Handle transport stream files
        if (file_exists($filePath)) {
            header("Content-Type: video/MP2T");
            readfile($filePath);
        } else {
            // Proxy request to origin server
            $url_for_stream = "http://opplex.tv:8080/live/93753738393/98556373838/$id.{$fileExtension}";
            $ch_for_stream = curl_init($url_for_stream);
            curl_setopt($ch_for_stream, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_for_stream, CURLOPT_FOLLOWLOCATION, true);
            $response_for_stream = curl_exec($ch_for_stream);
            $status_code_for_stream = curl_getinfo($ch_for_stream, CURLINFO_HTTP_CODE);
            curl_close($ch_for_stream);

            if ($status_code_for_stream == 200) {
                header("Content-Type: video/MP2T");
                echo $response_for_stream;
            } else {
                http_response_code($status_code_for_stream);
                echo "Stream error: $status_code_for_stream";
            }
        }
        break;

    default:
        http_response_code(400);
        echo "Invalid file extension";
        break;
}
?>
