<?php

namespace Drupal\product_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\product_api\Model\Beer;
use Drupal\product_api\Model\Meal;
use Drupal\product_api\ProductApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductPageController extends ControllerBase {

  /**
   * @var ProductApi
   */
  protected $api;

  /**
   * ProductPageController constructor.
   *
   * @param ProductApi $api
   */
  public function __construct(ProductApi $api) {
    $this->api = $api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('product_api')
    );
  }

  protected function loadProducts() {
    $meals = $this->api->getMeals();
    $beers = $this->api->getBeers();
    $products = $this->entityTypeManager()->getStorage('node')->loadByProperties([
      //'bundle' => 'product',
    ]);

    $allProducts = array_merge($meals, $beers, $products);
    $items = [];
    foreach ($allProducts as $product) {
      if ($product instanceof Meal) {
        $items[] = [
          'id' => $product->id,
          'name' => $product->meal,
          'img' => $product->mealThumb,
        ];
      }
      elseif ($product instanceof Beer) {
        $items[] = [
          'id' => $product->id,
          'name' => $product->name,
          'img' => $product->image_url,
        ];
      }
      elseif ($product instanceof NodeInterface) {
        $items[] = [
          'id' => $product->id,
          'name' => $product->name,
          'img' => $product->image_url,
        ];
      }
    }

    return $items;
  }

  public function home() {
    $items = $this->loadProducts();

    /*
     * Instead of shuffling here we should select the first three that are promoted to page.
     */
    shuffle($items);
    $items = array_slice($items, 0, 3);

    return [
      '#theme' => 'sundown_frontpage',
      '#products' => $items,
    ];
  }

  public function contact() {
    $contactForm = $this->formBuilder()->getForm('Drupal\product_pages\Form\ContactForm');

    return $contactForm;
  }

  public function products() {
    $items = $this->loadProducts();

    return [
      '#theme' => 'sundown_products',
      '#products' => $items,
    ];
  }

  public function product($product_id) {

  }

}
