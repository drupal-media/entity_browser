<?php

/**
 * @file
 * Definition of Drupal\entity_browser\Plugin\views\display\EntityBrowser.
 */

namespace Drupal\entity_browser\Plugin\views\display;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\views\Plugin\views\display\DisplayPluginBase;

/**
 * The plugin that handles entity browser display.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "entity_browser",
 *   title = @Translation("Entity browser"),
 *   help = @Translation("Displays a view as Entity browser widget."),
 *   theme = "views_view",
 *   admin = @Translation("Entity browser")
 * )
 */
class EntityBrowser extends DisplayPluginBase {

  /**
   * {@inheritdoc}.
   */
  public function execute() {
    parent::execute();
    $this->has_exposed = FALSE;
    $render = ['view' => $this->view->render()];

    $this->handleForm($render);
    return $render;
  }

  /**
   * {@inheritdoc}.
   */
  public function usesExposedFormInBlock() {
    return TRUE;
  }

  /**
   * {@inheritdoc}.
   */
  protected function defineOptions() {
    // Push users towards exposing filters as a block
    $options = parent::defineOptions();
    $options['exposed_block']['default'] = TRUE;
    return $options;
  }

  /**
   * {@inheritdoc}.
   */
  function preview() {
    return $this->execute();
  }

  /**
   * Pre render callback for a view. Based on DisplayPluginBase::elementPreRender()
   * except that we removed form part which need to handle by our own.
   */
  public function elementPreRender(array $element) {
    $view = $element['#view'];
    $empty = empty($view->result);

    // Force a render array so CSS/JS can be attached.
    if (!is_array($element['#rows'])) {
      $element['#rows'] = ['#markup' => $element['#rows']];
    }

    $element['#header'] = $view->display_handler->renderArea('header', $empty);
    $element['#footer'] = $view->display_handler->renderArea('footer', $empty);
    $element['#empty'] = $empty ? $view->display_handler->renderArea('empty', $empty) : [];
    $element['#exposed'] = !empty($view->exposed_widgets) ? $view->exposed_widgets : [];
    $element['#more'] = $view->display_handler->renderMoreLink();
    $element['#feed_icons'] = !empty($view->feedIcons) ? $view->feedIcons : [];

    if ($view->display_handler->renderPager()) {
      $exposed_input = isset($view->exposed_raw_input) ? $view->exposed_raw_input : NULL;
      $element['#pager'] = $view->renderPager($exposed_input);
    }

    if (!empty($view->attachment_before)) {
      $element['#attachment_before'] = $view->attachment_before;
    }
    if (!empty($view->attachment_after)) {
      $element['#attachment_after'] = $view->attachment_after;
    }

    return $element;
  }

  /**
   * Handles form elements on a view.
   */
  protected function handleForm(&$render) {
    if (!empty($this->view->field['entity_browser_select'])) {
      $this->view->field['entity_browser_select']->viewsForm($render);

      $render['#post_render'][] = [get_class($this), 'postRender'];
      $substitutions = [];
      foreach ($this->view->result as $row_id => $row) {
        $form_element_row_id = $row_id;

        $substitutions[] = [
          'placeholder' => '<!--form-item-entity_browser_select--' . $form_element_row_id . '-->',
          'field_name' => 'entity_browser_select',
          'row_id' => $form_element_row_id,
        ];
      }

      $render['#substitutions'] = [
        '#type' => 'value',
        '#value' => $substitutions,
      ];
    }
  }

  /**
   * Post render callback that moves form elements into the view.
   *
   * Form elements need to be added out of view to be correctly detected by Form
   * API and then added into the view afterwards. Views use the same approach for
   * bulk operations.
   */
  public static function postRender($content, $element) {
    // Placeholders and their substitutions (usually rendered form elements).
    $search = $replace = [];

    // Add in substitutions provided by the form.
    foreach ($element['#substitutions']['#value'] as $substitution) {
      $field_name = $substitution['field_name'];
      $row_id = $substitution['row_id'];

      $search[] = $substitution['placeholder'];
      $replace[] = isset($element[$field_name][$row_id]) ? drupal_render($element[$field_name][$row_id]) : '';
    }
    // Add in substitutions from hook_views_form_substitutions().
    $substitutions = \Drupal::moduleHandler()->invokeAll('views_form_substitutions');
    foreach ($substitutions as $placeholder => $substitution) {
      $search[] = $placeholder;
      $replace[] = $substitution;
    }

    $content = str_replace($search, $replace, $content);

    return $content;
  }
}
