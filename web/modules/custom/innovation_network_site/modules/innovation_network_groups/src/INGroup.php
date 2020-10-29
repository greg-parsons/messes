<?php

namespace Drupal\innovation_network_groups;

use Drupal\group\Entity\Group;
use Drupal\Core\Url;

class INGroup {

    function __construct($group_id) {

        $this->group_id = $group_id;
        $this->group = Group::load($group_id);
        $this->current_user = \Drupal::currentUser();

    }

    /*
    * Get the count of all members in a group
    */
    public function getMemberCount() {
        $group_members = $this->group->getMembers();
        return count($group_members);
    }

    /*
    * Get the links for the tabs on a group landing page and set one as active
    * Some links are hidden for non members of closed groups
    */
    public function getTabLinks() {
        $tabs = array();
        $current_route = \Drupal::routeMatch()->getRouteName();

        switch($current_route) {
            // For group content, the route name does not contain the type of content.
            // This gets the current type in the form (group typeslug )-group_node-(group content type slug), eg group-group_node-discussion
            case 'entity.group_content.canonical':
                $current_route = \Drupal::routeMatch()->getParameter('group_content')->getGroupContentType()->id();
            break;
            // Create node form does not specify content type.
            // This gets the current type in the form group_node:(group content type slug), eg group_node:discussion
            case 'entity.group_content.create_form':
                $current_route = \Drupal::routeMatch()->getParameter('plugin_id');
            break;
            // Edit node form does not specify content type.
            // This gets the current type in the form node_edit_form:(group content type slug), eg node_edit_form:discussion
            case 'entity.node.edit_form':
                $current_route = 'node_edit_form:' . \Drupal::routeMatch()->getParameter('node')->getType();
            break;
            // Delete node form does not specify content type.
            // This gets the current type in the form node_delete_form:(group content type slug), eg node_delete_form:discussion
            case 'entity.node.delete_form':
                $current_route = 'node_delete_form:' . \Drupal::routeMatch()->getParameter('node')->getType();
            break;
        }

        // For some reason, events and discussions in closed and hidden groups got a random string slug instead of a named slug

        // First item in routes array should be route to link to. Other items should be pages that need this tab highlighted as the active item
        $tab_types = array(
            array(
                'title' => t('Information'),
                'routes' => array(
                    'entity.group.canonical',
                    'entity.group.edit_form',
                    'view.group_members.page_1',
                    'view.group_pending_members.page_1',
                    'entity.group_content.group_approve_membership',
                    'group-group_membership',
                    'entity.group_content.edit_form',
                    $this->group->getGroupType()->id() . '-group_membership',
                    'entity.group_content.add_form',
                    'innovation_network_groups.email_members',
                    'entity.group.leave',
                    'entity.group.join'
                ),
                'permission' => 'view group',
                'icon' => 'info-circle'
            ),
            array(
                'title' => t('Group events'),
                'routes' => array(
                    'view.group_events.page_1',
                    $this->group->getGroupType()->id() . '-group_node-group_event',
                    'group_node:group_event',
                    'node_edit_form:group_event',
                    'node_delete_form:group_event',
                    'group_content_type_d3fcf6d4272a8', // Events in closed groups
                    'group_content_type_3c6bff42a2722'  // Events in hidden groups
                ),
                'permission' => 'view group_node:group_event entity',
                'icon' => 'calendar-event'
            ),
            array(
                'title' => t('Discussions'),
                'routes' => array(
                    'view.group_discussions.page_1',
                    $this->group->getGroupType()->id() . '-group_node-discussion',
                    'group_node:discussion',
                    'node_edit_form:discussion',
                    'node_delete_form:discussion',
                    'group_content_type_bf33df57c91c6', // Discussions in closed groups
                    'group_content_type_a767729b68a05'  // Discussions in hidden groups
                ),
                'permission' => 'view group_node:discussion entity',
                'icon' => 'message-2'
            ),
            array(
                'title' => t('Resources'),
                'routes' => array(
                    'view.group_resources.page_1',
                    $this->group->getGroupType()->id() . '-group_node-resource',
                    'group_node:resource',
                    'node_edit_form:resource',
                    'node_delete_form:resource'
                ),
                'permission' => 'view group_node:resource entity',
                'icon' => 'folder'
            )
        );

        foreach( $tab_types as $type ) {
            $tabs[] = array(
                'text' => $type['title'],
                'url' => Url::fromRoute($type['routes'][0], ['group' => $this->group_id]),
                'visible' => $this->group->hasPermission($type['permission'], $this->current_user),
                'is_active' => in_array($current_route, $type['routes']),
                'icon' => $type['icon']
            );
        }

        return $tabs;
    }

