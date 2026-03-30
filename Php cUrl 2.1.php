<?php

# https://curl.se/libcurl/c/easy_setopt_options.html

ini_set('DISPLAY_ERRORS', 'On');
error_reporting(E_ALL);

/*
if ( ($index = array_search($currentCharacter,$abc)) !== false) {
    $msgSecret .= $secret[$index];
}
*/

#$ch = curl_init();

$url = "http://localhost/index.html";
$url = "https://api.open-meteo.com/v1/forecast?latitude=48.6425125&longitude=9.459418399999999&current_weather=true&hourly=temperature_2m";

# Fehlerbehandlung/error handling
#if ($ch = curl_init()) {
if ( ($ch = curl_init($url)) !== false) {

    #curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ( ($response = curl_exec($ch)) !== false) {
        $headerEnd = strpos($response, "\r\n\r\n");
        $header = substr($response, 0, $headerEnd);
        $body = substr($response, $headerEnd + 4);

        #var_dump($header,$body);

        $data = json_decode($body,true);
        $time = $data['hourly']['time'];
        $temp = $data['hourly']['temperature_2m'];
        var_dump($time, $temp);

        # TODO write time/temp data to file (fopen error handling)
    }
    else {
        var_dump('FAILED: curl_exec',$response);
    }

}
else {
    # error occurred
    $no = curl_errno();
    echo curl_strerror($no) . "\n";
    echo curl_error($ch) . "\n";
}

echo "\nDUMP\n";
var_dump(curl_getinfo($ch));





# TODO open meteo api call (guzzle), write results (json) to file, cronjob daily call, jpgraph



#curl_exec($ch);
