<?php

/**
 * @file
 * Contains \Drupal\entity_browser\src\Element\EntityBrowserElement.
 */

namespace Drupal\entity_browser\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use \Drupal\entity_browser\Entity\EntityBrowser;

/**
 * Provides an Entity Browser render element.
 *
 * Configuration options are:
 *
 * @FormElement("entity_browser")
 */
class EntityBrowserElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#process' => [[$class, 'processEntityBrowser']],
      '#size' => 60,
      '#pre_render' => [[$class, 'preRenderEntityBrowser']],
      '#theme' => 'entity_browser',
      '#theme_wrappers' => ['form_element'],
      '#tree' => TRUE,
      '#attached' => [
        'library' => ['entity_browser/entity_reference']
      ],
    ];
  }

  /**
   * Processes the entity browser element.
   */
  public static function processEntityBrowser(&$element, FormStateInterface $form_state, &$complete_form) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    $entity_browser = EntityBrowser::load($element['#entity_browser_id']);

    $element['entity_browser'] = $entity_browser->getDisplay()->displayEntityBrowser();

    if ($element['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED || count($element['#default_value']) < $element['cardinality']) {
      $element['#attached']['drupalSettings']['entity_browser'] = [
        'field_settings' => [
          $entity_browser->getDisplay()->getUuid() => [
            'cardinality' => $element['#cardinality'],
          ],
        ]
      ];
    }

    return $element;
  }

  /**
   * Prepares a #type 'entity_browser' render element for entity_browser.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *
   * @return array
   *   The $element with prepared variables ready for entity-browser.html.twig.
   */
  public static function preRenderEntityBrowser($element) {
    $hidden_id = Html::getUniqueId('edit-' . $element['#identifier'] . '-target-id');
    $details_id = Html::getUniqueId('edit-' . $element['#identifier']);

    $element['#theme_wrappers'][] = [
      '#id' => $details_id,
      '#type' => 'details',
      '#open' => !empty($ids),
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        // We need to repeat ID here as it is otherwise skipped when rendering.
        '#attributes' => ['id' => $hidden_id],
        '#default_value' => $element['#default_value'],
        // #ajax is officially not supported for hidden elements but if we
        // specify event manually it works.
        '#ajax' => [
          'callback' => [self::class, 'updateWidgetCallback'],
          'wrapper' => $details_id,
          'event' => 'entity_browser_value_updated',
        ],
      ],
    ];

    $element['#variables']['entity_browser'] = $element['entity_browser'];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $return = [];
    return $return;
  }

  /**
   * AJAX form callback.
   */
  public static function updateWidgetCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    // AJAX requests can be triggered by hidden "target_id" element when entities
    // are added or by one of the "Remove" buttons. Depending on that we need to
    // figure out where root of the widget is in the form structure and use this
    // information to return correct part of the form.
    if (!empty($trigger['#ajax']['event']) && $trigger['#ajax']['event'] == 'entity_browser_value_updated') {
      $parents = array_slice($trigger['#array_parents'], 0, -2);
    }
    elseif ($trigger['#type'] == 'submit' && strpos($trigger['#name'], '_remove_')) {
      $parents = array_slice($trigger['#array_parents'], 0, -4);
    }

    return NestedArray::getValue($form, $parents);
  }
}
