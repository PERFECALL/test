<?php

// Main playlist URL
$url = "https://raw.githubusercontent.com/drmlive/fancode-live-events/refs/heads/main/fancode.m3u";

// Custom channel line (should always appear at the top)
$customLine = <<<EOD
#EXTINF:-1 tvg-logo="https://i.ibb.co.com/5gVjqSh0/Red-Abstract-Live-Stream-Free-Logo-20250309-192127-0002.png" group-title="𝗝𝗢𝗜𝗡 𝗢𝗨𝗥 𝗧𝗘𝗟𝗘𝗚𝗥𝗔𝗠", @bdixtv_official
https://bdixtv.short.gy/bdixtv_official

EOD;

// Fetch the playlist using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    curl_close($ch);
    die('cURL Error: ' . curl_error($ch));
}

curl_close($ch);

// Remove any existing instance of the custom channel if already present
$response = preg_replace('/#EXTINF:-1.*?@bdixtv_official.*?\nhttps?:\/\/.*?\.m3u8.*?\n?/s', '', $response);

// Modify each .m3u8 link to go through your stream.php proxy and add Referer
$response = preg_replace_callback('/(https?:\/\/[^\s]+?\.m3u8)(?!\|Referer)/', function($matches) {
    $originalLink = $matches[1];
    return "https://play.shihab.fun/in/stream.php?url=" . $originalLink . "|Referer=https://play.shihab.fun/";
}, $response);

// Prepend the custom channel at the very top of the playlist
$finalPlaylist = $customLine . "\n" . ltrim($response);

// Output the playlist with correct header
header("Content-Type: audio/x-mpegurl");
echo $finalPlaylist;

?>
