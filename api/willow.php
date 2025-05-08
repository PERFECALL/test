<?php
// Set header so browser treats it like a playlist file
header('Content-Type: audio/x-mpegurl');
header('Content-Disposition: inline; filename="playlist.m3u"');

// Fetch JSON from the URL using cURL
$url = "https://raw.githubusercontent.com/drmlive/willow-live-events/refs/heads/main/willow.json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo '#EXTM3U';
    echo "\n# Error fetching data: " . curl_error($ch);
} else {
    $data = json_decode($response, true);

    // M3U header
    echo "#EXTM3U\n\n";

    // Static first entry (general promo stream)
    echo "#EXTINF:-1 tvg-logo=\"https://i.ibb.co.com/5gVjqSh0/Red-Abstract-Live-Stream-Free-Logo-20250309-192127-0002.png\" group-title=\"𝗝𝗢𝗜𝗡 𝗧𝗘𝗟𝗘𝗚𝗥𝗔𝗠\", @bdixtv_official\n";
    echo "https://bdixtv.short.gy/bdixtv_official\n\n";

    // Loop through matches and print entries
    foreach ($data['matches'] as $match) {
        $stream_url = $match['playback_data']['urls'][0]['url'];
        $license_key = $match['playback_data']['keys'][0] ?? '';
        $title = $match['title'];
        $logo = $match['cover'];
        $id = $match['titleId'];

        echo "#EXTINF:-1 tvg-id=\"$id\" tvg-logo=\"$logo\" group-title=\"Live Matches\", $title\n";
        echo "#KODIPROP:inputstream.adaptive.license_type=clearkey\n";
        echo "#KODIPROP:inputstream.adaptive.license_key=$license_key\n";
        echo "$stream_url\n\n";
    }
}

curl_close($ch);
?>
