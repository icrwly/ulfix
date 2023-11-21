<?php

namespace Drupal\ul_marketo\Plugin\Validation\Constraint;

use Drupal\block_content\BlockContentInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\Validator\Constraint;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Validates the MarketoFormExists constraint.
 */
class MarketoFormExistsValidator extends ConstraintValidator implements ContainerInjectionInterface {
  /**
   * Entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an instance of MarketoFormExistsValidator.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Creates an instance of MarketoFormExistsValidator.
   *
   * @param Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   *
   * @return static
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var Drupal\Core\Entity\EntityInterface $parent */
    $parent = $this->context->getRoot()->getEntity();
    $pass_validation = FALSE;
    if ($parent instanceof NodeInterface && $parent->hasField('field_shared_marketo_custom')) {
      $pass_validation = $this->checkIfPassesValidation($value, $parent);
    }
    if ($parent instanceof Paragraph) {
      $grandparent = $parent->getParentEntity();
      $pass_validation = $this->checkIfPassesValidation($value, $grandparent);
    }
    if ($pass_validation === FALSE) {
      $label = $this->entityTypeManager->getStorage('marketo_form_type')->load($value)->label();
      $this->context->addViolation($constraint->notAvailable, ['%value' => $label]);
    }
  }

  /**
   * Helper method to check parent entity.
   *
   * @param string $value
   *   The value.
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   The parent.
   *
   * @return bool
   *   True or false.
   */
  private function checkIfPassesValidation(string $value, EntityInterface $parent) {
    $pass_validation = FALSE;
    if ($parent instanceof NodeInterface) {
      $form_entities = $parent->get('field_shared_marketo_custom')->referencedEntities();
    }
    if ($parent instanceof BlockContentInterface) {
      $form_entities = $parent->get('field_marketo_form_customization')->referencedEntities();
      $has_field = $parent->hasField('field_marketo_form_customization');
    }
    foreach ($form_entities as $entity) {
      if ($entity->bundle() === $value) {
        $pass_validation = TRUE;
        break;
      }
    }
    if ($has_field) {
      $pass_validation = TRUE;
    }
    return $pass_validation;
  }

}
