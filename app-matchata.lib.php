<?php

function logme($promptForLog, $responseText) {

   // Prepare the data to log
   $timestamp = date('Y-m-d H:i:s');
   $userIP = $_SERVER['HTTP_CF_CONNECTING_IP'];
   $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
   $url = $promptForLog; // Assuming the article URL is stored in the $prompt variable
   $userAgent = $_SERVER['HTTP_USER_AGENT'];
   $country = $_SERVER['HTTP_CF_IPCOUNTRY'];
   $referrer = $_SERVER['HTTP_REFERER'];
   $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
   $responseTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
   // $error = ''; // Replace with the appropriate error message if an error occurs
   $status = "false";

   // print_R($responseText);

   foreach($responseText as $key=>$val) { $body .= trim($val)."\t";} 

   // print_r($body);

   // Log the data in a CSV file
   $logfile = fopen('logement-duolingo.txt', 'a');
   if (fputcsv($logfile, [$timestamp, $country, $userIP, $proto, $language, $referrer, $promptForLog, $userAgent, $responseTime, trim($body)])) $status = "wow";
   fclose($logfile);

}

function cacheURLlocally($responseText, $shortUrlId) {

   $fp = fopen(CACHE_FILE_PREFIX . $shortUrlId, 'w');
   fwrite($fp, $responseText);
   fclose($fp);

   return 1;

}  

function generateShortUrl($passLenth=DEFAULT_SHORT_URL_LENGTH, $numbersOrLetters = "all") {
   
   $numbersOrLettersToChoose = array(

      "all" => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890",
      "numbers" => "1234567890",

   );

   $pass = array(); //remember to declare $pass as an array
   $alphaLength = strlen($numbersOrLettersToChoose[$numbersOrLetters]) - 1; //put the length -1 in cache
   for ($i = 0; $i < $passLenth; $i++) {
       $n = rand(0, $alphaLength);
       $pass[] = $numbersOrLettersToChoose[$numbersOrLetters][$n];
   }
   return implode($pass); //turn the array into a string
}


function curlPOST($postData, $url='', $post=1, $debug="") {

   if (empty($url)) $url = PDF_GERNERATE_URL;

   $ch = curl_init();

   curl_setopt($ch, CURLOPT_URL,$url);
   curl_setopt($ch, CURLOPT_POST, $post);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); 
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   
   $server_output['html'] = curl_exec($ch);
   $server_output['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

   // print_r($server_output);
   if (!empty($debug)) print curl_error($ch);
   
   return $server_output;

}


function trackGames($data) {

   $data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
   $data['country'] = $_SERVER['HTTP_CF_IPCOUNTRY'];
   $data['referrer'] = $_SERVER['HTTP_REFERER'];
   $data['language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

   curlPOST($data, SPREADSHEETS_API);

}