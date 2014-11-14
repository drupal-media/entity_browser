<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\Upload.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_browser\EntityBrowserWidgetInterface;
use Drupal\entity_browser\WidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "upload",
 *   label = @Translation("Upload"),
 *   description = @Translation("Adds an upload field browser's widget.")
 * )
 */
class Upload extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'upload_location' => 'public://',
    ) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm($original_form, FormStateInterface $form_state) {
    $form['upload'] = array(
      '#type' => 'managed_file',
      '#title' => t('Choose a file'),
      '#title_display' => 'invisible',
      '#upload_location' => $this->configuration['upload_location'],
      '#multiple' => TRUE,
      '#default_value' => NULL,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Upload',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $uploaded_files = $form_state->getValue(array('upload'), []);
    if (empty($uploaded_files)) {
      $form_state->setError($form['widget']['upload'], t('At least one file should be uploaded.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    $files = [];

    foreach ($form_state->getValue(array('upload'), []) as $fid) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->entityManager->getStorage('file')->load($fid);
      $file->setPermanent();
      $file->save();
      $files[] = $file;
    }

    $this->selectEntities($files);

    // We propagated entities to the other parts of the system. We can now remove
    // them from our values.
    $form_state->setValueForElement($element['upload']['fids'], '');
    NestedArray::setValue($form_state->getUserInput(), $element['upload']['fids']['#parents'], '');
  }

}
