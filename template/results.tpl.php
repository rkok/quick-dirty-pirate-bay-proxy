<?php
$config = @include_once(__DIR__ . '/../config.php');
$config = $config ?: [];
?>

<style>
.rutorrent-send {
	display: inline-block;
	width: 16px;
	height: 16px;
	background: url(images/rutorrent.png);
	cursor: pointer;
}
.rutorrent-send.busy {
	transform: rotate(360deg);
	transition: all 0.3s ease-in-out 0s;
}
.rutorrent-send.success {
	background: url(images/rutorrent-success.png);
}
.rutorrent-send.failure {
	background: url(images/rutorrent-failure.png);
}
</style>

<script>
/**
 * Send a torrent URL to ruTorrent for downloading
 */
const rutorrentSend = (torrentUrl, onSuccess, onFailure) => {
	const fail = err => {
		console.log("Error sending to ruTorrent. Torrent URL / fetch result: ", torrentUrl, err);
		onFailure();
	};

	fetch("<?php echo $config['rutorrent']['url']; ?>/php/addtorrent.php", {
		method: 'post',
		headers: new Headers({
			'Content-Type': 'application/x-www-form-urlencoded'
<?php if($config['rutorrent'] && $authCfg = @$config['rutorrent']['basic_auth']): ?>
			,'Authorization': 'Basic ' + btoa('<?php echo $authCfg['username'] . ":" . $authCfg['password']; ?>'),
<?php endif; ?>
		}),
		mode: 'cors',
		body: 'url=' + encodeURIComponent(torrentUrl),
	})
	.then(res => {
		let dummy = document.createElement('a');
		dummy.href = res.url;

		if( ! res.redirected || dummy.search.indexOf('result[]=Success') === -1) {
			fail(res);
			return false;
		}

		onSuccess();
	})
	.catch(fail);
};

// Attach ruTorrent button action to DOM
window.onload = () => {
	[].forEach.call(document.querySelectorAll('.rutorrent-send'), ruButton => {
		ruButton.onclick = () => {
			const toggleBusy = () => { ruButton.classList.toggle('busy') };
			toggleBusy();

			rutorrentSend(
				ruButton.previousElementSibling.href,
				() => { toggleBusy(); ruButton.classList.add('success') },
				() => {	toggleBusy(); ruButton.classList.add('failure') }
			);
		};
	});
};
</script>

<h2>Results for "<?php echo $searchString; ?>"</h2>
<table>
	<thead>
		<th>Name</th>
		<th>Upload date</th>
		<th>Size</th>
		<th>Uploader</th>
		<th>Seeds</th>
		<th></th>
	</thead>
	<tbody>
	<?php foreach($resultsArray as $result): ?>
		<tr>
			<td><?php echo htmlspecialchars($result['name']); ?></td>
			<td><?php echo htmlspecialchars($result['uploadDate']); ?></td>
			<td><?php echo htmlspecialchars($result['size']); ?></td>
			<td><?php echo htmlspecialchars($result['uploader']); ?></td>
			<td align="right"><?php echo htmlspecialchars($result['seeds']); ?></td>
			<td>
				<a href="<?php echo htmlspecialchars($result['url']); ?>">Link</a>
				<?php if($config['rutorrent']): ?><span class="rutorrent-send"></span><?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
