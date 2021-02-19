<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'contributors.php');
//require_once "./global.php";
//add_breadcrumb("Contributors");

$org = "scratchdit";

function fetch($url)
{
	$ch = curl_init();
	if ($ch === FALSE) {
		echo "init failed";
	}

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'RedGuy12');
	curl_setopt($ch, CURLOPT_COOKIE, "user_session=G9FxEJnxYRJuYeRDHMEiVHWdKamTuHHVxZyOi8b_Jzl_GqU6");
	$out = curl_exec($ch);
	curl_close($ch);
	if ($out === FALSE) {
		$out  = curl_error($ch);
		$out .= " ";
		$out .= curl_errno($ch);
	}

	unset($ch);
	return $out;
}

preg_match_all("/f=\"\/orgs\/$org\/people\/(.*)\"></", fetch("https://github.com/orgs/$org/people"), $people);
foreach ($people[1] as $i => $person) {
	$contributors[$person]["💼"] = 1;
}

unset($person);
unset($i);


$repos = json_decode(fetch("https://api.github.com/orgs/$org/repos"));
foreach ($repos as $r) {
	$repo        = $r->name;
	$commmits    = fetch("https://api.github.com/repos/$org/$repo/commits");
	$deploys     = fetch("https://api.github.com/repos/$org/$repo/deployments");
	$issues      = fetch("https://api.github.com/repos/$org/$repo/issues?state=all");
	$pulls       = fetch("https://api.github.com/repos/$org/$repo/pulls");
	$discussions = fetch("https://github.com/$org/$repo/discussions?discussions_q=is%3Aanswered");
	$wiki        = fetch("https://github.com/$org/$repo/wiki/_history");

	
}

print_r($contributors);


//output_page($contributors);
