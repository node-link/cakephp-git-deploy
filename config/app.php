<?php

return [
    'GitDeploy' => [
        'enable' => filter_var(env('GIT_DEPLOY_ENABLE', false), FILTER_VALIDATE_BOOLEAN),
        'token' => env('GIT_DEPLOY_TOKEN', 'secret'),
        'branch' => env('GIT_DEPLOY_BRANCH', 'master'),
        'git_path' => env('GIT_DEPLOY_GIT_PATH', '/usr/bin/git'),
        'composer_path' => env('GIT_DEPLOY_COMPOSER_PATH', '/usr/local/bin/composer')
    ],
];
