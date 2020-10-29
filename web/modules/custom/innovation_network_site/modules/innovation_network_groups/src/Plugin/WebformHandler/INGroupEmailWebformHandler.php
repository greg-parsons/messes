<?php 
namespace Drupal\innovation_network_groups\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\innovation_network_groups\INGroup;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "email_group_members",
 *   label = @Translation("Group Member Email"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends an email to all group members."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */

class INGroupEmailWebformHandler extends EmailWebformHandler {

    public function sendMessage(WebformSubmissionInterface $webform_submission, array $message) {
        // Set the to and reply to as the group owner who triggered the email, and the from as the site email
        $site_mail = \Drupal::config('system.site')->get('mail');

        $submission_creator = $webform_submission->getOwner();
        $to_email = $submission_creator->getEmail();

        $message['to_mail'] = $to_email;
        $message['reply_to'] = $to_email;
        $message['from_mail'] = $site_mail;

        // Get the group out of the form submission
        $form_data = $webform_submission->getData();

        $group_id = isset($form_data['group']) ? $form_data['group'] : false;
        $subject = isset($form_data['subject']) ? $form_data['subject'] : t('Group notification');

        $message['subject'] = $subject;

        if($group_id) {
            $group = new INGroup($group_id);

            // Make sure the user who filled out the form is indeed an owner of the group for this form
            if($this->isGroupOwner($submission_creator, $group)) {
                $members = $group->getMembers();

                if($members) {
                    $bcc_emails = [];

                    // Get all emails except for the user who filled out the form, to set as the BCC field
                    foreach($members as $member) {
                        $user = $member->getUser();
                        if($user !== $submission_creator) {
                            $bcc_emails[] = $user->getEmail();
                        }
                    }

                    if(count($bcc_emails) > 0) {
                        $message['bcc_mail'] = implode(', ', $bcc_emails);
                    }
                }
            }
        }

        parent::sendMessage($webform_submission, $message);
    }

    private function isGroupOwner($user, $group) {
        $group_owners = $group->getMembers(0, 'group_owner');
        if($group_owners) {
            foreach($group_owners as $owner) {
                if($owner->getUser() == $user) {
                    return true;
                }
            }
        }

        return false;
    }
}