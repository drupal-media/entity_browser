<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReference.
 */

namespace Drupal\entity_browser\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'entity_reference' widget for entity browser.

 * @FieldWidget(
 *   id = "entity_browser_entity_reference",
 *   label = @Translation("Entity browser"),
 *   description = @Translation("Uses entity browser to select entities."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReference extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    return [
      'entity_browser' => \Drupal::entityManager()->getStorage('entity_browser')->load('iframe_test')->getDisplay()->displayEntityBrowser(),
      'target_id' => [
        '#type' => 'hidden',
      ],
    ];
  }

}
