<?php

namespace Drupal\innovation_network_groups\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\entity\Group;
use Symfony\Component\HttpFoundation\Request;
use Drupal\innovation_network_groups\INGroup;
use Drupal\webform\Entity\Webform;

class INGroupController extends ControllerBase {

    public function content(Group $group, Request $request) {

        if(empty($group)) {
            return;
        }

        $in_group = new INGroup($group->id());

        $webform_email_form = 'email_group';

        $form_output[$webform_email_form] = [
            '#type' => 'webform',
            '#webform' => $webform_email_form,
            '#default_data' => [
                'group' => $group->id()
            ]
        ];

        $build = [
            '#theme' => 'innovation_network_groups_theme_hook',
            '#title' => t('Email group members'),
            '#form' => $form_output,
        ];

        return $build;
    }

}