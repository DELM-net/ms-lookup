<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$search = false;
$results = array();

// get form input
$page = cleanInput('page');
$cat = cleanInput('cat');
$id = cleanInput('id');
$out = cleanInput('out');
if ($out != 'xml' && $out != 'json') $out = 'html';

// load catalogue list
$catalogues = loadCSV("metadata/ms-catalogues.csv", false);
if (is_null($catalogues)) $page = 'error';

// load index of datasets
$datasets = loadCSV("metadata/linked-resources.csv", false); 
if (is_null($datasets)) $page = 'error';
else ksort($datasets);

// search
if ($cat <> '' & $id <> '') {
	$search = true;

	// load each dataset
	foreach ($datasets as $dataset) {
		$data = loadCSV('data/' . $dataset[0], false);
		if (! is_null($data)) {
			// check each line
			foreach ($data as $line) {
				// save details to results array if matching
				if ($line[0] == $cat && str_contains($line[1], $id)) {
					$results[] = array(
						'ref' => $line[0] . ' ' . $line[1], 
						'dataset' => $dataset[1], 
						'url' => $line[2]
						);
				}
			}
		}
	}

	// output as XML or JSON if requested
	if ($out == 'xml') {
		$xml = new SimpleXMLElement('<mslookup/>');
		foreach ($results as $result) {
			$link = $xml->addChild('link');
			$link->addChild('resource', $result['dataset']);
			$link->addChild('url', $result['url']);
		}
		header('Content-Type: text/xml');
		print $xml->asXML();
	}
	elseif ($out == 'json') {
		header('Content-Type: application/json; charset=utf-8');
		print json_encode($results);
	}
}

// write web page, unless XML or JSON has been outputted
if ($out == 'html') {

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">

<title>Manuscript Look-up Service</title>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500" />

<style>

body {
	font-family: 'Open Sans', sans-serif;
	font-size: 1.2em;
}
h1, .display-6 {
s	font-size: 2em;
}
h2 {
	font-size: 1.5em;
}
a, a:visited {
	color:  #2874a6;
}	

</style>

</head>
<body class="bg-light">

<div class="container mt-5">

<h1>Manuscript Look-up Service</h1>

<p class="mb-5 small"><a href="https://github.com/DELM-net/ms-lookup/">https://github.com/DELM-net/ms-lookup/</a></p>

<?php
	// show offline message if error found above
	if ($page == 'error') {
		print '<p>Service is currently unavailable.</p>';
	}
	else {

?>
<form method="get">
	<div class="row p-1">
		<div class="col-sm-2"><label for="formCat" class="form-label">Catalogue</label></div>
		<div class="col-sm-6">
			<select name="cat" id="formCat" class="form-select">
<?php

		foreach ($catalogues as $catOption) {
			if ($catOption[0] <> '') {
				print '<option value="' . $catOption[0] . '"';
				if ($catOption[0] == $cat) print ' selected';
				print '>' .  $catOption[1] . '</option>' . "\n";
			}
		}

?>
			</select>
		</div>
	</div>
	<div class="row p-1">
		<div class="col-sm-2"><label for="formIdent" class="form-label">Identifier</label></div>
		<div class="col-sm-6"><input class="form-control" type="text" name="id" id="formIdent" placeholder="Enter a reference number (without any volume number)" value="<?php print $id; ?>" /></div>
	</div>
	<div class="row p-1">
		<div class="col-sm-2">Output</div>
		<div class="col-sm-6 small">
			<input class="form-check-input" type="radio" name="out" value="" id="formOutput1" checked="checked" />
			<label for="formOutput1" class="form-label">HTML</label>
			<input class="form-check-input ms-5" type="radio" name="out" value="xml" id="formOutput2" />
			<label for="formOutput2" class="form-label">XML</label>
			<input class="form-check-input ms-5" type="radio" name="out" value="json" id="formOutput3" />
			<label for="formOutput3" class="form-label">JSON</label>
		</div>
	</div>
	<div class="row p-1">
		<div class="col-sm-2"></div>
		<div class="col-sm-6"><button type="submit" class="btn btn-primary">Search	for links</button></div>
	</div>
</form>

<?php

		if (! $search) {

			// list catalogues
			print '<h2 class="mt-5 h4 pt-3">Manuscript catalogues</h2>';
			print '<table class="table small">';

			foreach ($catalogues as $catalogue) {
				print '<tr>';
				print '<td>' . $catalogue[1] . '</td>';
				print '<td>' . $catalogue[2] . '</td>';
				print '</tr>';
			}
			print '</table>';

			// list datasets
			print '<h2 class="mt-5 h4 pt-3">Linked resources</h2>';
			print '<table class="table small">';

			foreach ($datasets as $dataset) {
				print '<tr>';
				print '<td>' . $dataset[1] . '</td>';
				print '<td><a href="' . $dataset[2] . '">' . $dataset[2] . '</a></td>';
				print '</td>';
				print '</tr>';
			}
			print '</table>';
		}
		
		// search results
		else {
			print '<h2 class="mt-5 mb-3">Search results</h2>';
			if (! $results) {
				print '<p>No matches found.</p>';
			}
			else {
				print '<table class="table">';
				print '<tr>';
				print '<th>Ref</th>';
				print '<th>Resource</th>';
				print '<th>Link</th>';
				print '</tr>';
				foreach ($results as $result) {
					print '<tr>';
					print '<td>' . $result['ref'] . '</td>';
					print '<td>' . $result['dataset'] . '</td>';
					print '<td><a href="' . $result['url'] . '">' . $result['url'] . '</a></td>';
					print '<tr>';
				}
				print '</table>';
			}
		}
	}
?>

</div>

</body>
</html>
<?php
}

// processes form input
function cleanInput($key) {
	if (isset($_GET[$key])) return htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8');
}

// opens CSV file and returns as array
function loadCSV($fileName, $hasHeader) {
	if (file_exists($fileName)) {
		$contents = array();
		$file = fopen($fileName,"r");
		
		// if file has header, load first line (and ignore)
		if ($hasHeader) $line = fgetcsv($file);

		// compile contents into array
		while (! feof($file)) {
			$line = fgetcsv($file);
			if ($line !== false) $contents[] = $line;
		}
		fclose($file);
		return $contents;
	}
	else {
		return null;
	}
}

?>