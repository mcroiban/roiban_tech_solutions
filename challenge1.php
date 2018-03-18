<?php

/*

Challenge 1

Leverage the Random User Generator API (https://randomuser.me) to create user data which you will sort and store into separate files.

Specifications

Please commit your answer to a GitHub repository.

Retrieve data for 100 random users from only the United States and Germany, and the United Kingdom. 
Your code should retrieve this data from a single API request. The response format should be in JSON.

Output the response into a file that should be committed with the rest of your code.

Using January 1, 1990 as a midpoint, divide and sort the 100 responses into two csv files based on the users’ birthdates.

Create one CSV file that contains all American males born after January 1, 1980.

The output csv files should have descriptive file names and contain column headers.

*/

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

// Create a client
$client = new GuzzleHttp\Client();

// Send a GET request to API
$response = $client->request('GET', 'https://randomuser.me/api', [
    'query' => [
        'results' => '100',
        'nat' => 'us,de,gb'
    ]
]);

// stop here if invalid response
if ($response->getStatusCode() != 200) exit(); 

// decode response to array
$data = json_decode($response->getBody(), true);
$users = $data['results'];

// Using January 1, 1990 as a midpoint, divide and sort the 100 responses into two csv files based on the users’ birthdates.

// open file pointers
$fp1 = fopen('data/dob_before_010190.csv', 'w');
$fp2 = fopen('data/dob_after_010190.csv', 'w');
$fp3 = fopen('data/dob_after_010180.csv', 'w');



// set midpoint
$midpoint = new DateTime('1990-01-01');

// parse data
$csv_header = [];

foreach($users[0] as $k => $v) {
    $csv_header[] = $k;
    if (is_array($v)) {
        foreach($v as $k2 => $v2) {
            $csv_header[] = $k . '_' . $k2;
        }
    }
}

fputcsv($fp1, $csv_header);
fputcsv($fp2, $csv_header);
fputcsv($fp3, $csv_header);

// parse data
foreach ($users as $user) {
    // flatten user data
    $row = [];
    foreach($user as $v) {
        if (is_array($v)) {
            foreach ($v as $v2) {
                $row[] = $v2;
            }
        } else {
            $row[] = $v;
        }
    }
    
    $dob = new DateTime($user['dob']);
    // split
    if($dob >= $midpoint) {
        fputcsv($fp2, $row);
    } else {
        fputcsv($fp1, $row);
    }
}

// close fp
fclose($fp1);
fclose($fp2);
fclose($fp3);