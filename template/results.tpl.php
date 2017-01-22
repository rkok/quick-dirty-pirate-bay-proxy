<h2>Results for "<?php echo $searchString; ?>"</h2>
<table>
	<thead>
		<th>Name</th>
		<th>Upload date</th>
		<th>Size</th>
		<th>Uploader</th>
		<th>Seeds</th>
		<th>Magnet URI</th>
	</thead>
	<tbody>
	<?php foreach($resultsArray as $result) { ?>
		<tr>
			<td><?php echo htmlspecialchars($result['name']); ?></td>
			<td><?php echo htmlspecialchars($result['uploadDate']); ?></td>
			<td><?php echo htmlspecialchars($result['size']); ?></td>
			<td><?php echo htmlspecialchars($result['uploader']); ?></td>
			<td align="right"><?php echo htmlspecialchars($result['seeds']); ?></td>
			<td><a href="<?php echo htmlspecialchars($result['url']); ?>">Magnet</a></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
