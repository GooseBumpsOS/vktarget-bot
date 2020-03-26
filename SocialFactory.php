<?php

namespace App;

require_once 'SocialNetworks/VkWorker.php';

use App\SocialNetworks\TaskManagerInterface;
use App\SocialNetworks\VkWorker;

class SocialFactory
{
    private $auth;

    public function __construct(Auth $auth)
    {

        $this->auth = $auth;

    }

    public function generate($socialNetwork): TaskManagerInterface
    {

        switch ($socialNetwork) {

            case 'vk':
                $social = new VkWorker($this->auth->getVkAuth('token'));
                break;

            default:
                throw new \Exception('Unknown type');


        }

        return $social;

    }

}