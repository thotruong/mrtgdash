<?

/**
 * MRTG Dashboard.
 * 
 * @author	Stuart Ford
 * @see		https://github.com/stuartford/mrtgdash
 * @license	http://www.gnu.org/licenses/gpl.html
 */

/**
 * Dashboard class sets up the dashboard and creates Entity objects.
 */
class Dashboard {
	
	/**
	 * Page title.
	 * 
	 * @var string 
	 */
	public $title = 'MRTG Dashboard';
	
	/**
	 * Script version
	 * 
	 * @var string 
	 */
	public $version = '1.004';
	
	/**
	 * MRTG entities
	 * 
	 * @var array 
	 */
	public $entities = array();
	
	/**
	 * Server hostname;
	 * 
	 * @var type 
	 */
	public $hostname;
	
	/**
	 * Construct.
	 */
	public function __construct() {
		
		// read entities in current directory
		foreach (glob("*.log") as $entity) {
			$this->entities[] = new Entity(preg_replace("/.log$/", "", $entity));
		}
		
		// determine hostname
		exec("hostname -f", $hostname);
		$this->hostname = $hostname[0];
		
	}

}

/**
 * Entity class represents each MRTG entity found in the current directory.
 */
class Entity {
	
	/**
	 * Entity name.
	 * 
	 * @var string 
	 */
	public $name;
	
	/**
	 * Entity title.
	 * 
	 * @var string 
	 */
	public $title;
	
	/**
	 * Entity page link.
	 * 
	 * @var string 
	 */
	public $link;
	
	/**
	 * Entity log file.
	 * 
	 * @var string 
	 */
	public $log;
	
	/**
	 * Construct.
	 */
	public function __construct($name) {
		
		// add name
		$this->name = $name;
		
		// create nicer-looking title
		$np = explode('_', $name);
		$this->title = $np[0];
		array_shift($np);
		$this->title .= " (".implode(" ",$np).")";
		
		// add HTML and log files
		$this->link = $name.'.html';
		$this->log = $name.'.log';
		
	}
	
	/**
	 * Get graph.
	 * 
	 * @param string $scale	(day, week, month, year)
	 * 
	 * @return string
	 */
	public function getGraph($scale) {
		return "{$this->name}-{$scale}.png";
	}

}

// setup
$dashboard = new Dashboard();

?>

<!DOCTYPE html>

<html lang="en">

