<?php	
// Get TSID
$tsid = (isset($_GET['tsid'])) ? $_GET['tsid'] : 0;

if ($tsid) {
	// Get data
	$cache_url = sprintf('cache/%s.js', $tsid);
	if (!file_exists($cache_url) || (time() - filemtime($cache_url) > (60 * 15))) {
		// Get fresh copy
		$glitch_url = sprintf('http://api.glitch.com/simple/players.fullInfo?player_tsid=%s', $tsid);
		$glitchdata = @file_get_contents($glitch_url);
		if ($glitchdata) {
			file_put_contents($cache_url, $glitchdata);
			echo 1;
		}
	}
}
?>