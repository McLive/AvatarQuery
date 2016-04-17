<?php

/**
 * Turn on for debugging. Will print possible errors on your page.
 */
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);


/**
 * Servers to query. Will show avatars of connected players on the right side.
 * Set status 'false' if you don't want playercounts on the left side.
 */
$servers = array(
    'Skyblock' => array(
        'ip' => 'sky.freecraft.eu',
        'port' => '25555',
        'status' => true
    ),
    'Survival' => array(
        'ip' => 's.freecraft.eu',
        'port' => '25566',
        'status' => true
    ),
    'Creative' => array(
        'ip' => 's.freecraft.eu',
        'port' => '25559',
        'status' => true
    )
);

/**
 * Bungee server to ping and show information on the left side. Can be the same as one of the servers defined above.
 * Needed to get the favicon, motd, players online/max and version.
 */
$bungee = array(
    'ip' => 'lobby.freecraft.eu',
    'port' => '25565'
);

/**
 * Settings
 */
$SHOW_FAVICON = true; # Show the favicon? - true, false

$TITLE = "FreeCraft";
$TITLE_LEFT = "General Information";
$TITLE_LEFT_DOWN = "Server Information";
$TITLE_RIGHT = "Players";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * No need to do something below here.
 */
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (empty($_GET['server'])) {
    $server = key($servers);
} else {
    $server = $_GET['server'];
}

if (empty($servers[$server]['ip'])) {
    $ip = reset($servers)['ip'];
    $port = reset($servers)['port'];
} else {
    $ip = $servers[$server]['ip'];
    $port = $servers[$server]['port'];
}

$ping_url = "https://api.minetools.eu/ping/" . $bungee['ip'] . "/" . $bungee['port'];
$query_url = "https://api.minetools.eu/query/" . $ip . "/" . $port;

$ping = json_decode(file_get_contents($ping_url), true);

