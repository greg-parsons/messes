<?php

namespace Drupal\innovation_network_site\Twig\Extension;

class SlugifyExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'slugify_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('slugify', array($this, 'slugify')),
        );
    }

    public function slugify($value)
    {
        $value = trim(mb_convert_case($value, MB_CASE_LOWER));
        $value = preg_replace('/[^a-z0-9]/', '-', $value);
        $value = preg_replace('/--/', '-', $value);
        $value = trim($value, '-');

        return $value;
    }
}
