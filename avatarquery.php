<?php
#ini_set("display_errors", 1);
#ini_set("track_errors", 1);
#ini_set("html_errors", 1);
#error_reporting(E_ALL);


$servers = array(
	'Skyblock' => array(
		'ip' => 'sky.freecraft.eu',
		'port' => '25555'
	),
	'Survival' => array(
		'ip' => 's.freecraft.eu',
		'port' => '25566'
	)
);


$SERVER_IP = "lobby.freecraft.eu"; //Insert the IP of the server you want to query. 
$SERVER_PORT = "25565"; //Insert the PORT of the server you want to ping. Needed to get the favicon, motd, players online and players max. etc

$SHOW_FAVICON = "on"; //"off" / "on"

$TITLE = "FreeCraft";
$TITLE_BLOCK_ONE = "General Information";
$TITLE_BLOCK_TWO = "Players";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_GET['server'])) {
	$server = key($servers);
} else {
	$server = $_GET['server'];
}

if(empty($servers[$server]['ip'])) {
	$ip = reset($servers)['ip'];
	$port = reset($servers)['port'];
} else {
	$ip = $servers[$server]['ip'];
	$port = $servers[$server]['port'];
}

$ping_url = "https://api.minetools.eu/ping/" . $SERVER_IP . "/" . $SERVER_PORT;
$query_url = "https://api.minetools.eu/query/" . $ip . "/" . $port;

$ping = json_decode(file_get_contents($ping_url), true);

//Put the collected player information into an array for later use.
if(empty($ping['error'])) { 
	$version = $ping['version']['name'];
	$online = $ping['players']['online'];
	$max = $ping['players']['max'];
	$motd = $ping['description'];
	$favicon = $ping['favicon'];
}

?>

<!DOCTYPE html>
<html>
	<head>
        <meta charset="utf-8">
        <title><?php echo htmlspecialchars($TITLE); ?></title>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    	<link href='//fonts.googleapis.com/css?family=Lato:300,400' rel='stylesheet' type='text/css'>
    	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.js"></script>
    	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.js"></script>
    	<script src="bootstrap-notify.js"></script>
    	<script language="javascript">
   		jQuery(document).ready(function(){
 			$('body').tooltip({
			    selector: '[rel=tooltip]'
			});
     	});

		</script>    	

<script>
$(document).ready(function () {
	// http://bootstrap-notify.remabledesigns.com/

	var ping_url = "<?php echo $ping_url; ?>"
	var query_url = "<?php echo $query_url; ?>"
	players =  []


	refresh();
	query(false);

	setInterval(function () {
		refresh(),
		query(true);
	}, 500);


	function display_notify(name, event, color) {
		$.notify({
			// options
			icon: '//cravatar.eu/helmhead/' + name + '/64',
			message: name + ' ' + event 
		},{
			// settings
			type: color,
			timer: 1000,
			placement: {
				from: "bottom",
				align: "right"
			},
			icon_type: 'img',
		});
	}

	function refresh() {
		$.get(ping_url, function( data ) {
			$(".playersonline").text(data['players']['online'] + '/' + data['players']['max'])
		});
	}

	function query(notify) {
		$.get(query_url, function( data ) {
			all = data['Playerlist']

			for(player in all) {
				if($.inArray(all[player], players) == "-1") {
					console.log("Added " + all[player]);
					players.push(all[player]);

					$('#players').append(
						'<a data-placement="top" rel="tooltip" style="display: inline-block;" title="' + all[player] + '">'
						+
						'<img id="' + all[player] + '" src="//cravatar.eu/avatar/' + all[player] + '/50" style="margin-bottom: 5px; margin-right: 5px; border-radius: 3px; ">'
						+
						'</a>'
					);

					if(notify) {
						display_notify(all[player], "joined", "success")
					}
					
				}
			}

			for(player in players) {
				if($.inArray(players[player], all) == "-1") {
					console.log("Removed " + players[player]);
					$('#' + players[player]).remove();

					display_notify(players[player], "left", "danger")

					for(var i = 0; i < players.length; i++) {
						if(players[i] === players[player]) {
							players.splice(i, 1);
							break;
						}
					}

				}
			}
		});
	}

});
</script>

    	<style>
    	/*Custom CSS Overrides*/
    	body {
      		font-family: 'Lato', sans-serif !important;
    	}
    	</style>
    </head>
    <body>
	<div class="container">
        <h1><?php echo htmlspecialchars($TITLE); ?></h1><hr>       
		<div class="row">
			<div class="col-md-4">
				<h3><?php echo htmlspecialchars($TITLE_BLOCK_ONE); ?></h3>
				<table class="table table-striped">
					<tbody>
						<tr>
							<td><b>IP</b></td>
							<td><?php echo $SERVER_IP; ?></td>
						</tr>
					<?php if(empty($ping['error'])) { ?>
						<tr>
							<td><b>Version</b></td>
							<td><?php echo $version; ?></td>
						</tr>
					<?php } ?>
					<?php if(empty($ping['error'])) { ?>
						<tr>
							<td><b>Players</b></td>
							<!-- <td><?php echo "".$online." / ".$max."";?></td> -->
							<td><div class="playersonline"></div></td>
						</tr>
					<?php } ?>
						<tr>
							<td><b>Status</b></td>
							<td><?php if(empty($ping['error'])) { echo "<i class=\"fa fa-check-circle\"></i> Server is online"; } else { echo "<i class=\"fa fa-times-circle\"></i> Server is offline";}?></td>
						</tr>
					<?php if(empty($ping['error'])) { ?>
					<?php if(!empty($favicon)) { ?>
					<?php if ($SHOW_FAVICON == "on") { ?>
						<tr>
							<td><b>Favicon</b></td>
							<td><img src='<?php echo $favicon; ?>' width="64px" height="64px" style="float:left;"/></td>
						</tr>
					<?php } ?>
					<?php } ?>
					<?php } ?>
					</tbody>
				</table>
			</div>
				
			<div id="players" class="col-md-8" style="font-size:0px;">
				<div id="serverselector">
					Available server:
					<?php
						foreach ($servers as $key => $value) {
							echo('<a class="btn btn-default" href="?server='. $key .'" role="button">'. $key .'</a>');
						}
					?>
					<hr>
				</div>
			</div>
		</div>
	</div>
	
	</body>
	
</html>