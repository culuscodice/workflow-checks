<?php

use App\Commands\BranchNameConvention;
use App\Commands\CommitMessages;
use App\Services\GithubActionConfig;
use App\Services\GithubApiCommands;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application('Workflow Checks');

$config = new GithubActionConfig();
$commands = new GithubApiCommands($config);

$app->add(new CommitMessages($config, $commands));
$app->add(new BranchNameConvention($config, $commands));
$app->run();
