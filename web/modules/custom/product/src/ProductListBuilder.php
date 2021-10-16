<?php

namespace Drupal\product;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for product entity.
 *
 * @ingroup product
 */
class ProductListBuilder extends EntityListBuilder {

  protected function getEntityIds() {
    $entities = $this->getStorage()->loadMultiple();

    $ids = [];
    foreach ($entities as $entity) {
      $ids[] = $entity->id();
    }

    return $ids;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the product list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Product ID');
    $header['name'] = $this->t('Name');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\product\Entity\Product */
    $row['id'] = $entity->id();
    $row['name'] = $entity->label();

    return $row + parent::buildRow($entity);
  }

}
