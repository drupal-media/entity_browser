<?php

/**
 * Contains \Drupal\entity_browser\Plugin\EntityBrowser\Widget\Upload.
 */

namespace Drupal\entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Component\Plugin\PluginBase;
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
  public function getForm() {
    $form['upload'] = array(
      '#type' => 'managed_file',
      '#title' => t('Choose a file'),
      '#title_display' => 'invisible',
      '#upload_location' => empty($this->configuration['settings']['upload_location']) ? 'public://' : $this->configuration['settings']['upload_location'],
      '#multiple' => TRUE,
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
    if (count($form_state->getValue(array('upload'), [])) == 0) {
      $form_state->setError($form['widget']['upload'], t('At least one file should be uploaded.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$form, FormStateInterface $form_state) {
    $files = [];

    foreach ($form_state->getValue(array('upload'), []) as $fid) {
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->entityManager->getStorage('file')->load($fid);
      $file->setPermanent();
      $file->save();
      $files[] = $file;
    }

    $this->selectEntities($files);
  }

}
