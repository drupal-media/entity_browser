<?php

namespace Drupal\entity_browser\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\entity_browser\Entity\EntityBrowser;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an Entity Browser form element.
 *
 * Properties:
 * - #entity_browser: Entity browser or ID of the Entity browser to be used.
 * - #cardinality: (optional) Maximum number of items that are expected from
 *     the entity browser. Unlimited by default.
 * - #default_value: (optional) Array of entities that Entity browser should be
 *     initialized with.
 * - #entity_browser_validators: (optional) Array of validators that are to be
 *     passed to the entity browser. Array keys are plugin IDs and array values
 *     are plugin configuration values. Cardinality validator will be set
 *     automatically.
 * - #selection_mode: (optional) Determines whether newly added entities get
 *     prepended on top or are appended to the bottom of the list. Defaults to
 *     append.
 *
 * Return value will be an array of selected entities, which will appear under
 * 'entities' key on the root level of the element's values in the form state.
 *
 * @FormElement("entity_browser")
 */
class EntityBrowserElement extends FormElement {

  /**
   * Indicating an entity browser can return an unlimited number of values.
   */
  const CARDINALITY_UNLIMITED = -1;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#cardinality' => $this::CARDINALITY_UNLIMITED,
      '#process' => [[$class, 'processEntityBrowser']],
      '#default_value' => [],
      '#entity_browser_validators' => [],
      '#attached' => ['library' => ['entity_browser/common']],
      '#selection_mode' => 'append',
    ];
  }

  /**
   * Render API callback: Processes the entity browser element.
   */
  public static function processEntityBrowser(&$element, FormStateInterface $form_state, &$complete_form) {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
    if (is_string($element['#entity_browser'])) {
      $entity_browser = EntityBrowser::load($element['#entity_browser']);
    }
    else {
      $entity_browser = $element['#entity_browser'];
    }

    $default_value = implode(' ', array_map(
      function (EntityInterface $item) {
        return $item->getEntityTypeId() . ':' . $item->id();
      },
      $element['#default_value']
    ));
    $validators = array_merge(
      $element['#entity_browser_validators'],
      ['cardinality' => ['cardinality' => $element['#cardinality']]]
    );

    $display = $entity_browser->getDisplay();
    $display->setUuid(sha1(implode('-', array_merge([$complete_form['#build_id']], $element['#parents']))));
    $element['entity_browser'] = [
      '#eb_parents' => array_merge($element['#parents'], ['entity_browser']),
    ];
    $element['entity_browser'] = $display->displayEntityBrowser(
      $element['entity_browser'],
      $form_state,
      $complete_form,
      ['validators' => $validators, 'selected_entities' => $element['#default_value']]
    );

    $hidden_id = Html::getUniqueId($element['#id'] . '-target');
    $element['entity_ids'] = [
      '#type' => 'hidden',
      '#id' => $hidden_id,
      // We need to repeat ID here as it is otherwise skipped when rendering.
      '#attributes' => ['id' => $hidden_id, 'class' => ['ed-target']],
      '#default_value' => $default_value,
    ];

    $element['#attached']['drupalSettings']['entity_browser'] = [
      $entity_browser->getDisplay()->getUuid() => [
        'cardinality' => $element['#cardinality'],
        'selector' => '#' . $hidden_id,
        'selectionMode' => $element['#selection_mode'],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      return $element['#default_value'] ?: [];
    }

    $entities = [];
    if ($input['entity_ids']) {
      $entities = static::processEntityIds($input['entity_ids']);
    }

    return ['entities' => $entities];
  }

  /**
   * Processes entity IDs and gets array of loaded entities.
   *
   * @param array|string $ids
   *   Processes entity IDs as they are returned from the entity browser. They
   *   are in [entity_type_id]:[entity_id] form. Array of IDs or a
   *   space-delimited string is supported.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Array of entity objects.
   */
  public static function processEntityIds($ids) {
    if (!is_array($ids)) {
      $ids = array_filter(explode(' ', $ids));
    }

    return array_map(
      function ($item) {
        list($entity_type, $entity_id) = explode(':', $item);
        return \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
      },
      $ids
    );
  }

  /**
   * Processes entity IDs and gets array of loaded entities.
   *
   * @param string $id
   *   Processes entity ID as it is returned from the entity browser. ID should
   *   be in [entity_type_id]:[entity_id] form.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity object.
   */
  public static function processEntityId($id) {
    $return = static::processEntityIds([$id]);
    return current($return);
  }

}
