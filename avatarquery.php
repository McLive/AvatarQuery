<?php
include_once 'config.php';

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

$query_url = "https://api.minetools.eu/query/" . $ip . "/" . $port;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($TITLE); ?></title>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Lato:300,400" rel="stylesheet" type="text/css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.js" type="text/javascript"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-debug.js"></script>
    <script src="bootstrap-notify.js"></script>

    <script language="javascript">
        jQuery(document).ready(function () {
            $('body').tooltip({
                selector: '[rel=tooltip]'
            });
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
        <!-- ko with: server -->
        <div id="servers" class="col-md-4">
            <h3><?php echo htmlspecialchars($TITLE_LEFT); ?></h3>
            <table class="table table-striped">
                <tbody>
                <tr>
                    <td><b>IP</b></td>
                    <td data-bind="text: bungee.ip"></td>
                </tr>
                <tr>
                    <td><b>Version</b></td>
                    <td data-bind="text: bungee.version"></td>
                </tr>
                <tr>
                    <td><b>Players</b></td>
                    <td><span data-bind="text: bungee.online">1</span>/<span data-bind="text: bungee.max">2</span></td>
                </tr>
                <tr>
                    <td><b>Status</b></td>
                    <td>
                        <span data-bind="if: bungee.isOnline"><i class="fa fa-check-circle text-success"></i> Server is online</span>
                        <span data-bind="if: !bungee.isOnline()"><i class="fa fa-times-circle text-danger"></i> Server is offline</span>
                    </td>
                </tr>
                <?php if ($SHOW_FAVICON) { ?>
                    <tr>
                        <td><b>Favicon</b></td>
                        <td><img data-bind="attr: { src: bungee.favicon()}" src="" width="64px" height="64px" style="float:left;"/></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <h3><?php echo htmlspecialchars($TITLE_LEFT_DOWN); ?></h3>

            <table class="table table-striped">
                <tbody data-bind="foreach: servers">
                <tr>
                    <td><b data-bind="text: name"></b></td>
                    <!-- ko if: isOnline -->
                    <td>
                        <div class="progress progress-striped active pull-right" style="width: 200px;">
                            <div data-bind="text: online, attr: {'aria-valuenow': percent()}, style: {width: percent()}"
                                 class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </td>
                    <!-- /ko -->
                    <!-- ko if: !isOnline() -->
                    <td style="width: 100px;">
                        <i class="fa fa-times-circle text-danger"></i> Server is offline
                    </td>
                    <!-- /ko -->
                </tr>
                </tbody>
            </table>
        </div>
        <!-- /ko -->

        <div class="col-md-8" style="">
            <h3><?php echo htmlspecialchars($TITLE_RIGHT); ?></h3>
            <!-- ko with: server -->
            <div id="servers" data-bind="foreach: servers">
                <a data-bind="text: name, attr: { href: serverUrl()}" class="btn btn-default" role="button" href=""></a>
            </div>
            <hr>
            <!-- /ko -->
            <!-- ko with: player -->
            <div id="players" data-bind="foreach: playersOnline">
                <a data-bind="attr: { title: name}" data-placement="top" rel="tooltip"
                   style="display: inline-block;" title="">
                    <img data-bind="attr: { src: avatar_url()}" id="" src=""
                         style="margin-bottom: 5px; margin-right: 5px; border-radius: 3px;">
                </a>
            </div>
            <!-- /ko -->
        </div>
    </div>
</div>

</body>

<script>
    function Server(name, ip, port, status) {
        var self = this;

        self.name = ko.observable(name);
        self.ip = ko.observable(ip);
        self.port = ko.observable(port);
        self.status = ko.observable(status);

        self.online = ko.observable();
        self.max = ko.observable();
        self.version = ko.observable();
        self.favicon = ko.observable();
        self.isOnline = ko.observable();

        self.percent = ko.computed(function () {
            return ko.unwrap(self.online) / ko.unwrap(self.max) * 100 + "%";
        });

        self.pingUrl = ko.computed(function () {
            return "https://api.minetools.eu/ping/" + ko.unwrap(self.ip) + "/" + ko.unwrap(self.port)
        });
        self.serverUrl = ko.computed(function () {
            return "?server=" + ko.unwrap(self.name)
        });

        self.ping = function () {
            if(self.status == false) {
                return true;
            }
            $.get(self.pingUrl(), function (data) {
                var status = data['error'];

                if (!status) {
                    var online = data['players']['online'];
                    var max = data['players']['max'];
                    var percent = online / max * 100;
                    var version = data['version']['name'];
                    var favicon = data['favicon'];

                    //console.log(self.name() + ":" + a);

                    self.online(online);
                    self.max(max);
                    self.version(version);
                    self.favicon(favicon);
                    self.isOnline(true);
                    return true;
                }
                self.isOnline(false);
            });
        };
    }

    function ServerModel() {
        var self = this;

        self.servers = ko.observableArray([]);
        self.bungee = ko.observable();

        self.addServers = function (servers) {
            for (var srv in servers) {
                if (servers.hasOwnProperty(srv)) {
                    var server = servers[srv];

                    self.servers.push(new Server(srv, server['ip'], server['port'], server['status']));
                }
            }
        };

        self.addBungee = function (server) {
            self.bungee = new Server("Bungeecord", server['ip'], server['port'], false)
        };

        self.updateServers = function () {
            self.servers().forEach(function(item){
                item.ping();
            });
            self.bungee.ping();
        };
    }


    function Player(name) {
        var self = this;

        self.name = ko.observable(name);
        self.avatar_url = ko.computed(function () {
            return "https://cravatar.eu/helmavatar/" + ko.unwrap(self.name) + "/50";
        });
    }

    var PlayerModel = function () {
        var self = this;

        this.playersOnline = ko.observableArray([]);

        this.addPlayer = function (player, notify) {
            var newPlayer = new Player(player.name);

            var match = ko.utils.arrayFirst(this.playersOnline(), function(item) {
                return newPlayer.name() === item.name();
            });

            // Player does not exist in the observableArray so we'll add him
            if(!match) {
                this.playersOnline.push(new Player(player.name));
                console.log("[KO] Added " + player.name);
                if(notify) {
                    display_notify(player.name, "joined", "success")
                }
            }
        };

        /**
         * Check if the playerlist returned from server contains
         * new players so we'll add them to the observableArray.
         */
        this.addNewPlayers = function (players, notify) {
            for (player in players) {
                var name = players[player];
                this.addPlayer({name: name}, notify);
            }
        };

        /**
         * Check if the observableArray still contains players which are no longer online
         * on the server so we'll remove them from the observableArray.
         */
        this.removePlayers = function (players, notify) {
            var self = this;
            this.playersOnline().forEach(function(item){
                if ($.inArray(item.name(), players) == "-1") {
                    self.removePlayer(item);
                    console.log("[KO] Removed " + item.name());

                    if(notify) {
                        display_notify(ko.unwrap(item.name), "left", "danger");
                    }
                }
            });
        };

        this.removePlayer = function (player) {
            this.playersOnline.remove(player);
        };

    };

    ko.bindingHandlers.stopBinding = {
        init: function() {
            return { controlsDescendantBindings: true };
        }
    };

    ko.virtualElements.allowedBindings.stopBinding = true;

    playermodel = new PlayerModel();
    //ko.applyBindings(playermodel, document.getElementById("players"));

    servermodel = new ServerModel();
    servermodel.addServers(<?php echo json_encode($servers); ?>);
    servermodel.addBungee(<?php echo json_encode($bungee); ?>);
    //ko.applyBindings(servermodel, document.getElementById("servers"));

    var viewModel = {
        player: playermodel,
        server: servermodel
    };

    ko.applyBindings(viewModel);

    setInterval(function () {
        query();
        refreshServers();
    }, 500);

    var firstRun = true;
    function query() {
        var notify = !firstRun;
        var query_url = "<?php echo $query_url; ?>";
        $.get(query_url, function (data) {
            if(data['status'] == "OK") {
                players = data['Playerlist'];

                playermodel.addNewPlayers(players, notify);
                playermodel.removePlayers(players, notify);
            } else {
                // console.log(query_url + ": " + data['status'])
            }
        });
        firstRun = false
    }

    function refreshServers() {
        servermodel.updateServers();
    }

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
</script>

</html>