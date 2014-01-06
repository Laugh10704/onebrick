<?php
//$_SESSION['tool'] = 'imageBrowser';
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>One Brick Newsletter Images</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="robots" content="noindex, nofollow" />
	<link href="<?php print($newsletter_tool_dir); ?>css/standaloneBrowser.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="ckfinder/ckfinder.js"></script>
</head>
<body>
	<p>
    <img src="<?php print($newsletter_tool_dir); ?>images/150px-Logo.gif" border=0 />
  </p>  
	<hr />
	<p style="padding-left: 30px; padding-right: 30px;">
    
		<script type="text/javascript">
			var finder = new CKFinder();
			finder.basePath = './';
			finder.create();
		</script>

	</p>
</body>
</html>
