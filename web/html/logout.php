<?php

session_start();

session_destroy();
require_once "../config.php";
require_once "../Functions/authenticatedRequest.php";

header("Set-Cookie: PHPSESSID=; expires=".(time() - 3600).";path=/; domain=".DB_HOST.";SameSite=Strict;HttpOnly=On;Secure");
?>
<script>
	(async function () {
		try {
			await authenticatedRequest(function () {
				return fetch(`${apiServer}logout`, {
					method: 'POST',
					credentials: 'include',
					headers: {
						'X-Client-Type': 'web'
					}
				});
			});
		} catch (error) {
			// API logout is best effort; the native web session is already closed.
		}

		window.location.replace('../index.php');
	})();
</script>