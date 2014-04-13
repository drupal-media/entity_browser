<?php

/**
 * @file
 * Contains \Drupal\media_entity\Controller\MediaController.
 */

namespace Drupal\entity_browser\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Returns responses for Media entity routes.
 */
class MediaController extends ControllerBase {

  /**
   * Displays a media.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The media we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(MediaInterface $media) {
    $build = $this->buildPage($media);

    foreach ($media->uriRelationships() as $rel) {
      // Set the node path as the canonical URL to prevent duplicate content.
      $build['#attached']['drupal_add_html_head_link'][] = array(
        array(
          'rel' => $rel,
          'href' => $media->url($rel),
        )
      , TRUE);

      if ($rel == 'canonical') {
        // Set the non-aliased canonical path as a default shortlink.
        $build['#attached']['drupal_add_html_head_link'][] = array(
          array(
            'rel' => 'shortlink',
            'href' => $media->url($rel, array('alias' => TRUE)),
          )
        , TRUE);
      }
    }

    return $build;
  }

  /**
   * The _title_callback for the media.view route.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The current media.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(MediaInterface $media) {
    return String::checkPlain($this->entityManager()->getTranslationFromContext($media)->label());
  }

  /**
   * Builds a media page render array.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The media we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  protected function buildPage(MediaInterface $media) {
    return array('media' => $this->entityManager()->getViewBuilder('media')->view($media));
  }

  /**
   * Page callback: Provides the media submission form.
   *
   * @param $media_bundle
   *   The media bundle object for the submitted node.
   *
   * @return array
   *   A media submission form.
   */
  public function add(MediaBundleInterface $media_bundle) {
    $user = \Drupal::currentUser();

    $bundle = $media_bundle->id();
    $langcode = $this->moduleHandler()->invoke('language', 'get_default_langcode', array('media', $bundle));
    $media = $this->entityManager()->getStorage('media')->create(array(
      'uid' => $user->id(),
      'bundle' => $bundle,
      'langcode' => $langcode ? $langcode : language_default()->id,
    ));
    return $this->entityFormBuilder()->getForm($media);
  }

  /**
   * The _title_callback for the media.add route.
   *
   * @param \Drupal\media_entity\MediaBundleInterface $media_bundle
   *   The current media.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(MediaBundleInterface $media_bundle) {
    return $this->t('Create @name', array('@name' => $media_bundle->id()));
  }

}
