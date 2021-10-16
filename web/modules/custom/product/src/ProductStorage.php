<?php

namespace Drupal\product;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityStorageBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\node\NodeInterface;
use Drupal\product\Entity\Product;
use Drupal\product_api\Model\Beer;
use Drupal\product_api\Model\Meal;
use Drupal\product_api\ProductApi;
use \GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements storage in API.
 */
class ProductStorage extends ContentEntityStorageBase implements SqlEntityStorageInterface {
  /**
   * @var ProductApi
   */
  private $api;

  /**
   * ProductStorage constructor.
   *
   * @param EntityTypeInterface $entity_type
   * @param EntityFieldManagerInterface $entity_field_manager
   * @param CacheBackendInterface $cache
   * @param MemoryCacheInterface $memory_cache
   * @param EntityTypeBundleInfoInterface $entity_type_bundle_info
   * @param ProductApi $api
   */
  public function __construct(EntityTypeInterface $entity_type, EntityFieldManagerInterface $entity_field_manager, CacheBackendInterface $cache, MemoryCacheInterface $memory_cache, EntityTypeBundleInfoInterface $entity_type_bundle_info, ProductApi $api) {
    parent::__construct($entity_type, $entity_field_manager, $cache, $memory_cache,$entity_type_bundle_info);
    $this->api = $api;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('product_api')
    );
  }

  /**
   * Resets the internal, static entity cache.
   *
   * @param $ids
   *   (optional) If specified, the cache is reset for the entities with the
   *   given ids only.
   */
  public function resetCache(array $ids = NULL) {
    // TODO: Implement resetCache() method.
  }

  /**
   * This function converts a model to a drupal entity.
   *
   * @param $product
   * @return EntityInterface|Product
   */
  protected function convertToEntity($product) {
    if ($product instanceof Meal) {
      $product = [
        'id' => $product->id,
        'name' => $product->meal,
        'img' => $product->mealThumb,
      ];
    }
    elseif ($product instanceof Beer) {
      $product = [
        'id' => $product->id,
        'name' => $product->name,
        'img' => $product->image_url,
      ];
    }
    elseif ($product instanceof NodeInterface) {
      $product = [
        'id' => $product->id(),
        'name' => $product->get('field_name')->getValue()[0]['value'],
        'img' => '',
      ];
    }

    $entity = Product::create($product);

    return $entity;
  }

  /**
   * Loads one or more entities.
   *
   * @param array $ids
   *   An array of entity IDs, or NULL to load all entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entity objects indexed by their IDs. Returns an empty array
   *   if no matching entities are found.
   */
  public function loadMultiple(array $ids = NULL) {
    if (!empty($ids)) {
      $ids = array_values(array_unique($ids));
    }

    try {
      $meals = $this->api->getMeals();
      $beers = $this->api->getBeers();
      $apiEntities = array_merge($meals, $beers);
    }
    catch (\Exception $e) {
      return [];
    }
    catch (GuzzleException $e) {
      return [];
    }

    $entities = [];
    foreach ($apiEntities as $apiEntity) {
      if (in_array($apiEntity->id, $ids)) {
        $entity = $this->convertToEntity($apiEntity);
        $entities[] = $entity;
      }
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    $entity = $this->loadMultiple([$id]);

    return count($entity) > 0 ? $entity[0] : NULL;
  }

  /**
   * Loads an unchanged entity from the database.
   *
   * @param mixed $id
   *   The ID of the entity to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The unchanged entity, or NULL if the entity cannot be loaded.
   *
   * @throws GuzzleException
   * @todo Remove this method once we have a reliable way to retrieve the
   *   unchanged entity from the entity object.
   */
  public function loadUnchanged($id) {
    return $this->load($id);
  }

  /**
   * Load a specific entity revision.
   *
   * @param int|string $revision_id
   *   The revision id.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The specified entity revision or NULL if not found.
   *
   * @todo Deprecated in Drupal 8.5.0 and will be removed before Drupal 9.0.0.
   *   Use \Drupal\Core\Entity\RevisionableStorageInterface instead.
   *
   * @see https://www.drupal.org/node/2926958
   * @see https://www.drupal.org/node/2927226
   */
  public function loadRevision($revision_id) {
    // TODO: Implement loadRevision() method.
  }

  /**
   * Delete a specific entity revision.
   *
   * A revision can only be deleted if it's not the currently active one.
   *
   * @param int $revision_id
   *   The revision id.
   *
   * @todo Deprecated in Drupal 8.5.0 and will be removed before Drupal 9.0.0.
   *   Use \Drupal\Core\Entity\RevisionableStorageInterface instead.
   *
   * @see https://www.drupal.org/node/2926958
   * @see https://www.drupal.org/node/2927226
   */
  public function deleteRevision($revision_id) {
    // TODO: Implement deleteRevision() method.
  }

  /**
   * Load entities by their property values.
   *
   * @param array $values
   *   An associative array where the keys are the property names and the
   *   values are the values those properties must have.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entity objects indexed by their ids.
   * @throws GuzzleException
   */
  public function loadByProperties(array $values = []) {
    $entities = $this->loadMultiple();

    if (!empty($entities)) {
      $entities = array_filter($entities, function ($entity) use ($values) {
        if (isset($values['name']) && $entity->label() !== $values['name']) {
          return FALSE;
        }

        return TRUE;
      });
    }

    return $entities;
  }

  /**
   * Constructs a new entity object, without permanently saving it.
   *
   * @param array $values
   *   (optional) An array of values to set, keyed by property name. If the
   *   entity type has bundles, the bundle key has to be specified.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A new entity object.
   */
  public function create(array $values = []) {
    $entity = new Product($values, 'product');
    foreach ($values as $field => $value) {
      $entity->set($field, $value);
    }
    return $entity;
  }

  /**
   * Deletes permanently saved entities.
   *
   * @param Product[] $entities
   *   An array of entity objects to delete.
   *
   * @return int
   */
  public function delete(array $entities) {
    return SAVED_DELETED;
  }

  /**
   * Saves the entity permanently.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to save.
   *
   * @return
   *   SAVED_NEW or SAVED_UPDATED is returned depending on the operation
   *   performed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures, an exception is thrown.
   */
  public function save(EntityInterface $entity) {
    return SAVED_NEW;
  }

  /**
   * Restores a previously saved entity.
   *
   * Note that the entity is assumed to be in a valid state for the storage, so
   * the restore process does not invoke any hooks, nor does it perform any
   * post-save operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to restore.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures, an exception is thrown.
   *
   * @internal
   */
  public function restore(EntityInterface $entity) {
    $entity = $this->load($entity->id());
  }

  /**
   * Determines if the storage contains any data.
   *
   * @return bool
   *   TRUE if the storage contains data, FALSE if not.
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function hasData() {
    try {
      $res = TRUE;
    }
    catch (\Exception $e) {
      $res = FALSE;
    }

    return $res;
  }

  /**
   * Gets an entity query instance.
   *
   * @param string $conjunction
   *   (optional) The logical operator for the query, either:
   *   - AND: all of the conditions on the query need to match.
   *   - OR: at least one of the conditions on the query need to match.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query instance.
   *
   * @see \Drupal\Core\Entity\EntityStorageBase::getQueryServiceName()
   */
  public function getQuery($conjunction = 'AND') {
    // TODO: Implement getQuery() method.
  }

  /**
   * Gets an aggregated query instance.
   *
   * @param string $conjunction
   *   (optional) The logical operator for the query, either:
   *   - AND: all of the conditions on the query need to match.
   *   - OR: at least one of the conditions on the query need to match.
   *
   * @return \Drupal\Core\Entity\Query\QueryAggregateInterface
   *   The aggregated query object that can query the given entity type.
   *
   * @see \Drupal\Core\Entity\EntityStorageBase::getQueryServiceName()
   */
  public function getAggregateQuery($conjunction = 'AND') {
    return \Drupal::service($this->getQueryServiceName())->get($this->entityType, $conjunction);
  }

  /**
   * Gets the name of the service for the query for this entity storage.
   *
   * @return string
   *   The name of the service for the query for this entity storage.
   */
  protected function getQueryServiceName() {
    return 'entity.query.null';
  }

  /**
   * Gets the entity type ID.
   *
   * @return string
   *   The entity type ID.
   */
  public function getEntityTypeId() {
    return $this->entityType->id();
  }

  /**
   * Gets the entity type definition.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   Entity type definition.
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * @inheritDoc
   */
  public function createWithSampleValues($bundle = FALSE, array $values = []) {
    // TODO: Implement createWithSampleValues() method.
  }

  /**
   * @inheritDoc
   */
  public function loadMultipleRevisions(array $revision_ids) {
    // TODO: Implement loadMultipleRevisions() method.
  }

  /**
   * @inheritDoc
   */
  public function getLatestRevisionId($entity_id) {
    // TODO: Implement getLatestRevisionId() method.
  }

  /**
   * @inheritDoc
   */
  public function createRevision(RevisionableInterface $entity, $default = TRUE, $keep_untranslatable_fields = NULL) {
    // TODO: Implement createRevision() method.
  }

  /**
   * @inheritDoc
   */
  public function getLatestTranslationAffectedRevisionId($entity_id, $langcode) {
    // TODO: Implement getLatestTranslationAffectedRevisionId() method.
  }

  /**
   * @inheritDoc
   */
  public function createTranslation(ContentEntityInterface $entity, $langcode, array $values = []) {
    // TODO: Implement createTranslation() method.
  }


  /**
   * Reads values to be purged for a single field.
   *
   * This method is called during field data purge, on fields for which
   * onFieldDefinitionDelete() has previously run.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param $batch_size
   *   The maximum number of field data records to purge before returning.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface[]
   *   An array of field item lists, keyed by entity revision id.
   */
  protected function readFieldItemsToPurge(FieldDefinitionInterface $field_definition, $batch_size)
  {
    // TODO: Implement readFieldItemsToPurge() method.
  }

  /**
   * Removes field items from storage per entity during purge.
   *
   * @param ContentEntityInterface $entity
   *   The entity revision, whose values are being purged.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field whose values are bing purged.
   */
  protected function purgeFieldItems(ContentEntityInterface $entity, FieldDefinitionInterface $field_definition)
  {
    // TODO: Implement purgeFieldItems() method.
  }

  /**
   * Actually loads revision field item values from the storage.
   *
   * @param array $revision_ids
   *   An array of revision identifiers.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The specified entity revisions or an empty array if none are found.
   */
  protected function doLoadMultipleRevisionsFieldItems($revision_ids)
  {
    // TODO: Implement doLoadMultipleRevisionsFieldItems() method.
  }

  /**
   * Writes entity field values to the storage.
   *
   * This method is responsible for allocating entity and revision identifiers
   * and updating the entity object with their values.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   * @param string[] $names
   *   (optional) The name of the fields to be written to the storage. If an
   *   empty value is passed all field values are saved.
   */
  protected function doSaveFieldItems(ContentEntityInterface $entity, array $names = [])
  {
    // TODO: Implement doSaveFieldItems() method.
  }

  /**
   * Deletes entity field values from the storage.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   An array of entity objects to be deleted.
   */
  protected function doDeleteFieldItems($entities)
  {
    // TODO: Implement doDeleteFieldItems() method.
  }

  /**
   * Deletes field values of an entity revision from the storage.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $revision
   *   An entity revision object to be deleted.
   */
  protected function doDeleteRevisionFieldItems(ContentEntityInterface $revision)
  {
    // TODO: Implement doDeleteRevisionFieldItems() method.
  }

  /**
   * Performs storage-specific loading of entities.
   *
   * Override this method to add custom functionality directly after loading.
   * This is always called, while self::postLoad() is only called when there are
   * actual results.
   *
   * @param array|null $ids
   *   (optional) An array of entity IDs, or NULL to load all entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Associative array of entities, keyed on the entity ID.
   */
  protected function doLoadMultiple(array $ids = NULL)
  {
    // TODO: Implement doLoadMultiple() method.
  }

  /**
   * Determines if this entity already exists in storage.
   *
   * @param int|string $id
   *   The original entity ID.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being saved.
   *
   * @return bool
   */
  protected function has($id, EntityInterface $entity)
  {
    // TODO: Implement has() method.
  }

  /**
   * Determines the number of entities with values for a given field.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field for which to count data records.
   * @param bool $as_bool
   *   (Optional) Optimizes the query for checking whether there are any records
   *   or not. Defaults to FALSE.
   *
   * @return bool|int
   *   The number of entities. If $as_bool parameter is TRUE then the
   *   value will either be TRUE or FALSE.
   *
   * @see \Drupal\Core\Entity\FieldableEntityStorageInterface::purgeFieldData()
   */
  public function countFieldData($storage_definition, $as_bool = FALSE)
  {
    // TODO: Implement countFieldData() method.
  }

  /**
   * Gets a table mapping for the entity's SQL tables.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface[] $storage_definitions
   *   (optional) An array of field storage definitions to be used to compute
   *   the table mapping. Defaults to the ones provided by the entity field
   *   manager.
   *
   * @return \Drupal\Core\Entity\Sql\TableMappingInterface
   *   A table mapping object for the entity's tables.
   */
  public function getTableMapping(array $storage_definitions = NULL)
  {
    // TODO: Implement getTableMapping() method.
  }
}
