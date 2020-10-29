<?php

namespace Drupal\innovation_network_site\Plugin\search_api\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Plugin\PluginFormTrait;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\node\NodeInterface;
use \DateTime;

/**
 * Filters out users based on their role.
 *
 * @SearchApiProcessor(
 *   id = "node_filter",
 *   label = @Translation("Node filter"),
 *   description = @Translation("Filters out nodes for certain conditions"),
 *   stages = {
 *     "alter_items" = 0,
 *   },
 * )
 */
class NodeFilter extends ProcessorPluginBase implements PluginFormInterface {

  use PluginFormTrait;

  /**
   * Can only be enabled for an index that indexes the user entity.
   *
   * {@inheritdoc}
   */public static function supportsIndex(IndexInterface $index) {
    foreach ($index->getDatasources() as $datasource) {
      if ($datasource->getEntityTypeId() == 'node') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */public function alterIndexedItems(array &$items) {
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $node = $item->getOriginalObject()->getValue();
      if (!($node instanceof NodeInterface)) {
        continue;
      }

      // Remove any events or group events that have a date in the past
      if ($node->bundle() == 'event' || $node->bundle() == 'group_event') {
        if ($node->hasField('field_event_date')) {
          $event_date = $node->get('field_event_date')->getValue();

          if (isset($event_date[0]['value'])) {
            $event_date_time = new DateTime( $event_date[0]['value'] );
            $now = new DateTime('now');

            if ($event_date_time < $now) {
              unset($items[$item_id]);
            }
          }

        }
      }

      // Remove any group event from a closed or hidden group
      if($node->bundle() == 'group_event') {
        if(function_exists('in_get_group_from_node')) {
          $group = in_get_group_from_node($node);
          $group_type = $group->getGroupType()->id();
          if($group_type == 'closed_group' || $group_type == 'hidden_group') {
            unset($items[$item_id]);
          }
        } 
      }

    }
  }

}