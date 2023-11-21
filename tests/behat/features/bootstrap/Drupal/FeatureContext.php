<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ExpectationException;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
// class FeatureContext extends RawDrupalContext {
  class FeatureContext extends RawDrupalContext implements Context{

  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * @Given I am logged in as user :name
   */
  public function iAmLoggedInAsUser($name) {
    $domain = $this->getMinkParameter('base_url');

    // Pass base url to drush command.
    $uli = $this->getDriver('drush')->drush('uli', [
      "--name '" . $name . "'",
      "--browser=0",
      "--uri=$domain",
    ]);

    // Trim EOL characters.
    $uli = trim($uli);

    // Log in.
    $this->getSession()->visit($uli);
  }

  /**
   * @Then /^the "([^"]*)" entity type "([^"]*)" should have field "([^"]*)" of type "([^"]*)"$/
   */
  public function assertFieldNameAndType($entity_type, $bundle, $field_name, $type)
  {
    $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);
    $field = FieldConfig::loadByName($entity_type, $bundle, $field_name);

    if (!$field) {
      throw new ExpectationException('Field does not exist.', $this->getSession());
    }
    else {
      echo "$entity_type, $bundle, $field_name";
    }

  }

  /**
   * Check that an html element exists.
   *
   * @param string $selector
   *   The css selector.
   *
   * @Then the :selector element should exist
   */
  public function theElementShouldExist($selector) {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $selector);

    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $selector));
    }
  }

  /**
   * Fills in field (input, textarea, select) with specified locator.
   *
   * @param string $locator
   *   Input id, name or label.
   * @param string $value
   *   The text for the field.
   *
   * @throws \InvalidArgumentException
   *
   * @When I fill in paragraph :locator with the text :value
   */
  public function WhenIFillInParagraphWith($locator, $value)
  {
    $session = $this->getSession();
    $element = $session->getPage()->find('css', $locator);

    if (null === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate element: "%s"', $locator));
    }
    $session->getPage()->find('css', $locator)->setValue($value);
  }

  /**
   * @Then I fill in wysiwyg on field :locator with :value
   */
  public function iFillInWysiwygOnFieldWith($locator, $value) {
    $session = $this->getSession();
    // $el = $this->getSession()->getPage()->findField($locator);
    $el = $session->getPage()->find('css', $locator);

    if (empty($el)) {
      throw new ExpectationException('Could not find WYSIWYG with locator: ' . $locator, $this->getSession());
    }

    $fieldId = $el->getAttribute('id');

    if (empty($fieldId)) {
      throw new \InvalidArgumentException('Could not find an id for field with locator: ' . $locator);
    }

    // $session->executeScript("CKEDITOR.instances[\"$fieldId\"].setData(\"$value\");");
    $session->getPage()->find('css', $locator)->setValue($value);
  }
}
