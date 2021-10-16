<?php

namespace Drupal\product_api\Normalizer;


use Drupal\product_api\Model\Beer;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BeerNormalizer extends NormalizerBase implements DenormalizerInterface {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = Beer::class;


  /**
   * {@inheritdoc}
   */
  public function normalize($datetime, $format = NULL, array $context = []) {

  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $beers = [];
    if (is_array($data)) {
      foreach ($data as $b) {
        $fields = [];
        foreach ($b as $key => $val) {
          if (is_object($val)) {
            $fields[$key] = (array)$val;
          }
          else {
            $fields[$key] = $val;
          }
        }

        $beers[] = new $class($fields);
      }
    }

    return $beers;
  }

}
