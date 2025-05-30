<?php
// Status php proxy to send status from Mikrotik to alerta
// Matt Perkins May 2025 

$url = 'http://192.168.0.2:8080/api/';
$minimum_rtt = 250000 ; //250ms 
$lockpath="/dev/shm"; 

// API key header
$headers = [
    'Authorization: Key your-api-key',
    'Content-Type: application/json',
];


// Load infomation from mikrotik.
$path_info  = $_SERVER["PATH_INFO"];
//$path_info = "status.php./bbchiro.mk/ZT/0/2549"; 
$path_info = preg_replace('#[^a-zA-Z0-9_\-/.]#', '', $path_info);
list($xx,$mikrotik_host,$link_type,$link_loss,$link_rtt) = explode ("/",$path_info);

echo "$mikrotik_host  $link_type  $link_loss  $link_rtt "; 


// If we get a bad result either high rtt 
$ms_link_rtt = $link_rtt/1000;
if($link_rtt > $minimum_rtt){ 

$fp = fopen("$lockpath/$mikrotik_host",'c');

if(!$fp){
	die("cold not open lock file");
}

fclose($fp); 

// Build the JSON payload as an array
$data = [
    "attributes" => [
        "region" => "AU"
    ],
    "correlate" => [
        "HttpServerError",
        "HttpServerOK"
    ],
    "environment" => "Production",
    "event" => "LATENCY",
    "group" => "RouterDB",
    "origin" => "$mikrotik_host/$link_type",
    "resource" => "$mikrotik_host/$link_type",
    "service" => [
        "RouterDB"
    ],
    "severity" => "minor",
    "tags" => ["mikrotik_script"],
    "text" => "Hight RTT time $ms_link_rtt ms",
    "type" => "",
    "value" => "$mikrotik_host/$link_type"
];


// Convert the array to JSON
$jsonPayload = json_encode($data);

// Setup the cURL request
$ch = curl_init("$url/alert");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);

// Execute and check for errors
$response = curl_exec($ch);
if ($response === false) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo 'Alarm Response: ' . $response;
}

curl_close($ch);

}else{
// Clear latency message
	if (is_file("$lockpath/$mikrotik_host")) {

// Build the JSON payload as an array
$data = [
    "attributes" => [
        "region" => "AU"
    ],
    "correlate" => [
        "HttpServerError",
        "HttpServerOK"
    ],
    "environment" => "Production",
    "event" => "LATENCY",
    "group" => "RouterDB",
    "origin" => "$mikrotik_host/$link_type",
    "resource" => "$mikrotik_host/$link_type",
    "service" => [
        "RouterDB"
    ],
    "severity" => "cleared",
    "tags" => ["mikrotik_script"],
    "text" => "Hight RTT time $ms_link_rtt ms",
    "type" => "",
    "value" => "$mikrotik_host/$link_type"
];


// Convert the array to JSON
$jsonPayload = json_encode($data);

// Setup the cURL request
$ch = curl_init("$url/alert");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);

// Execute and check for errors
$response = curl_exec($ch);
if ($response === false) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo 'Alarm Response: ' . $response;
}

unlink("$lockpath/$mikrotik_host"); 

} 






}










if($link_loss == 0 ){


// JSON payload
$data = [
    'origin' => "$mikrotik_host/$link_type",
    'timeout' => 90,
    'tags' => ['mikrotik_script'],
    'attributes' => [
        'environment' => 'Production',
        'service' => ['RouterDB'],
        'group' => 'Network',
        'severity' => 'indeterminate',
    ],
];

// Encode JSON payload
$jsonData = json_encode($data);


// Initialise cURL
$ch = curl_init("$url/heartbeat");

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 seconds connect timeout

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
   echo 'Heartbeat Response: ' . $response;
}

// Close cURL handle
curl_close($ch);

}


