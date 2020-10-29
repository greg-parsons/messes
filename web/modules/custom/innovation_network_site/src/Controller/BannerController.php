<?php

/**
 * @file
 * Contains \Drupal\innovation_network_site\Controller\BannerController.
 */

namespace Drupal\innovation_network_site\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\user\Entity\User;
use Drupal\innovation_network_site\Ajax\DismissBannerCommand;

class BannerController extends ControllerBase {

    function __construct() {
        $current_user = \Drupal::currentUser();
        $user = User::load($current_user->id());

        $this->user = $user;
    }

    public function dismissBannerCallback($banner_id, $banner_type) {

        $user_data = $this->getUserData();

        // Should be block_53_19 or banner_5 for example
        // The block ones refer to the paragraph id combined with the node or block id that the paragraph is placed on
        // Banner ones are the alert banner content type
        $data_to_save = $banner_type . '_' . $banner_id;

        if(!in_array($data_to_save, $user_data)) {
            $user_data[] = $data_to_save;

            $this->user->set('field_dismissed_messages', implode(',', $user_data));
            $this->user->save();
        }

        $response = new AjaxResponse();

        $response->addCommand(new DismissBannerCommand($banner_id, $banner_type));

        return $response;
    }

    public function hasUserSeenBanner($banner_id, $banner_type) {
        $banners_user_has_seen = $this->getUserData();

        $banner_to_check = $banner_type . '_'. $banner_id;

        return in_array($banner_to_check, $banners_user_has_seen);
    }

    private function getUserData() {

        $dismissed_messages = $this->user->get('field_dismissed_messages')->value;

        if($dismissed_messages) {
            $dismissed_messages = explode(',', $dismissed_messages);

            return $dismissed_messages;
        }

        return [];

    }

}