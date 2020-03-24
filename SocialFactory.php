<?php

namespace App;

require_once 'SocialNetworks/VkWorker.php';

use App\SocialNetworks\TaskManagerInterface;
use App\SocialNetworks\VkWorker;

class SocialFactory
{

    public function generate($socialNetwork): TaskManagerInterface
    {

        switch ($socialNetwork) {

            case 'vk':
                $social = new VkWorker();
                break;

            default:
                throw new \Exception('Unknown type');


        }

        return $social;

    }

}