<?php

/*

reddit-giveaway
https://github.com/neatnik/reddit-giveaway

MIT License

Copyright (c) 2022 Neatnik LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

define('POST_URL', 'https://www.reddit.com/r/SeveranceAppleTVPlus/comments/tzayjj/postfinale_community_giveaway/');
define('SELECTION_COUNT', 3);
define('INELIGIBLE_USERNAMES', array('neatnikllc'));
define('RESULTS_FILE', 'results.txt');

$GLOBALS['entries'] = array();
$GLOBALS['more'] = array();

$json = json_decode(file_get_contents(POST_URL.'.json?limit=1000'), true);

function crawl($a) {
	if(!is_array($a)) {
		return;
	}
	
	if(is_array($a)) {
		if(isset($a['author']) && isset($a['body'])) {
			$GLOBALS['entries'][] = json_encode(array('author' => $a['author'], 'body' => $a['body'], 'permalink' => $a['permalink']));
		}
	}
	
	if(isset($a['kind']) && $a['kind'] == 'more') {
		foreach($a['data']['children'] as $child) {
			$GLOBALS['more'][] = $child;
		}
	}
	
	foreach($a as $v) {
		crawl($v);
	}
}

$output = 'Reddit Giveaway Winner Selection'."\n";
$output .= '================================'."\n\n";
$output .= 'Giveaway URL: '.POST_URL."\n\n";
$output .= 'Entry retrieval began: '.date("r")."\n";

crawl($json);

foreach($GLOBALS['more'] as $id) {
	$json = json_decode(file_get_contents(POST_URL.$id.'/.json?limit=1000'), true);
	crawl($json);
}

$output .= 'Entry retrieval ended: '.date("r")."\n\n";

$output .= 'Total comments: '.count($GLOBALS['entries'])."\n";

foreach($GLOBALS['entries'] as $json) {
	$entry = json_decode($json);
	if(in_array($entry->author, INELIGIBLE_USERNAMES)) continue;
	$valid_entries[$entry->author]['body'] = $entry->body;
	$valid_entries[$entry->author]['permalink'] = $entry->permalink;
}

$output .= 'Total valid entries: '.count($valid_entries)."\n\n";

$winners = array_rand($valid_entries, SELECTION_COUNT);

if(SELECTION_COUNT == 1) {
	$output .= 'Winner'."\n";
	$output .= '------'."\n\n";
}
else {
	$output .= 'Winners'."\n";
	$output .= '-------'."\n\n";
}

$i = 1;
foreach($winners as $winner) {
	$output .= $i.'. u/'.$winner."\n";
	$output .= $valid_entries[$winner]['body']."\n";
	$output .= 'https://reddit.com'.$valid_entries[$winner]['permalink']."\n\n";
	$i++;
}

$output .= 'Valid entries'."\n";
$output .= '-------------'."\n\n";

foreach($valid_entries as $username => $arr) {
	$output .= $username.' - https://reddit.com'.$arr['permalink']."\n";
}

echo '<pre>';
echo $output;
file_put_contents('results.txt', $output);