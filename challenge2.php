<?php

/*

Challenge 2

Collect unstructured data from www.motionpoint.com, analyze the data and store it in a file.

Specifications

Please commit you answer to a GitHub repository.

The results should be stored in a CSV file with a unique file name and column headers.

Starting at www.motionpoint.com, Extract and store content from the following HTML elements:
<title></title>
<meta name="description">

In addition to the above elements, you should also store the URL where this information was retrieved.

Create and store meta data that measures the length of the extracted contact.

Leveraging HREFs found on the starting URL, collect the same information from 4 additional URLs 
without collecting information from the same URL multiple times.

Limitations

There should be a 5 second delay between each page request.

*/

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;

$client = new Client();

// open file pointer
$fp = fopen('data/crawler.csv', 'w');

// build csv header
$csv_header = [
    'url',
    'title',
    'title_length',
    'description',
    'description_length',
    'length'
];

// write csv header
fputcsv($fp, $csv_header);

function crawlPage ( $url = '', $fp, $n = 0 ) {

    global $client, $parsed_url;
    
    // get parsed URL
    $parsed_url = parse_url($url);
    
    // exception
    if (empty($parsed_url)) return false;
    
    // go to URL
    $crawler = $client->request('GET', $url);
    
    // variables
    $data = [];
    global $data;
    
    // current url
    $data['url'] = $url;
    
    // get the title
    $crawler->filter('title')->each(function ($node) {
        global $data;
        $data['title'] = $node->text();
        $data['title_length'] = strlen($data['title']);
    });
    
    // get the description
    $crawler->filter('meta')->each(function ($node) {
        global $data;
        if ($node->attr('name') == 'description') {
            $data['description'] = $node->attr('content');
            $data['description_length'] = strlen($data['description']);
        }
    });
    
    // total length
    $data['length'] = $data['title_length'] + $data['description_length'];
    
    // write data
    fputcsv($fp, $data);
    
    // retrieve additional URLs
    global $urls, $this_url, $this_n;
    $urls = [];
    $this_url = $url;
    $this_n = $n;

    if ($n > 0) {
        $count = 0;
        // filter and iterate
        $crawler->filter('a')->each(function ($node) {
            global $this_url, $this_n, $urls, $parsed_url;
            if (count($urls) < $this_n) {
                $href = $node->attr('href');
                $parsed_href = parse_url($href);
                // exclude empty
                if (!empty($parsed_href)) {
                    // exclude non http(s) schemes
                    if ( ! (isset($parsed_href['scheme']) && !strstr($parsed_href['scheme'], 'http')) ) {
                        // normalize scheme
                        if ( ! isset($parsed_href['scheme']) ) {
                            $parsed_href['scheme'] = $parsed_url['scheme'];
                        }
                        // normalize host
                        if ( ! isset($parsed_href['host']) ) {
                            $parsed_href['host'] = $parsed_url['host'];
                        }
                        
                        // re-build href now
                        $href_final = $parsed_href['scheme'] . '://' . $parsed_href['host'];
                        
                        // add path if exist
                        if (isset($parsed_href['path']) ) {
                            $href_final .= '/'. ltrim($parsed_href['path'], '\/');
                        }
                        
                        // add query if exist
                        if (isset($parsed_href['query']) ) {
                            $href_final .= '?'. ltrim($parsed_href['query'], '\?');
                        } 
                        
                        // add to array if not seen already
                        if ($href_final != $this_url & ! in_array($href_final, $urls)) {
                            $urls[] = $href_final;
                        }
                    }
    
                }
            }
        }); 
        
    }
    
    return $urls;    

}

// crawl initial URL
$urls = crawlPage('https://www.motionpoint.com/', $fp, 4);

// crawl additional set
foreach($urls as $url) {
    sleep(5); // sleep for 5 seconds
    crawlPage($url, $fp);
}

// close fp
fclose($fp);
