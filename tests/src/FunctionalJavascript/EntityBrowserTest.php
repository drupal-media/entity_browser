<?php

namespace Drupal\Tests\entity_browser\FunctionalJavascript;

/**
 * Tests the entity_browser.
 *
 * @group entity_browser
 */
class EntityBrowserTest extends EntityBrowserJavascriptTestBase {

  /**
   * Tests single widget selector.
   */
  public function testSingleWidgetSelector() {

    // Sets the single widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file');

    $this->assertEquals($browser->getWidgetSelector()->getPluginId(), 'single', 'Widget selector is set to single.');

    // Create a file.
    $image = $this->createFile('llama');

    $this->drupalGet('node/add/article');

    $this->assertSession()->linkExists('Select entities');
    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    $this->getSession()->getPage()->checkField('entity_browser_select[file:' . $image->id() . ']');
    $this->getSession()->getPage()->pressButton('Select entities');

    $this->assertSession()->pageTextContains('llama.jpg');
  }

  /**
   * Tests tabs widget selector.
   */
  public function testTabsWidgetSelector() {

    // Sets the tabs widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file')
      ->setWidgetSelector('tabs');
    $browser->save();

    $this->assertEquals($browser->getWidgetSelector()->getPluginId(), 'tabs', 'Widget selector is set to tabs.');

    // Create a file.
    $image = $this->createFile('llama');

    // Create a second file.
    $image2 = $this->createFile('llama2');

    $this->drupalGet('node/add/article');

    $this->assertSession()->linkExists('Select entities');
    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    $this->assertSession()->linkExists('view');
    $this->assertSession()->linkExists('upload');

    $this->assertEquals('is-active active', $this->getSession()->getPage()->findLink('view')->getAttribute('class'));

    $this->getSession()->getPage()->checkField('entity_browser_select[file:' . $image->id() . ']');
    $this->getSession()->getPage()->pressButton('Select entities');
    $this->getSession()->switchToIFrame();

    $this->waitForAjaxToFinish();

    $this->assertSession()->pageTextContains('llama.jpg');

    $this->getSession()->getPage()->clickLink('Select entities');
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');
    $this->getSession()->getPage()->clickLink('upload');

    // This is producing an error. Still investigating
    // InvalidStateError: DOM Exception 11: An attempt was made to use an object
    // that is not, or is no longer, usable.
    //$edit = [
    //  'files[upload][]' => $this->container->get('file_system')->realpath($image2->getFileUri()),
    //];
    // $this->drupalPostForm(NULL, $edit, 'Select files');.
  }

  /**
   * Tests dropdown widget selector.
   */
  public function testDropdownWidgetSelector() {

    // Sets the dropdown widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file')
      ->setWidgetSelector('drop_down');
    $browser->save();

    $this->assertEquals($browser->getWidgetSelector()->getPluginId(), 'drop_down', 'Widget selector is set to dropdown.');

    // Create a file.
    $image = $this->createFile('llama');

    $this->drupalGet('node/add/article');

    $this->assertSession()->linkExists('Select entities');
    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    $this->assertSession()->selectExists('widget');
    // Selects the view widget.
    $this->getSession()->getPage()->selectFieldOption('widget', '774798f1-5ec5-4b63-84bd-124cd51ec07d');

    $this->getSession()->getPage()->checkField('entity_browser_select[file:' . $image->id() . ']');
    $this->getSession()->getPage()->pressButton('Select entities');
    $this->getSession()->switchToIFrame();

    $this->waitForAjaxToFinish();

    $this->assertSession()->pageTextContains('llama.jpg');

    $this->getSession()->getPage()->clickLink('Select entities');

    $this->getSession()->switchToIFrame('entity_browser_iframe_test_entity_browser_file');

    // Causes a fatal.
    // Selects the upload widget.
    // $this->getSession()->getPage()->selectFieldOption('widget', '2dc1ab07-2f8f-42c9-aab7-7eef7f8b7d87');.
  }

  /**
   * Tests wievs selection display.
   */
  public function testViewsSelectionDisplayWidget() {

    // Sets the dropdown widget selector.
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser')
      ->load('test_entity_browser_file')
      ->setSelectionDisplay('view');
    $browser->save();

    $this->assertEquals($browser->getSelectionDisplay()->getPluginId(), 'view', 'Selection display is set to view.');

  }

}
