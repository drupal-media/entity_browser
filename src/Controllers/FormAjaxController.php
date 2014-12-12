<?php

/**
 * @file
 * Contains \Drupal\entity_browser\Controllers\FormAjaxController.
 */

namespace Drupal\entity_browser\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Ajax\UpdateBuildIdCommand;
use Drupal\Core\Form\FormState;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\system\FileAjaxForm;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Entity\EntityManagerInterface;

class FormAjaxController extends \Drupal\system\Controller\FormAjaxController {
  /**
   * Gets a form submitted via #ajax during an Ajax callback.
   *
   * This will load a form from the form cache used during Ajax operations. It
   * pulls the form info from the request body.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Drupal\system\FileAjaxForm
   *   A wrapper object containing the $form, $form_state, $form_id,
   *   $form_build_id and an initial list of Ajax $commands.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
   */
  protected function getForm(Request $request) {
    $form_state = new FormState();
    $form_build_id = $request->request->get('form_build_id');
  
    $form_id = $request->request->get('form_id');
    
    if (!substr($form_id, 0, 15) == 'entity_browser') {
      return parent::getForm($request);
    }
    
    // Get the form from the cache.
    $form = $this->formBuilder->getCache($form_build_id, $form_state);
    if (!$form) {
      $browser_id =  $request->request->get('browser_id');
      $browser = \Drupal::entityManager()->getStorage('entity_browser')->load($browser_id);
      $form = $this->formBuilder->getForm($browser);
      //$form = $this->formBuilder->buildForm($form_id, $form_state);

      if (!$form) {
        // If $form cannot be loaded from the cache, the form_build_id must be
        // invalid, which means that someone performed a POST request onto
        // system/ajax without actually viewing the concerned form in the browser.
        // This is likely a hacking attempt as it never happens under normal
        // circumstances.
        $this->logger->warning('Invalid form POST data.');
        throw new BadRequestHttpException();
      }
    }
  
    // When a page level cache is enabled, the form-build id might have been
    // replaced from within \Drupal::formBuilder()->getCache(). If this is the
    // case, it is also necessary to update it in the browser by issuing an
    // appropriate Ajax command.
    $commands = [];
    if (isset($form['#build_id_old']) && $form['#build_id_old'] != $form['#build_id']) {
      // If the form build ID has changed, issue an Ajax command to update it.
      $commands[] = new UpdateBuildIdCommand($form['#build_id_old'], $form['#build_id']);
      $form_build_id = $form['#build_id'];
    }
  
    // Since some of the submit handlers are run, redirects need to be disabled.
    $form_state->disableRedirect();
  
    // When a form is rebuilt after Ajax processing, its #build_id and #action
    // should not change.
    // @see \Drupal\Core\Form\FormBuilderInterface::rebuildForm()
    $form_state->addRebuildInfo('copy', [
        '#build_id' => TRUE,
        '#action' => TRUE,
        ]);
  
    // The form needs to be processed; prepare for that by setting a few
    // internal variables.
    $form_state->setUserInput($request->request->all());
    $form_id = $form['#form_id'];
  
    return new FileAjaxForm($form, $form_state, $form_id, $form_build_id, $commands);
  }
}