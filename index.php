<!DOCTYPE html>

<html>

<head>

	<title>Combined MRTG Overview</title>

	<meta http-equiv="refresh" content="300" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />

	<style type="text/css">

		BODY {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 80%;
		}

		H2 {
			font-weight: normal;
		}

		div#footer {
			border: none;
			font-size: .8em;
			width: 476px;
			margin-top: 2em;
		}

		div#footer img {
			border: none;
			height: 25px;
		}

	</style>

</head>

<body>

<h1>Combined MRTG Overview</h1>

<?php foreach (glob("*.log") as $entity) : ?>

	<?php $text = preg_replace("/.log$/", "", $entity); ?>
	<?php $link = preg_replace("/.log$/", ".html", $entity); ?>
	<?php $dpng = preg_replace("/.log$/", "-day.png", $entity); ?>
	<?php $wpng = preg_replace("/.log$/", "-week.png", $entity); ?>

	<h2><a href="<?php echo $link; ?>"><?php echo $text; ?></a></h2>

	<div>
		<a href="<?php echo $link; ?>"><img src="<?php echo $dpng; ?>" /></a>
		<a href="<?php echo $link; ?>"><img src="<?php echo $wpng; ?>" /></a>
	</div>

<?php endforeach; ?>

<div id="footer">
	<a href="http://oss.oetiker.ch/mrtg/"><img src="mrtg-l.png" width="63" title="MRTG" alt="MRTG" /><img src="mrtg-m.png" width="25" title="MRTG" alt="MRTG" /><img src="mrtg-r.png" width="388" title="Multi Router Traffic Grapher" alt="Multi Router Traffic Grapher" /></a>
</div>

</body>
</html>
