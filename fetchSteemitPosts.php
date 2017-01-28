<?php

/*
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
(of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org>
*/

/**
 *  Fetches a single category from Steemit Frontend
 *  @requirements PHP Curl
 *  @version 1.0
 *  @comment 
 *  !! A WORD OF CAUTION !! 
 *  This script fetches data Directly from the steemit.com frontend and should NOT be used for fetching data on user request! 
 *  I recommend using this with a backend database of some kind.
 *  
 *  @param category name of category to fetch
 *  @return array of posts (18 by standard) 
 */

function getSteemitPosts($category) {
  $postsArray = array();

  $steemitAddress = "https://steemit.com/created/{$category}";
  $urlHeaders = @get_headers($steemitAddress);
  if(stristr($urlHeaders['0'],"200")) {
    echo "Fetching $category\n";

    $pageArray = curl($steemitAddress);

    // Steemit PregMatch String
    $re = '/.*?"PostSummary__header.*?small.*?<a href="(?\'PostLink\'.*?)".*?-->(?\'PostTitle\'.*?)<!.*?<\/a>.*?<span title="(?\'PostDate\'.*?)".*?Person.*?href="\/@(?\'PostAuthor\'.*?)".*?Reputation".*?>(?\'AuthorReputation\'.*?)<.*?in.
*?a href=.*?>(?\'PostCategory\'.*?)<.*?(?\'PostImage\'(PostSummary__content|image:url\(.*?\))).*?PostSummary__body.*?<a.*?">(?\'PostSummary\'.*?)<\/a>.*?VotesAndComments__votes".*?-->.*?-->.*?-->(?\'PostVotes\'.*?)<!--.*?VotesAndComments
__comments.*?-->.*?-->.*?-->(?\'PostComments\'.*?)<!--/m';

    // Match
    preg_match_all($re, $pageArray['content'], $matches);

    // Empty Array of Numerical Matches
    for($c=0;$c < count($matches);$c++) { unset($matches[$c]); }

    // Populte Posts Array
    foreach($matches as $type => $item) {
      for($c=0;$c < count($item);$c++) {
        // Fix Image Linking
        if($type == "PostImage") {
          if(!stristr($item[$c], "http")) { $item[$c] = null; }
          $item[$c] = str_replace(array("http://","https://"),"!explodeHere!",$item[$c]);
          $imageUrls = explode("!explodeHere!", $item[$c]);
          $item[$c] = $imageUrls[count($imageUrls)-1];
          if(strstr($item[$c],")")) { $item[$c] = str_replace(")","",$item[$c]); }
        }
        // Fix Summary Double Spaces
        if($type == "PostSummary") { $item[$c] = str_replace("  ", " ", $item[$c]); }
        $postsArray[$c][$type] = $item[$c];
      }
    }

    // Posts are Numbered in order of appearance. Up to 18 entries are sent by steemit by default.
    // 0 = Latest
    // 18 = Oldest
    // $postsArray['number']['PostLink']
    // $postsArray['number']['PostTitle']
    // $postsArray['number']['PostDate']
    // $postsArray['number']['PostAuthor']
    // $postsArray['number']['AuthorReputation']
    // $postsArray['number']['PostImage']
    // $postsArray['number']['PostSummary']
    // $postsArray['number']['PostVotes']
    // $postsArray['number']['PostComments']

  }

  return $postsArray;
}

// CURL Functions
function curl($url, $post=''){
  //cURL options
  $options = array(
      CURLOPT_RETURNTRANSFER => true,     // return web page
      CURLOPT_HEADER         => false,    // don't return headers
      CURLOPT_FOLLOWLOCATION => true,     // follow redirects
      CURLOPT_ENCODING       => "",       // handle all encodings
      CURLOPT_AUTOREFERER    => true,     // set referer on redirect
      CURLOPT_CONNECTTIMEOUT => 500,      // timeout on connect
      CURLOPT_TIMEOUT        => 500,      // timeout on response
      CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_USERAGENT      => "",
      CURLOPT_COOKIESESSION  => false,
  );

  //Go go go!
  $ch      = curl_init( $url );
  curl_setopt_array( $ch, $options );

  $output['content'] = curl_exec( $ch );
  $output['err']     = curl_errno( $ch );
  $output['errmsg']  = curl_error( $ch );
  $output['header']  = curl_getinfo( $ch );
  return $output;
}

?>
