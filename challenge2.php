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

Leveraging HREFs found on the starting URL, collect the same information from 4 additional URLs without collecting information from the same URL multiple times.

Limitations

There should be a 5 second delay between each page request.

*/

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/vendor/autoload.php';
