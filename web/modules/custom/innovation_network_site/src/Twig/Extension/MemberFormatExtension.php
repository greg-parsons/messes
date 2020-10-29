<?php

namespace Drupal\innovation_network_site\Twig\Extension;

class MemberFormatExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'memberFormat_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('memberFormat', array($this, 'memberFormat')),
        );
    }

    public function memberFormat($count)
    {
        if( $count == 1 ) {
            $value = t('@count member', [
                '@count' => $count
            ]);
        } else {
            $value = t('@count members', [
                '@count' => $count
            ]);
        }

        return $value;
    }
}
