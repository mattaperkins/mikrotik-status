<?php
// Status php proxy to send status from Mikrotik to alerta
// Matt Perkins May 2025 

// URL of Alerta Server
// $url = 'http://192.168.0.2:8080/api/heartbeat';
$minimum_rtt = 100000 ; 

// API key header
$headers = [
    'Authorization: Key al-spec-key-1',
    'Content-Type: application/json',
];


// Load infomation from mikrotik.
$path_info  = $_SERVER["PATH_INFO"];
//$path_info = "status.php./bbchiro.mk/ZT/0/2549"; 
$path_info = preg_replace('#[^a-zA-Z0-9_\-/.]#', '', $path_info);
list($xx,$mikrotik_host,$link_type,$link_loss,$link_rtt) = explode ("/",$path_info);

echo "$mikrotik_host  $link_type  $link_loss  $link_rtt "; 

// If we get a bad result either high rtt or not zero loss
if($link_loss != 0 | $link_rtt < $minimum_rtt ){

	//echo "got here"; 

// JSON payload
$data = [
    'origin' => "$mikrotik_host/$link_type",
    'timeout' => 90,
    'tags' => ['mikrotik_script'],
    'attributes' => [
        'environment' => 'Production',
        'service' => ['RouterDB'],
        'group' => 'Network',
        'severity' => 'minor',
    ],
];

// Encode JSON payload
$jsonData = json_encode($data);


// Initialise cURL
$ch = curl_init($url);

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
   //print_r($data);
}

// Close cURL handle
curl_close($ch);




}
