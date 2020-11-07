<?php
/*
DEPRECATED!!!
This is a test PHP script to convert a Google Font into a sprite strip... it's not needed any more
*/

//First, set the default properties
$fontfamily = "Architects Daughter";
$charmap = " !".'"'."#$%&'()*+-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ{\}^_`abcdefghijklmnopqrstuvwxyz{|}~";
$size = 30;
$padding = 1.25;

//If the GETS are not empty then change the properties
if (!empty(filter_input(INPUT_GET, "font")))
{
    $fontfamily = filter_input(INPUT_GET, "font", FILTER_SANITIZE_STRING);
}
if (!empty(filter_input(INPUT_GET, "map")))
{
    $charmap = filter_input(INPUT_GET, "map");
}
if (!empty(filter_input(INPUT_GET, "size", FILTER_SANITIZE_NUMBER_INT)))
{
    $size = filter_input(INPUT_GET, "size", FILTER_SANITIZE_NUMBER_INT);
}
if (!empty(filter_input(INPUT_GET, "padding", FILTER_SANITIZE_NUMBER_FLOAT)))
{
    $padding = filter_input(INPUT_GET, "padding", FILTER_SANITIZE_NUMBER_FLOAT);
}

//Clears the given directory of all files and removes it
function deletedirectory($path)
{
    $files = glob($path . '*', GLOB_MARK);
    foreach ($files as $file)
    {
        unlink($file);
    }
    rmdir($path);
}

//Recursive binary searches the "family" attribute of the given array 
function binarysearch($array, $start, $end, $value)
{    
    //Kill if there there isn't anything to search
    if ($end < $start) { return -1; }
    
    //Get the middle of the array
    $middleindex = ceil($start + (($end-$start)/2));
    //The family that is at the middle
    $currentfamily = $array[$middleindex]["family"];
    //The difference between the family and the value
    $difference = strcmp($value, $currentfamily);
 
    logadd("Searching: i:$middleindex s:$start e:$end c:$currentfamily f:$value d:$difference");
    
    //If there's no difference then return the index at the middle
    if ($difference == 0) { return $middleindex; }
    //If there is a difference then do a binary search on the left or right half of the array
    else if ($difference < 0) { return binarysearch ($array, $start, $middleindex - 1, $value); }
    else { return binarysearch ($array, $middleindex + 1, $end, $value); }
}

$log = "";
function logadd($string)
{
    global $log;
    $log .= " <br> ".$string;
}

// Source: http://php.net/manual/en/function.imagettfbbox.php#75407
function imagettfbboxextended($size, $angle, $fontfile, $text) {
    /*this function extends imagettfbbox and includes within the returned array
    the actual text width and height as well as the x and y coordinates the
    text should be drawn from to render correctly.  This currently only works
    for an angle of zero and corrects the issue of hanging letters e.g. jpqg*/
    $bbox = imagettfbbox($size, $angle, $fontfile, $text);

    //calculate x baseline
    if($bbox[0] >= -1) {
        $bbox['x'] = abs($bbox[0] + 1) * -1;
    } else {
        //$bbox['x'] = 0;
        $bbox['x'] = abs($bbox[0] + 2);
    }

    //calculate actual text width
    $bbox['width'] = abs($bbox[2] - $bbox[0]);
    if($bbox[0] < -1) {
        $bbox['width'] = abs($bbox[2]) + abs($bbox[0]) - 1;
    }

    //calculate y baseline
    $bbox['y'] = abs($bbox[5] + 1);

    //calculate actual text height
    $bbox['height'] = abs($bbox[7]) - abs($bbox[1]);
    if($bbox[3] > 0) {
        $bbox['height'] = abs($bbox[7] - $bbox[1]) - 1;
    }

    return $bbox;
}

//Get the font from Google Fonts
//Setup cURL request
$googleapikey = "X";
$googleapiurl = "https://www.googleapis.com/webfonts/v1/webfonts?key=$googleapikey&sort=alpha";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $googleapiurl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
$data = curl_exec($curl);
curl_close($curl);

//Get the items from the data
$items = json_decode($data,true)["items"];

//Find the item from the name
$itemcount = sizeof($items); //Number of fonts
$fontindex = binarysearch($items, 0, $itemcount, $fontfamily); //Get font index
if ($fontindex < 0) { die("Font not found"); } //If a font hasn't been found

//Get the url
$fonturl = $items[$fontindex]["files"]["regular"];
$font = "tempfont.ttf"; //The font to use
//Copy the font from the url to the local directory
copy($fonturl, $font);

//Find out the height of the font with the given size
$box = imagettfbboxextended($size, 0, $font, $charmap);
$height = $box['height'];
$yposition = $box['y'];
$xposition = $boc['x'];
$width = ceil($height*$padding);

//Create an image
$image = imagecreatetruecolor($width * strlen($charmap), $height * $padding);

//Enable background alpha
imagesavealpha($image, true);
imagealphablending($image, false);

//Clear background to alpha color
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparent);

//Create a color for the text to use
$textcolor = imagecolorallocate($image, 255,255, 255);
$red = imagecolorallocate($image, 255,0,0);
$green = imagecolorallocate($image, 0, 255, 0);

//Loop through the character map to print out each character
for($i=0; $i<strlen($charmap); $i++)
{
    $left = ($i * $width);
    
    /*
    // Test to see if all characters are staying within their bounds
    if ($i % 2 == 0)
    {
        imagefilledrectangle($image, $left, 0, $left + ($width*$padding), $height * $padding, $red);
    }
    */
    
    //Get the individual box for the current character
    $char = $charmap[$i];
    $charbbox = imagettfbboxextended($size, 0, $font, $char);
    $charx = $charbbox['x'];
    $charw = $charbbox['width'];
    
    //Draw the current character
    imagettftext($image, $size, 0, $left + ($width / 2) - ($charw/2) + $charx, $yposition * $padding, $textcolor, $font, $char);
}

/* ------- Image finished ------- */

$success = imagepng($image, "file.png");
header("Content-type: image/png");
$size = filesize($image,"file.png");
header("Content-length: $size");
readfile("file.png");

//Destroy resources
imagedestroy( $image );
unlink("file.png");
unlink($font);

//Done!
