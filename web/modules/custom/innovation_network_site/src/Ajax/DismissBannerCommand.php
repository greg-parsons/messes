<?php

namespace Drupal\innovation_network_site\Ajax;
use Drupal\Core\Ajax\CommandInterface;

class DismissBannerCommand implements CommandInterface {

    protected $banner_id;

    public function __construct($banner_id, $banner_type) {
        $this->banner_id = $banner_id;
        $this->banner_type = $banner_type;
    }

    public function render() {
        return array(
            'command' => 'dismissBanner',
            'banner_id' => $this->banner_id,
            'banner_type' => $this->banner_type
        );
    }
}