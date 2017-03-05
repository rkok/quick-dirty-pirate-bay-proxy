<?php

return [
	// ruTorrent support
	// If enabled, this will add a button which
	// sends the torrent to ruTorrent for downloading
	'rutorrent' => [
		// The root URL of the ruTorrent instance, trailing slash omitted
		// If it is located on a different domain, you may need to set CORS headers
		'url' => '/myrutorrent',
		// Optional: HTTP Basic authentication
		'basic_auth' => [
			'username' => 'changeme',
			'password' => 'changeme',
		],
	],
];
