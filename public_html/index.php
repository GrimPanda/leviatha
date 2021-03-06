<?php

// Application Configuration
include "../config.php";

// PHP Fat Free Framework (http://fatfree.sourceforge.net/)
require_once (__SITE_PATH . "/library/F3/lib/base.php");

// Framework Configuration and Database Info
require_once (__SITE_PATH . "/F3Config.php");

// Autoload Assets
F3::set('AUTOLOAD',
    __SITE_PATH . "/application/controllers/|" .
    __SITE_PATH . "/application/models/|" .
    __SITE_PATH . "/library/F3/lib/"
);

// Framework Variables
F3::set('GUI', __SITE_PATH . "/application/views/");

// Application Routes
F3::route('GET /', "RootController->get");
F3::route('GET /about', "RootController->about");
F3::route('GET /loot', "LootDirectoryController->get",604800);
F3::route('GET /loot/@item', "LootController->get",3600);
F3::route('GET /search', "SearchController->get");

F3::route('GET /ajax/loot', "AjaxController->loot");

// Let's Roll Out, Autobots!
F3::run();
