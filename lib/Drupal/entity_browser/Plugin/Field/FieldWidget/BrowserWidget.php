<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Plugin\Field\FieldWidget\BrowserWidget.
 */

namespace Drupal\entity_browser\Plugin\Field\FieldWidget;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;


/**
 * Plugin implementation of the 'entity_browser browser' widget.
 *
 * @FieldWidget(
 *   id = "entity_browser_browser",
 *   label = @Translation("Entity browser"),
 *   description = @Translation("A popup entity browser."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class BrowserWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'match_operator' => 'CONTAINS',
      'size' => '60',
      'autocomplete_type' => 'tags',
      'placeholder' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function elementValidate($element, &$form_state, $form) {
    $value = array();
    // If a value was entered into the autocomplete.
    $handler = \Drupal::service('plugin.manager.entity_reference.selection')->getSelectionHandler($this->fieldDefinition);
    $bundles = entity_get_bundles($this->getFieldSetting('target_type'));

    if (!empty($element['#value'])) {
      $value = array();
      foreach (Tags::explode($element['#value']) as $input) {
        $match = FALSE;

        // Take "label (entity id)', match the ID from parenthesis when it's a
        // number.
        if (preg_match("/.+\((\d+)\)/", $input, $matches)) {
          $match = $matches[1];
        }
        // Match the ID when it is the only thing
        elseif (preg_match('/^\d+$/', $input, $matches)) {
          $match = $matches[0];
        }
        // Match the ID when it's a string (e.g. for config entity types).
        elseif (preg_match("/.+\(([\w.]+)\)/", $input, $matches)) {
          $match = $matches[1];
        }
        else {
          // Try to get a match from the input string when the user didn't use
          // the autocomplete but filled in a value manually.
          $match = $handler->validateAutocompleteInput($input, $element, $form_state, $form, FALSE);
        }

        if ($match) {
          $value[] = array('target_id' => $match);
        }
      }
    };

    // Change the element['#parents'], so in form_set_value() we
    // populate the correct key.
    array_pop($element['#parents']);
    form_set_value($element, $value, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $entity = $items->getEntity();

    $element += array(
      '#type' => 'textfield',
      '#maxlength' => 1024,
      '#default_value' => implode(', ', $this->getLabels($items, $delta)),
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#element_validate' => array(array($this, 'elementValidate')),
      'browser' => array(
        '#markup' => '<a class="entity_browser-open">' . t('Open browser') . '</a>',
      ),
      '#attached' => array(
        'library' => array('entity_browser/drupal.entity_browser'),
      ),
    );

    return array('target_id' => $element);
  }

  /**
   * Gets the entity labels.
   */
  protected function getLabels(FieldItemListInterface $items, $delta) {
    if ($items->isEmpty()) {
      return array();
    }

    $entity_labels = array();

    // Load those entities and loop through them to extract their labels.
    $entities = entity_load_multiple($this->getFieldSetting('target_type'), $this->getEntityIds($items, $delta));

    foreach ($entities as $entity_id => $entity_item) {
      $label = $entity_item->label();
      $key = "$label ($entity_id)";
      // Labels containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $entity_labels[] = $key;
    }
    return $entity_labels;
  }


}