    /*
    * Get the links for the 'Manage Group' menu for group owners
    */
    public function getGroupAdminLinks() {
        $manage_links = array();

        if($this->group->hasPermission('edit group', $this->current_user)) {
            $manage_links[] = array(
                'text' => t('Edit group details'),
                'url' => Url::fromRoute('entity.group.edit_form', ['group' => $this->group_id])
            );
        }

        if($this->group->hasPermission('administer members', $this->current_user)) {
            $manage_links[] = array(
                'text' => t('Manage members'),
                'url' => Url::fromRoute('view.group_members.page_1', ['group' => $this->group_id])
            );

            if($this->group->getGroupType()->id() == 'closed_group') {

                $countPendingRequests = $this->getCountRequests('pending');

                $manage_links[] = array(
                    'text' => t('Manage membership requests'),
                    'url' => Url::fromRoute('view.group_pending_members.page_1', ['group' => $this->group_id]),
                    'notification_count' => $countPendingRequests
                );
            }

            $manage_links[] = array(
                'text' => t('Email group'),
                'url' => Url::fromRoute('innovation_network_groups.email_members', ['group' => $this->group_id])
            );
        }

        return count($manage_links) > 0 ? $manage_links : false;
    }

    /*
    * Get the count of a particular type of membership request on a closed group, or all requests.
    */
    public function getCountRequests($type = null) {
        $status_counts = array(
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        );

        $membership_requests = $this->group->getContent('group_membership_request');

        if(!empty($membership_requests)) {
            foreach($membership_requests as $request) {
                $status = (int) $request->grequest_status->value;
                if($status == 0) {
                    $status_counts['pending']++;
                } elseif($status == 1) {
                    $status_counts['approved']++;
                } elseif($status == 2) {
                    $status_counts['rejected']++;
                }
            }
        }

        return !empty($type) && isset($status_counts[$type]) ? $status_counts[$type] : count($membership_requests);
    }

    /*
    * Get all members, or members of a particular role, with a limit
    */
    public function getMembers($limit = 0, $role = null) {
        // Roles are created as (group_type)-(role_name), eg closed_group-group_owner
        // Expectation is that role is passed in just as the role_name
        $role = !empty($role) ? $this->group->getGroupType()->get('id') . '-' . $role : $role;

        $members = $this->group->getMembers($role);

        return ($limit > 0 && count($members) > $limit) ? array_slice($members, 0, $limit) : $members;
    }

    /*
    * Checks the users membership and group type to return the correct CTA link
    */
    public function getCurrentUserCTALink() {
        $group_type = $this->group->getGroupType()->get('id');

        $icon = 'plus';

        if($this->isMember()) {

            $text = t('Leave');
            $icon = 'minus';
            $url = new Url('entity.group.leave', ['group' => $this->group_id]);

        } else {

            $text = t('Join now');

            if($group_type == 'closed_group') {

                // Check if user has pending membership request
                if($this->isPendingMember()) {
                    $text = t('Pending');
                    $url = false;
                } else {
                    $text = t('Request');
                    $url = $this->group->toUrl('group-request-membership');
                }

            } else {
                $url = new Url('entity.group.join', ['group' => $this->group_id]);
            }
        }

        $cta = array(
            'url' => $url,
            'text' => $text,
            'icon' => $icon
        );

        return $cta;
    }

    /**
     * Get the group membership "status" of the current user
     *
     * @return string The group membership "status" of the current user
     */
    public function getMemberStatus() {
        if ($this->isMember()) return 'member';

        if ($this->isPendingMember()) return 'pending';

        if ($this->group->getGroupType()->get('id') === 'closed_group') return 'request';

        return '';
    }

    /**
     * Is the current user a member of the current group
     *
     * @return boolean If the current user is a member of the current group
     */
    private function isMember() {
        return (bool) $this->group->getMember($this->current_user);
    }

    /**
     * Is the current user a pending member of the current group
     *
     * @return boolean If the current user is a pending member of the current group
     */
    private function isPendingMember() {
        if ($this->group->getGroupType()->get('id') === 'closed_group') {
            return (bool) $this->group->getContentByEntityId('group_membership_request', $this->current_user->id());
        }

        return false;
    }

}