//Put the collected player information into an array for later use.
if (empty($ping['error'])) {
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

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Lato:300,400" rel="stylesheet" type="text/css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.js"></script>
    <script src="bootstrap-notify.js"></script>

    <script language="javascript">
        jQuery(document).ready(function () {
            $('body').tooltip({
                selector: '[rel=tooltip]'
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // http://bootstrap-notify.remabledesigns.com/

            var ping_url = "<?php echo $ping_url; ?>";
            var query_url = "<?php echo $query_url; ?>";

            var servers = <?php echo json_encode($servers); ?>;

            players = [];


            refresh();
            query(false);

            setInterval(function () {
                refresh();
                updateServer();
                query(true);
            }, 500);


            function display_notify(name, event, color) {
                $.notify({
                    // options
                    icon: '//cravatar.eu/helmhead/' + name + '/64',
                    message: name + ' ' + event
                }, {
                    // settings
                    type: color,
                    timer: 1000,
                    placement: {
                        from: "bottom",
                        align: "right"
                    },
                    icon_type: 'img'
                });
            }

            function refresh() {
                $.get(ping_url, function (data) {
                    $(".playersonline").text(data['players']['online'] + '/' + data['players']['max'])
                });
            }

            function updateServer() {
                for (var srv in servers) {
                    if (servers.hasOwnProperty(srv)) {
                        var server = servers[srv];
                        if (server['status'] == false) {
                            return;
                        }

                        var url = "https://api.minetools.eu/ping/" + server['ip'] + "/" + server['port'];
                        pingServer(url, srv);
                    }
                }
            }

            function pingServer(url, srv) {
                $.get(url, function (data) {
                    //$("#status-" + srv).text(data['players']['online'] + '/' + data['players']['max']);
                    var status = data['error'];

                    if (!status) {
                        var online = data['players']['online'];
                        var max = data['players']['max'];
                        var percent = online / max * 100;
                    }
                    updateProgess(online, max, percent, srv, status);
                });
            }

            function updateProgess(online, max, percent, srv, status) {
                $('.progress-bar#' + srv).css('width', percent + '%').attr('aria-valuenow', percent).text(online);

                if (status) {
                    $('td#' + srv).html('<i class="fa fa-times-circle text-danger"></i> Server is offline').css('width', '100px');
                }
            }

            function query(notify) {
                $.get(query_url, function (data) {
                    all = data['Playerlist'];

                    for (player in all) {
                        if ($.inArray(all[player], players) == "-1") {
                            console.log("Added " + all[player]);
                            players.push(all[player]);

                            $('#players').append(
                                '<a data-placement="top" rel="tooltip" style="display: inline-block;" title="' + all[player] + '">'
                                +
                                '<img id="' + all[player] + '" src="//cravatar.eu/avatar/' + all[player] + '/50" style="margin-bottom: 5px; margin-right: 5px; border-radius: 3px; ">'
                                +
                                '</a>'
                            );

                            if (notify) {
                                display_notify(all[player], "joined", "success")
                            }

                        }
                    }

                    for (player in players) {
                        if ($.inArray(players[player], all) == "-1") {
                            console.log("Removed " + players[player]);
                            $('#' + players[player]).remove();

                            display_notify(players[player], "left", "danger");

                            for (var i = 0; i < players.length; i++) {
                                if (players[i] === players[player]) {
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
    <h1><?php echo htmlspecialchars($TITLE); ?></h1>
    <hr>
    <div class="row">
        <div class="col-md-4">
            <h3><?php echo htmlspecialchars($TITLE_LEFT); ?></h3>
            <table class="table table-striped">
                <tbody>
                <tr>
                    <td><b>IP</b></td>
                    <td><?php echo $bungee['ip']; ?></td>
                </tr>
                <?php if (empty($ping['error'])) { ?>
                    <tr>
                        <td><b>Version</b></td>
                        <td><?php echo $version; ?></td>
                    </tr>
                <?php } ?>
                <?php if (empty($ping['error'])) { ?>
                    <tr>
                        <td><b>Players</b></td>
                        <!-- <td><?php echo "" . $online . " / " . $max . ""; ?></td> -->
                        <td>
                            <div class="playersonline"></div>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><b>Status</b></td>
                    <td>
                        <?php if (empty($ping['error'])) {
                            echo "<i class=\"fa fa-check-circle text-success\"></i> Server is online";
                        } else {
                            echo "<i class=\"fa fa-times-circle text-danger\"></i> Server is offline";
                        } ?>
                    </td>
                </tr>
                <?php if (empty($ping['error'])) { ?>
                    <?php if (!empty($favicon) && $SHOW_FAVICON) { ?>
                        <tr>
                            <td><b>Favicon</b></td>
                            <td><img src='<?php echo $favicon; ?>' width="64px" height="64px" style="float:left;"/>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>

            <h3><?php echo htmlspecialchars($TITLE_LEFT_DOWN); ?></h3>
            <table class="table table-striped">
                <tbody>
                <?php foreach ($servers as $key => $value) {
                    if ($value['status'] == false)
                        break; ?>

                    <tr>
                        <td><b><?php echo $key ?></b></td>
                        <td id="<?php echo $key ?>">
                            <div class="progress progress-striped active pull-right" style="width: 200px;">
                                <div class="progress-bar" id="<?php echo $key ?>" role="progressbar" aria-valuenow="0"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </td>
                    </tr>

                <?php } ?>
                </tbody>
            </table>

        </div>

        <div id="players" class="col-md-8" style="">
            <h3><?php echo htmlspecialchars($TITLE_RIGHT); ?></h3>

            <div id="serverselector">
                <?php
                foreach ($servers as $key => $value) {
                    echo('<a class="btn btn-default" href="?server=' . $key . '" role="button">' . $key . '</a>');
                }
                ?>
                <hr>
            </div>
        </div>
    </div>
</div>

</body>
</html>