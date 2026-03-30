<?php
    //<editor-fold desc="Region">
    $url = "http://localhost/"; //region Ip der internetseite
    $savepath = "file.html"; //endregion Speicherort des Downloads
    $fp = fopen($savepath, 'w');

        if(!$fp) {
            die("Couldn't open file.");
        }
     $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_FILE, $fp);
     curl_setopt($ch, CURLOPT_HEADER, 0);

        if (!curl_exec($ch)) {
            die("Error opening website.");
     }
     curl_close($ch);
     fclose($fp);

    //</editor-fold>