<head>

	<title><?php print $dashboard->title; ?></title>

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />

	<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700&#038;subset=latin,latin-ext" type="text/css" media="all" />
	
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
	
	<style type="text/css">

		BODY {
			font-family: 'Open Sans', Helvetica, Arial, sans-serif;
		}
		
		DIV#pageframe {
			margin: 0px;
			margin-left: auto;
			margin-right: auto;
			width: 1050px;
		}
		
		DIV#header {
			background: #333;
			color: white;
			padding: 2px 10px 2px 10px;
		}
		
			DIV#header SPAN.topinfo {
				float: right;
				font-weight: normal;
				padding-right: 15px;
				line-height: 125%;
				margin-top: 10px;
			}
			
				DIV#header SPAN.topinfo DIV.header-left {
					display: inline-block;
					text-align: right;
					width: 100px;
				}
				
				DIV#header SPAN.topinfo DIV.header-right {
					display: inline-block;
					width: auto;
					font-weight: bold;
				}

		DIV#footer {
			border-top: 2px solid #666;
			margin: 2em 10px 2em 10px;
			padding-top: 1em;
			font-weight: bold;
		}

		DIV#footer IMG {
			border: none;
			height: 25px;
		}
		
		.float-right {
			float: right;
		}
		
		DIV.entity H2 {
			font-weight: normal;
			font-size: 150%;
			margin-left: 10px;
		}

		DIV.entity IMG.graph {
			margin: 0px 10px 0px 10px;
			width: 500px;
			height: 135px;
			-moz-box-shadow: 0px 0px 10px #AAA;
			-webkit-box-shadow: 0px 0px 10px #AAA;
			box-shadow: 0px 0px 10px #AAA;
		}

		DIV.entity DIV.graphs {
			position: relative;
		}

			DIV.entity DIV.graphs DIV.graphLabel {
				position: absolute;
				top: -10px;
				background: #666;
				border-radius: 2px;
				opacity: 0.9;
				color: white;
				text-transform: capitalize;
				padding: 0px 4px 0px 4px;
				font-size: 75%;
			}

			DIV.entity DIV.graphs DIV.graphLabel.day,
			DIV.entity DIV.graphs DIV.graphLabel.month {
				left: 260px;
			}

			DIV.entity DIV.graphs DIV.graphLabel.week,
			DIV.entity DIV.graphs DIV.graphLabel.year {
				left: 780px;
			}

		DIV.entity SPAN.options {
			float: right;
			margin-right: 15px;
			margin-top: 10px;
		}
		
			DIV.entity SPAN.options A.btn {
				padding: 3px 4px;
			}
		
			DIV.entity SPAN.options IMG {
				margin-left: 2px;
				margin-right: 2px;
			}

	</style>
	
	<script type="text/javascript">
		
	$(document).ready(function() {
		
		// initial update and set interval
		updateGraphs();
		setInterval(updateGraphs, 300000);
		
		// trigger update on click on last update timestamp
		$('#lastUpdate').click(function() { updateGraphs(); }).tooltip();
		
		// add graph labels
		addGraphLabels();
		
		// change scale change button click
		$('A.change-scale').click(function() {
			
			$(this).parent().parent().children('DIV.graphs').children('IMG.graph').each(function() {
				
				// determine new scale
				var oldScale = $(this).attr('scale');
				if (oldScale == 'day') var newScale = 'month';
				if (oldScale == 'week') var newScale = 'year';
				if (oldScale == 'month') var newScale = 'day';
				if (oldScale == 'year') var newScale = 'week';
				
				// change scale and update
				$(this).attr('scale', newScale);
				updateGraphs($(this));
				addGraphLabels();
				
			});
			
			
		});
		
		// handle clicks on the graphs
		$('IMG.graph').click(function() {
			window.location = $(this).attr('entity')+'.html';
		});
		
		// add images and tooltips to option icons
		$('DIV.entity SPAN.options IMG.icon.log').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADoSURBVBgZBcExblNBGAbA2ceegTRBuIKOgiihSZNTcC5LUHAihNJR0kGKCDcYJY6D3/77MdOinTvzAgCw8ysThIvn/VojIyMjIyPP+bS1sUQIV2s95pBDDvmbP/mdkft83tpYguZq5Jh/OeaYh+yzy8hTHvNlaxNNczm+la9OTlar1UdA/+C2A4trRCnD3jS8BB1obq2Gk6GU6QbQAS4BUaYSQAf4bhhKKTFdAzrAOwAxEUAH+KEM01SY3gM6wBsEAQB0gJ+maZoC3gI6iPYaAIBJsiRmHU0AALOeFC3aK2cWAACUXe7+AwO0lc9eTHYTAAAAAElFTkSuQmCC');
		$('DIV.entity SPAN.options IMG.icon.time').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKrSURBVDjLpdPbT9IBAMXx/qR6qNbWUy89WS5rmVtutbZalwcNgyRLLMyuoomaZpRQCt5yNRELL0TkBSXUTBT5hZSXQPwBAvor/fZGazlb6+G8nIfP0znbgG3/kz+Knsbb+xxNV63DLxVLHzqV0vCrfMluzFmw1OW8ePEwf8+WgM1UXDnapVgLePr5Nj9DJBJGFEN8+TzKqL2RzkenV4yl5ws2BXob1WVeZxXhoB+PP0xzt0Bly0fKTePozV5GphYQPA46as+gU5/K+w2w6Ev2Ol/KpNCigM01R2uPgDcQIRSJEYys4JmNoO/y0tbnY9JlxnA9M15bfHZHCnjzVN4x7TLz6fMSJqsPgLAoMvV1niSQBGIbUP3Ki93t57XhItVXjulTQHf9hfk5/xgGyzQTgQjx7xvE4nG0j3UsiiLR1VVaLN3YpkTuNLgZGzRSq8wQUoD16flkOPSF28/cLCYkwqvrrAGXC1UYWtuRX1PR5RhgTJTI1Q4wKwzwWHk4kQI6a04nQ99mUOlczMYkFhPrBMQoN+7eQ35Nhc01SvA7OEMSFzTv8c/0UXc54xfQcj/bNzNmRmNy0zctMpeEQFSio/cdvqUICz9AiEPb+DLK2gE+2MrR5qXPpoAn6mxdr1GBwz1FiclDcAPCEkTXIboByz8guA75eg8WxxDtFZloZIdNKaDu5rnt9UVHE5POep6Zh7llmsQlLBNLSMTiEm5hGXXDJ6qb3zJiLaIiJy1Zpjy587ch1ahOKJ6XHGGiv5KeQSfFun4ulb/josZOYY0di/0tw9YCquX7KZVnFW46Ze2V4wU1ivRYe1UWI1Y1vgkDvo9PGLIoabp7kIrctJXSS8eKtjyTtuDErrK8jIYHuQf8VbK0RJUsLfEg94BfIztkLMvP3v3XN/5rfgIYvAvmgKE6GAAAAABJRU5ErkJggg==');
		$('DIV.entity SPAN.options IMG.icon.chart').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJYSURBVDjLY/j//z8DJRhMmJQd+x89/W4IRQbY1x5L8590dzmy5PuIqC4gfvA+PPIyEMfhNqD06H+L9gfG9p33/jr23OMEiX30DTj8yT/oFxCf+hAYfBeIfwPxIyBWwjSg5Mh/tYZHzDr1D34aND7Y9tXOsf2Lg/O/z85uNjCFn908lT56eH985xXwzXvygwYUA4yLD/9Xcm+QlS572JWesP7XVyOL79/MLKci22Rc/6DXvPH+X8um+79t2u7/tOu4/w9ugFHxof8wha+1LP89NHT9iaxZIf/BCpWie7/Vi+/N/25kqvrN2Oz/suiO6QgDig6ADfgtJrX0p6TMb1u/Xd+5Eh9M4k16yCyQdH+HYOK9H6JJd+tgBv7U0j3wXVvvA9wAg8J9/6sNAvT/8gr++8Mn1MYQ8aCFIfzBf6bwB3+Zwx/8Ywu7H44e+j8VVX4hDMjf+/8/I6v/fya2OyghHHCn3GuRw3TvJTZnPJdYnXVbbA436Le49Aa4Afp5u///ZGAJ+c3AIg5T4DXT0stjpuULj1nmD9xmW6x1nWu2z2W+6RenBcbxIHmga6XgBujl7vw/R1TDAabZscNommOn0UeHLsNFDj2GPDBxh37DDrtJ+u8x0oFu9vb/liU6khal2jPNS3UfAem3FmU6Gej+tqjX5rBo0rln1qI9GdWArG3/jTI0/Q0z1N3UAyxdgTQ4NQpreMjCFAqpOoHZRvnqUhpROhmmxRo8cAO0M7f8187Y/F8rYxMQb/yvlbYBiNf/1wTh1HX/NUA4ZS0Ur/mvkbwajOEGUIIBf5BxjDvwFIUAAAAASUVORK5CYII=');
		$('DIV.entity SPAN.options A.btn').tooltip();
		
		// add image data to MRTG logo
		$('IMG.mrtg-l').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD8AAAAZBAMAAAB0hOvHAAAAGFBMVEV9Zn00XZhfYoiIZ3mybWn/dk3tdFTVcVy/t7hSAAAAAWJLR0QHFmGI6wAAARFJREFUeNqtkr1vwkAMxalU9jofzQwtnYNOEWsRhK5tqMkc0exFVf7/vueE5AISEyed5bN+vmdbnujt8zm5D/Dn3Ep1Q3twPDvVoznu3YCZyJPZSEux86an1knPgOgBJjwDUo8Aes0eJuiBhQ9YMC9GQOID35ZS2c8EdmtUcwXEaxMqu8+iny2L3v4S4OcSLU2oB1QRiNs54D2TiK1IRuAV7rMPfInMJRBBZNF34QOo54XAaQBC9QGUt2KP6CPugGYEwMuYBamk7BU8ADXluEnRActLCQKQiQGEBDatxgDwDf3pvgM+LrpgrEZ2DiCwQT2MB8VJ09R0DKhMYwCwOFq6R1znsFFpw0jODcvutrS3gH9HWKtq4xNkGAAAAEN0RVh0U29mdHdhcmUAQCgjKUltYWdlTWFnaWNrIDQuMi45IDk5LzA5LzAxIGNyaXN0eUBteXN0aWMuZXMuZHVwb250LmNvbe3o2fAAAAAqdEVYdFNpZ25hdHVyZQBkMjI3Yzc0OThhNTAxZTdmYTQ1MDg2YzlmZjQ0YmI5Y1s0iH4AAAAOdEVYdFBhZ2UANjN4MjUrMCswGBJ4lAAAAABJRU5ErkJggg==');
		$('IMG.mrtg-m').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAAZBAMAAAA2x5hQAAAAGFBMVEV9Zn00XZhVYYxuZIKXaXT/dk3sdFTRcmHY53kGAAAAAWJLR0QHFmGI6wAAAIBJREFUeNpjCEUCBQwEeClGSiapMF6IIBCIpEJ4YYognqAahJcE5ggKpYJ5hhCeICuIFw5kCDsLCpqD5UBmuIYWuUHMDAJqCU0vKy8vB/ECgaaHgo0lxAPrA/GEQLwAIO0aWghSgWSfoDiKW0yxuDMcxQ+hwcj+A/rdGMnvRIYZAMTBUVH0b7Q/AAAAQ3RFWHRTb2Z0d2FyZQBAKCMpSW1hZ2VNYWdpY2sgNC4yLjkgOTkvMDkvMDEgY3Jpc3R5QG15c3RpYy5lcy5kdXBvbnQuY29t7ejZ8AAAACp0RVh0U2lnbmF0dXJlADRlMzY4NzEwMTYxNzJhZWFiOTM5YzlhMmVkNTRiYWI1gk36MgAAAAl0RVh0RGVsYXkAMjUwIyjOEwAAAA50RVh0UGFnZQAyNXgyNSswKzCDkSAfAAAAAElFTkSuQmCC');
		$('IMG.mrtg-r').attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAYQAAAAZBAMAAADDHETJAAAAGFBMVEU0XZg8Y5xVdqhwjbear8z8/f7V3um6yNwiFjvBAAAAAWJLR0QHFmGI6wAABdVJREFUeNrtWElz2lgQbi3AVYaAr/JC5koIOFd5sPCVRRJXvCCuThzB35/+ut+TxJLMUjVVk6qRKSG919vX68MU0C9//Q/hb1zOv8XEEDoKwydy7/HgDfAhZ4Trs3Mruy4/Ksdtnr+DwxJ7E1z3LqgH1JlMBqpc2enzZCLynQ+iBIuktM6tiHnMt/fWmg+7/DXk71JKB2/uBDLUEJetgL6IOqPRJwuhpU9dVrDAQ/MJH6IU14oKhcDPSzx8TLf7dBmUxGtf6BYgSNfU4LvKs+x8EwwxbthOydAWVyy3yPa71GDw02y/yUKqpDTfZDlNE1JD3JVKnlGT75GB0FwLfxySJ1b11viIQghQCN6KOhv2jJd+5dsGN0PsCEnABMri3C2NTw17O8Y3gVuXqJ3IfsHG3iVswW/K4RQvwLSsSRnPoXvhdDaRGtJmNRmp8+hLYiGoAFbhL81eswYhJou+y6vjZ+i65DdD/GRQuhYCOVlAB+yXoPSydQnBWwoFR8FNB3gcCn1LwuUxO6SkWNrBSNbk9NdqCNSofGjeXCiEhij3OCo2Nw4gFCWE5ozcTagOrBErSbuCUNQh8J4HOy7fFyUEt4xC00ZMXK5apwJBYuQsd6Qp21qoIZBVRoFiRJZh9EFMzTxi7/wQAlhbi8A3KrtPFfFxFKgYHEKQrf5gVUHQKLAB46gGwbjHSuE3fz1G8i4C6C5roQYh0iiM+3jo371VjrW1EB1HoTE3hZccR8FLSpYyCoa9g60dxYFZsrRF6BRhhcA4oZQS4htg4DcbhfbqJAosNm7BrqK1Frjakc4m0vhr6TV+LYm1XNzV8OY6BIub1XwJ9iZTOgnBU4336+E1J9LN9fACtZDV5pK/LF+slHHgz7Vx9GZVR8qGw5sQNmq9MNcUqekk/ux8OZtEykZf0lBh48oqYlMLpql+HxWLOoR48rDhb28mpOiWmdJGiELleHZ0VReNlwkaE22lbDjeHjqSadOmqfZeR/HSQNjxH/lzhuCV5fxUs0Hdzmzf+OXK5m1FbBIpm0weBrBxFdYhsOJXkgYPhsbraDThDs3DKeQoeAcQINC95mBaKe4S1nGnkbkQY6Q9AMIIU43nQvbJQNgS10w3Yg2+nQunUWiv+muiMgpOXhEflvO3rLRJ2cUyGocEl9tyNrXgnkBoibksBUnFue2wbf5qjwlf8HHDcaUjBfzXfNpo4nFHSuCiOGS5P+tIK6lBWwtO9sOOVDUZZXcwbqnI8zw9aqrsj6wGQTLTGd7cLiEFA7ub5flGwxcYQ7yqnJ/6Igxb0ly2GJknUYhq5eygV3aVCz3lKAq2I71VKW3Yoelitd/veZiYjmSjQJtaORsJgNKIBE/8+LjfzY0mOmqqvbWfGAhwSQKhiWky7KmzHan7XJVcc1ER245kWJyyvZs9aEJjgdTj0RZHFQQnVTytmZWyVVqL+BACS9MBFEiYYjhzZfzAze/MGSmRNueaLsiqS+LD6RxRf32QSITwySPcq2ckYxOvP9cgGDyciUzWN8OzrLozc6G3UAgg6BZc2zlQkJz3zp+RoCL+KoCAxBIfT2cT38oDH+doGIR6NmckUwuhCmLD5L0ryzjDMBknPYdDKkbjXTsjWQieHKp4dM/RBwLAFSMh9VxH0izyUZxu/EwV8fEZ6eSAwdbk4uYiOOpILASPHXWzi1MwjZdWikSYy8+vJdLRGSlSCDPjFj4XtLLJqGDzmtwKnk+mM/zjjLOHUSHoLbGJQso9501YeotDCBT/npgUOayFK/T818ljatLpY/p9NM0GZKToQaU1Py5naNLZJaXJZkMq4E/ZKdM03eJXUC4QmgA5VQiYT3f8HoBEC9YQK4mbCwSweFu1qGS/fNVR2XuTJXJflB9i/E2avttq+MLj6t5wtrffZbHz4inCqbUjFwiw24V+09RCW1NtPbr/7De1exUcEf/pdfGzzeuw/IV84d6ccp0yO5V5wX/iPxj/6P8Cf9U/v8T1Bxsqem0dCtU+AAAAQ3RFWHRTb2Z0d2FyZQBAKCMpSW1hZ2VNYWdpY2sgNC4yLjkgOTkvMDkvMDEgY3Jpc3R5QG15c3RpYy5lcy5kdXBvbnQuY29t7ejZ8AAAACp0RVh0U2lnbmF0dXJlADEyMTc1ZTZiMzE4MzMzN2QzODFmMDFjNjNjZmM0MzZlsw4ywQAAAA90RVh0UGFnZQAzODh4MjUrMCswUrNpiQAAAABJRU5ErkJggg==');
		
	});
	
	/**
	 * Add graph labels.
	 * 
	 * @return void
	 */
	function addGraphLabels() {
		
		// remove existing labels
		$('DIV.graphLabel').each(function() { $(this).remove(); });
		
		// add new labels
		$('DIV.entity DIV.graphs IMG.graph').each(function() {
			var scale = $(this).attr('scale');
			var label = $('<div class="graphLabel '+scale+'">'+scale+'</div>');
			$(this).parent().append(label);
		});
		
	}
	
	/**
	 * Refresh all entities.
	 * 
	 * @param specificGraph		- pass one graph IMG just to process that one
	 * 
	 * @return void
	 */
	function updateGraphs(specificGraph) {
		
		// if no specific graph passed then update all
		if (specificGraph) {
			var graphs = specificGraph;
		} else {
			var graphs = $('IMG.graph');
		}
		
		// update graphs
		graphs.each(function() {
			var graph = $(this);
			$(this).fadeTo('slow', 0.25, function() {
				$(this).attr('src', $(this).attr('entity')+'-'+$(this).attr('scale')+'.png?i=' + (Math.random()*1000));
				$(this).fadeTo('slow', 1);
			});
		});
		
		// update timestamp
		var date = new Date();
		$('#lastUpdate').html(date.toLocaleString());
		
	}
	
	</script>

