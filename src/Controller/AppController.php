<?php

namespace NodeLink\GitDeploy\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;

class AppController extends Controller
{

    /**
     * Deploy method
     *
     * @return \Cake\Http\Response|void
     * @throws NotFoundException|BadRequestException|InternalErrorException
     */
    public function deploy()
    {
        if (!Configure::read('GitDeploy.enable')) {
            throw new NotFoundException();
        }

        $this->authenticateWithToken(Configure::read('GitDeploy.token'));

        $result = $this->request->input('json_decode');

        if (empty($result->ref) || $result->ref !== 'refs/heads/' . Configure::read('GitDeploy.branch')) {
            throw new BadRequestException(__('Pushed branch does not match.'));
        }

        chdir(ROOT);

        exec(Configure::read('GitDeploy.git_path', 'git') . ' pull 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            Log::error(implode("\n", $output));
            throw new InternalErrorException(__('Failed to execute "git pull" command.'));
        }

        exec(Configure::read('GitDeploy.composer_path', 'composer') . ' install --no-interaction 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            Log::error(implode("\n", $output));
            throw new InternalErrorException(__('Failed to execute "composer install" command.'));
        }

        exec('bin' . DS . 'cake cache clear_all', $output, $returnVar);
        if ($returnVar !== 0) {
            Log::error(implode("\n", $output));
            throw new InternalErrorException(__('Failed to execute "bin/cake cache clear_all" command.'));
        }

        Log::debug(implode("\n", $output));
    }

    /**
     * @param string $token
     */
    protected function authenticateWithToken($token)
    {
        if ($token) {
            if (
                !$this->checkForGitHubSignature($token) &&
                !$this->checkForGitLabToken($token) &&
                !$this->checkForGetParameterToken($token)
            ) {
                throw new BadRequestException(__('No token detected.'));
            }
        }
    }

    /**
     * @param string $token
     * @return bool
     */
    protected function checkForGitHubSignature($token)
    {
        if ($hubSignature = $this->request->getEnv('HTTP_X_HUB_SIGNATURE')) {
            list($algorithm, $requestToken) = explode('=', $hubSignature, 2) + ['', ''];
            if ($requestToken !== hash_hmac($algorithm, $this->request->input(), $token)) {
                throw new BadRequestException(__('X-Hub-Signature does not match.'));
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $token
     * @return bool
     */
    protected function checkForGitLabToken($token)
    {
        if ($requestToken = $this->request->getEnv('HTTP_X_GITLAB_TOKEN')) {
            if ($requestToken !== $token) {
                throw new BadRequestException(__('X-GitLab-Token does not match.'));
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $token
     * @return bool
     */
    protected function checkForGetParameterToken($token)
    {
        if ($requestToken = $this->request->getQuery('token')) {
            if ($requestToken !== $token) {
                throw new BadRequestException(__('Get parameter token does not match.'));
            }

            return true;
        }

        return false;
    }

}
