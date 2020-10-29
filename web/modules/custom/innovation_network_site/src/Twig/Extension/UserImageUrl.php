<?php

namespace Drupal\innovation_network_site\Twig\Extension;

class UserImageUrl extends \Twig_Extension
{
    public function getName()
    {
        return 'userImageUrl_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('userImageUrl', array($this, 'userImageUrl')),
        );
    }

    public function userImageUrl($user_id)
    {

        $user_id = (int) $user_id;

        $user = \Drupal\user\Entity\User::load($user_id);

        if(!$user->user_picture->isEmpty()) {
            $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');
            $user_image_url = $style->buildUrl($user->user_picture->entity->getFileUri());

            return $user_image_url;
        }

        return false;
    }
}
