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

use \Drupal\system\Controller\FormAjaxController as CoreFormAjaxController;

class FormAjaxController extends CoreFormAjaxController {

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

    $browser_id =  $request->request->get('browser_id');
    $browser = \Drupal::entityManager()->getStorage('entity_browser')->load($browser_id);
    $form = $this->formBuilder->getForm($browser);

    $form_build_id = $request->request->get('form_build_id');
  
    $form_id = $request->request->get('form_id');
  
      $browser_id =  $request->request->get('browser_id');
      $browser = \Drupal::entityManager()->getStorage('entity_browser')->load($browser_id);
      $form = $this->formBuilder->getForm($browser);

    $form_state->setUserInput($request->request->all());
    $form_id = $form['#form_id'];
  
    return new FileAjaxForm($form, $form_state, $form_id, $form_build_id, $commands);
  }
}
