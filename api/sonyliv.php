<?php



// Main playlist URL

$url = "https://play.yuvraj.news/extra/sliv-live-event.php";



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

    echo 'cURL Error: ' . curl_error($ch);

} else {

    // Remove any existing instance of the custom channel if already present

    $response = preg_replace('/#EXTINF:-1.*?@bdixtv_official.*?\nhttps?:\/\/.*?\.m3u8\n?/s', '', $response);



    // Prepend the custom channel at the very top of the playlist

    $finalPlaylist = $customLine . "\n" . ltrim($response);



    // Output the playlist with correct header

    header("Content-Type: audio/x-mpegurl");

    echo $finalPlaylist;

}



curl_close($ch);

?>