</head>

<body>

<div class="container" id="pageframe">

<div id="header">
		
	<span class="topinfo">
		<div>
			<div class="header-left">MRTG server:</div>
			<div class="header-right"><?php print $dashboard->hostname; ?></div>
		</div>
		<div>
			<div class="header-left">Last update:</div>
			<div class="header-right" id="lastUpdate" data-toggle="tooltip" title="click to update now" data-placement="bottom"></div>
		</div>
	</span>
	
	<h1><?php print $dashboard->title; ?></h1>
	
</div> <!-- header -->
	
<?php foreach ($dashboard->entities as $entity) : ?>

	<div class="entity">

		<span class="options">
			<a class="btn change-scale" data-toggle="tooltip" title="change graph scales" data-placement="bottom"><img width="16" height="16" class="icon time" alt="change graph scales" /></a>
			<a href="<?php echo $entity->log; ?>" class="btn" data-toggle="tooltip" title="download log file" data-placement="bottom"><img width="16" height="16" class="icon log" alt="download log file" /></a>
		</span>
		
		<h2><a href="<?php print $entity->link; ?>"><?php print $entity->title; ?></a></h2>

		<div class="graphs">
			<img class="graph graph-left" scale="day" entity="<?php print $entity->name; ?>" src="<?php print $entity->getGraph('day'); ?>" />
			<img class="graph graph-right" scale="week" entity="<?php print $entity->name; ?>" src="<?php print $entity->getGraph('day'); ?>" />
		</div>
	
	</div>

<?php endforeach; ?>

<div id="footer">
	<div class="float-right">
		<a href="http://oss.oetiker.ch/mrtg/">
			<img class="mrtg-l" width="63" title="MRTG" alt="MRTG" /><img class="mrtg-m" width="25" title="MRTG" alt="MRTG" /><img class="mrtg-r" width="388" title="Multi Router Traffic Grapher" alt="Multi Router Traffic Grapher" />
		</a>
	</div>
	<div>
		<a href="https://github.com/stuartford/mrtgdash">MRTG Dashboard</a> v.<?php echo $dashboard->version; ?>
	</div>
</div> <!-- footer -->

</div> <!-- pageframe -->
	
</body>
</html>
