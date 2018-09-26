<?php

use Cake\Core\Configure;

try {
    Configure::load('NodeLink/GitDeploy.app', 'default', true);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}
