<?php
$config = @include_once(__DIR__ . '/config.php');
$config = $config ?: [];

header('Content-Type: text/html; charset=utf-8');

if(isset($_GET['search_string']) && !empty($_GET['search_string'])) {
	$searchString = preg_replace('#[^A-Za-z0-9 -]#', '', $_GET['search_string']);
	$resultsArray = array();
	$npages = ($config && $config['npages']) ? $config['npages'] : 1;

	for($p=1; $p<$npages+1; $p++) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_URL, "https://tpb.party/search/$searchString/$p/7/0");
		// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);

		if (@$config['debug']) {
			$out = fopen('php://output', 'w');
			curl_setopt($curl, CURLOPT_STDERR, $out);
		}

		if (@$config['proxy']) {
			curl_setopt($curl, CURLOPT_PROXY, $config['proxy']);
		}

		$result = curl_exec($curl);

		if (@$config['debug']) {
			fclose($out);
			$debug = ob_get_clean();
			echo "Raw cURL output:<br><pre>$debug</pre>";
        }

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if(!$result || $httpCode !== 200) {
		    die("Error: HTTP code $httpCode returned (0 == cURL issue)");
		}

		curl_close($curl);

		// Trim useless whitespace
		$result = preg_replace("/>[\t\s\n]+</", "><", $result);

		// Trim scripts
		$result = preg_replace("/\/\* <!\[CDATA.*\]\]> \*\//", '', $result);

		// Suppress ugly HTML formatting warnings in error log
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc->loadHTML($result);

		$xpath = new DOMXPath($doc);

		$rows = $xpath->query('//table[@id="searchResult"]//tr');

		if(empty($rows)) {
			break;
		}

		foreach($rows as $i => $tr) {
			if ($i === 0 || $i === $rows->length - 1) {
				continue; // Skip first and last row (column headings + page numbers)
			}

			$resultItem = array();
			/** @var DOMElement $mainInfoObj */
			$mainInfoObj = $tr->childNodes->item(1); // 2nd cell: name, link and metadata
			$seedsObj = $tr->childNodes->item(2);

			$resultItem['name'] = trim($xpath->query('.//*[@class="detLink"]', $mainInfoObj)->item(0)->nodeValue);
			$resultItem['url'] = $xpath->query('.//a[starts-with(@href, "magnet:")]', $mainInfoObj)->item(0)->attributes->getNamedItem('href')->value;
			$resultItem['seeds'] = $seedsObj->textContent;

			// Meta fields
			$meta = trim($mainInfoObj->textContent);
			preg_match("/Uploaded ([^,]+), Size ([^,]+), ULed by (.*)$/", $meta, $matches);
			list($_, $uploadDate, $size, $uploader) = $matches;
			$resultItem += array(
				'uploadDate' => $uploadDate,
				'size' => $size,
				'uploader' => $uploader
			);
			$resultsArray[] = $resultItem;
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title><?=isset($searchString) ? "$searchString - " : ''?>TPB</title>
</head>
<body>
<?php
include(__DIR__ . "/template/search-box.tpl.php");

if(isset($resultsArray)) {
	include(__DIR__ . "/template/results.tpl.php");
}
?>
</body>
</html>
