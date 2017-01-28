<?php
/**
 * Turn on for debugging. Will print possible errors on your page.
 */
ini_set("display_errors", 0);
ini_set("track_errors", 0);
ini_set("html_errors", 0);
error_reporting(E_ERROR);  # E_ALL || E_ERROR


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