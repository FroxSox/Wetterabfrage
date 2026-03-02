<?php
    declare(strict_types=1);

    # https://curl.se/libcurl/c/easy_setopt_options.html

    const LOG_FILE = 'temp_results.txt';
    const LOG_FILE_SEPARATOR = "\n\n";

    const ZERO_KELVIN = -273;

    ini_set('DISPLAY_ERRORS', 'On');
    error_reporting(E_ALL);

    $url = "http://localhost/index.html";

    # https://github.com/WZBSocialScienceCenter/plz_geocoord
    $url = "http://api.open-meteo.com/v1/forecast?latitude=48.6425125&longitude=9.459418399999999&current_weather=true&hourly=temperature_2m";



    /*

    # process control demo - call external program (curl)

    #noModCurlCall($url);
    $exitCodeCurl = noModCurlCallImproved($url); # 0 on success

    # https://curl.se/libcurl/c/libcurl-errors.html
    switch ($exitCodeCurl) {
        case 1:
                    echo "The URL you passed to libcurl used a protocol that this libcurl does not support." .
                         "The support might be a compile-time option that you did not use, it can be a misspelled" .
                         "protocol string or just a protocol libcurl has no code for.";
                    break;
        default:    echo "whatever error :(";
                    break;
    }

    */


    /*
    # benutzer@benutzer-VirtualBox:/var/www/playground/api$ php -i|grep allow_url_fopen
    # allow_url_fopen => On => On

    $handle =fopen($url, "r");
    if ($handle) {
        while (!feof($handle)) {
            echo fgets($handle);
        }
        fclose($handle);
    }
    */


    # Fehlerbehandlung/error handling
    #if ($ch = curl_init()) {
    if ( ($ch = curl_init($url)) !== false) {

        #curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ( ($response = curl_exec($ch)) !== false) {
            $headerEnd = strpos($response, "\r\n\r\n");
            $header = substr($response, 0, $headerEnd);
            $body = substr($response, $headerEnd + 4); # 4 -> 4 Bytes for "\r\n\r\n"

            #var_dump($header,$body);

            $data = json_decode($body,true);
            $time = $data['hourly']['time'];            # 168 elements (temp 24  h * 7 days)
            $temp = $data['hourly']['temperature_2m'];

            $timeTemp = array_combine($time, $temp);

            # https://de.wikipedia.org/wiki/Serialisierung
            $timeTemp = serialize($timeTemp); # PHP array is converted to a string which we can store (persist), so the data doesn't get lost after program termination

            #var_dump($timeTemp);

            # https://www.php.net/manual/en/ref.filesystem.php
            # DIRECTORY_SEPARATOR ("\\" on Win, '/' Linux,...)

            if ( (file_put_contents(LOG_FILE,$timeTemp  . LOG_FILE_SEPARATOR,FILE_APPEND)) === false) {
                echo "Writing to file FAILED!\n";
            }



            # find MAX and MIN temp values
            $timeTemp = unserialize($timeTemp);

            # https://www.php.net/manual/en/function.array-search.php -> Searches the array for a given value and returns the first corresponding key if successful
            # problem: we could have the same MAX temp on several dates

            # fast
            # callback is an anonymous function
            #$daysMaxTemp = (array_filter([9=>10, 1=>1, 4=>10], function($value) { return $value === max([9=>10, 1=>10, 4=>10]); }));
            $daysMaxTemp = (array_filter($timeTemp, function($value) use ($timeTemp) { return $value === max($timeTemp); }));
            var_dump($daysMaxTemp);

            $max = ZERO_KELVIN; # min temp possible
            $foundDaysMaxTemp = [];

            # slow - php user land function that mimics already defined php functions -> (array_filter($timeTemp, function($value) use ($timeTemp) { return $value === max($timeTemp); }));
            # max() functionality
            foreach ($timeTemp as $time => $temp) {
                if ($temp > $max) {
                    $max = $temp;
                }
            }

            # find array keys for max values
            $foundDaysMaxTemp = array_keys($timeTemp, $max);

            var_dump(__LINE__,$max,$foundDaysMaxTemp);

            # TODO  -> find the MIN temp, show both and the data
            $daysMinTemp = (array_filter($timeTemp, function($value) use ($timeTemp) { return $value === min($timeTemp); }));
            var_dump($daysMinTemp);

            $min = abs(ZERO_KELVIN); # max temp possible
            $foundDaysMinTemp = [];

            # min() functionality
            foreach ($timeTemp as $time => $temp) {
                if ($temp < $min) {
                    $min = $temp;
                }
            }

            # find array keys for min values
            $foundDaysMinTemp = array_keys($timeTemp, $min);

            var_dump(__LINE__,$min,$foundDaysMinTemp);
        }
        else {
            var_dump('FAILED: curl_exec',$response);
        }

    }
    else {
        # error occurred
        $no = curl_errno($ch);
        echo curl_strerror($no) . "\n";
        echo curl_error($ch) . "\n";
    }

    #echo "\nDUMP\n";
    #var_dump(curl_getinfo($ch));



function array_average(array $temp): ?float {
    // Filter out non-numeric values
    $filtered = array_filter($temp, 'is_numeric');

    // If no valid numeric values, return null
    if (count($filtered) === 0) {
        return null;
    }

    // Calculate average
    return  array_sum($filtered) / count($filtered);
}
    var_dump($temp);



/**
 * This function shows how to call an external program without the php (binding) module for curl.
 * This is a phpdoc comment :)
 *
 * @param string $connection a URL or something to connect to
 * @return void
 */
function noModCurlCall(string $connection): void {
    # compare -> popen(), shell_exec() ... https://www.php.net/manual/de/ref.filesystem.php
    $output = `/usr/bin/curl "$connection"`; # output only no return code

    echo $output;
}

/**
 * Returns curl error code like curl_errno()
 * @param string $connection
 * @return int
 */
function noModCurlCallImproved(string $connection): int {
    $output = [];
    $returnCode = 0;

    # -s -> silent suppress unwanted error message "curl: (1) Protocol "htt" not supported or disabled in libcurl"
    $result = exec("/usr/bin/curl -s '$connection'", $output,$returnCode);

    /*
    # In PHP, arguments are passed by value by default — but you can explicitly pass by reference (&parameter).
    $colors = ["red", "green", "blue", "yellow"];
    sort($colors);
    var_dump($colors); # sorted without assignment. $colors = sort($colors); # WRONG!!!!!
    */

    # var_dump($result,$output,$returnCode);
    return $returnCode;
}


    # TODO open meteo api call (guzzle), write results (json) to file, cronjob daily call, jpgraph



    #curl_exec($ch);