<?php

namespace Drupal\product_api;


use GuzzleHttp\Client;
use Symfony\Component\Serializer\Serializer;

class ProductApi {

  const meals_api_url = 'https://www.themealdb.com/api/json/v1/1/random.php';

  const beers_api_url = 'https://api.punkapi.com/v2/beers';

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Symfony serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Constructor.
   *
   * @param \GuzzleHttp\Client $http_client
   * @param \Symfony\Component\Serializer\Serializer $serializer
   */
  public function __construct(Client $http_client, Serializer $serializer) {
    $this->httpClient = $http_client;
    $this->serializer = $serializer;
  }

  /**
   * @param $url
   * @param string $method
   * @param array $options
   * @return string
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function handleRequest($url, $method = 'GET', array $options = []) {
    $method = strtolower($method);
    $options += [
      'headers' => [],
      'http_errors' => FALSE,
    ];

    $options['headers'] += [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
    ];

    $response = $this->httpClient->request($method, $url, $options);
    $statusCode = $response->getStatusCode();

    if ($statusCode >= 400) {
      throw new \Exception('Unable to fetch data from API');
    }

    return (string)$response->getBody();
  }

  /**
   * @return array|object
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getMeals() {
    $data = $this->handleRequest(self::meals_api_url);
    $meals = $this->serializer->deserialize($data, 'Drupal\\product_api\\Model\\Meal', 'json');

    return $meals;

  }

  /**
   * @return array|object
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getBeers() {
    $data = $this->handleRequest(self::beers_api_url);

    $beers = $this->serializer->deserialize($data, 'Drupal\\product_api\\Model\\Beer', 'json');

    return $beers;
  }
}
