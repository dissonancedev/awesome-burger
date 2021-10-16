<?php

namespace Drupal\product_api\Normalizer;


use Drupal\product_api\Model\Meal;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MealsNormalizer extends NormalizerBase implements DenormalizerInterface {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = Meal::class;


  /**
   * {@inheritdoc}
   */
  public function normalize($datetime, $format = NULL, array $context = []) {

  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $meals = [];
    if (isset($data['meals'])) {
      foreach ($data['meals'] as $m) {
        $fields = [];
        foreach ($m as $key => $val) {
          if (strpos($key, 'Ingredient') !== FALSE || strpos($key, 'Measure') !== FALSE) {
            $k = strpos($key, 'Ingredient') !== FALSE ? 'ingredient' : 'measure';
            $num = str_replace(['strIngredient', 'strMeasure'], '', $key);
            $fields['ingredients'][intval($num)][$k] = $val;
          }
          elseif ($key === 'idMeal') {
            $fields['id'] = $val;
          }
          elseif ($key === 'strInstructions') {
            $fields['instructions'] = $val;
          }
          elseif (strpos($key, 'str') !== FALSE) {
            $fields[lcfirst(str_replace('str', '', $key))] = $val;
          }
        }

        $meals[] = new $class($fields);
      }
    }

    return $meals;
  }

}
