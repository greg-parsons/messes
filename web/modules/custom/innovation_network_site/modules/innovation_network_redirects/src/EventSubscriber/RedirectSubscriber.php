<?php

namespace Drupal\innovation_network_redirects\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class RedirectSubscriber implements EventSubscriberInterface {

    public function checkForRedirection(GetResponseEvent $event) {
        /*
        * For the group content types, check if it has a group content URL, and redirect the node view to that if so
        */
        $current_route = \Drupal::routeMatch()->getRouteName();

        $content_types_to_redirect = array(
            'discussion',
            'resource',
            'group_event'
        );

        $attr = $event->getRequest()->attributes;

        if(
            $attr !== NULL
            && $attr->get('node') !== NULL
            && in_array($attr->get('node')->get('type')->getString(), $content_types_to_redirect)
            && $current_route === 'entity.node.canonical') {

            $group_content_url = in_get_group_content_url_from_node( $attr->get('node') );

            if(!empty($group_content_url)) {
                $event->setResponse(new RedirectResponse($group_content_url));
            }
        }
    }

    /**
    * {@inheritdoc}
    */
    static function getSubscribedEvents() {
        $events[KernelEvents::REQUEST][] = array('checkForRedirection');
        return $events;
    }

}