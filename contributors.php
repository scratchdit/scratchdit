<?php
$time_start = microtime(TRUE);
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'contributors.php');
//require_once "./global.php";
//add_breadcrumb("Contributors");

$org = "scratchdit";

$people_html = fetch("https://github.com/orgs/$org/people");
preg_match_all("/f=\"\/orgs\/$org\/people\/(.*)\"></", $people_html, $people);
foreach ($people[1] as $i => $person) {
	$contributors[$person]["💼"] = 1;
}

$count = preg_match_all('/\d*<\/a> <a c/', $people_html);
for ($page = 2; $page < $count; $page++) {
	$people_html = fetch("https://github.com/orgs/$org/people?page=$page");
	preg_match_all("/f=\"\/orgs\/$org\/people\/(.*)\"></", $people_html, $people);
	foreach ($people[1] as $i => $person) {
		$contributors[$person]["💼"] = 1;
	}
}

unset($i);
unset($page);
unset($count);
unset($person);
unset($people);
unset($people_html);

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

output_page($contributors);

function output_page($out)
{
	global $time_start;
	$time_end      = microtime(TRUE);
	$out["__TIME"] = $time_end - $time_start;
	echo json_encode($out);
}

function fetch($url)
{
	$ch = curl_init($url);
	if ($ch === FALSE) {
		return FALSE;
	}

	curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_SSL_VERIFYPEER => FALSE,
		CURLOPT_USERAGENT      => 'RedGuy12',
		CURLOPT_COOKIE         => "user_session=G9FxEJnxYRJuYeRDHMEiVHWdKamTuHHVxZyOi8b_Jzl_GqU6"
	));
	$out = curl_exec($ch);
	curl_close($ch);
	if ($out === FALSE) {
		return $ch;
	}

	return $out;
}
