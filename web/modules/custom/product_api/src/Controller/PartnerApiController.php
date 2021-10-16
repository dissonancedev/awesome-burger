<?php

namespace Drupal\product_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\product_api\ProductApi;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class PartnerApiController extends ControllerBase {

  /**
   * @var ProductApi
   */
  protected $api;

  /**
   * PartnerApiController constructor.
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

  /**
   * @return JsonResponse
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function products() {
    $start = microtime(TRUE);

    $meals = $this->api->getMeals();
    $beers = $this->api->getBeers();

    $end = microtime(TRUE);
    $response = [
      'meals' => $meals,
      'beers' => $beers,
      'meta' => [
        'response_ms' => number_format($end - $start, 2) . 'ms',
      ],
    ];

    return new JsonResponse($response);
  }
}
