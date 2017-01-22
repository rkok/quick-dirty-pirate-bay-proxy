<?php
$prefill = isset($searchString) ? $searchString : "";
?>
<form method="GET" action="">
	<h1>TPB Search</h1>
	<p>Enter search string:
		<input type="text" name="search_string" value="<?php echo htmlspecialchars($prefill); ?>">
		<input type="submit" value="Go!">
	</p>
</form>
