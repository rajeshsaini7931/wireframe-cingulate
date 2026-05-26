<?php

declare(strict_types=1);

namespace Drupal\cingulate_npi\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * NPI API proxy controller.
 *
 * Acts as a backend proxy to the CMS NPI Registry API to avoid CORS issues.
 */
class NpiApiController extends ControllerBase {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Constructs a NpiApiController instance.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('http_client'),
    );
  }

  /**
   * Searches for NPI providers via CMS NPI Registry API.
   *
   * Acts as a proxy to avoid CORS issues with direct browser calls.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response from the NPI API.
   */
  public function search(Request $request): JsonResponse {
    // Get query parameters from the request.
    $lastName = $request->query->get('last_name', '');
    $state = $request->query->get('state', '');

    // Validate required fields.
    if (empty($lastName) || empty($state)) {
      return new JsonResponse([
        'error' => TRUE,
        'message' => 'Last name and state are required.',
      ], 400);
    }

    // Build query parameters for CMS NPI API.
    $params = [
      'version' => '2.1',
      'last_name' => $lastName,
      'state' => $state,
      'limit' => '10',
    ];

    // Add optional parameters if provided.
    $firstName = $request->query->get('first_name', '');
    if (!empty($firstName)) {
      $params['first_name'] = $firstName;
    }

    $city = $request->query->get('city', '');
    if (!empty($city)) {
      $params['city'] = $city;
    }

    $postalCode = $request->query->get('postal_code', '');
    if (!empty($postalCode)) {
      // Normalize ZIP code (remove hyphen).
      $params['postal_code'] = str_replace('-', '', $postalCode);
    }

    // Build the API URL.
    $apiUrl = 'https://npiregistry.cms.hhs.gov/api/?' . http_build_query($params);

    try {
      // Make the API request.
      $response = $this->httpClient->request('GET', $apiUrl, [
        'headers' => [
          'Accept' => 'application/json',
        ],
        'timeout' => 10,
      ]);

      // Get the response body.
      $body = $response->getBody()->getContents();
      $data = json_decode($body, TRUE);

      // Return the API response.
      return new JsonResponse($data);
    }
    catch (RequestException $e) {
      // Log the error.
      $this->getLogger('cingulate_npi')->error(
        'NPI API request failed: @message',
        ['@message' => $e->getMessage()]
      );

      // Return error response.
      return new JsonResponse([
        'error' => TRUE,
        'message' => 'Unable to retrieve NPI information at this time.',
        'result_count' => 0,
        'results' => [],
      ], 500);
    }
    catch (\Exception $e) {
      // Log unexpected errors.
      $this->getLogger('cingulate_npi')->error(
        'Unexpected error in NPI API proxy: @message',
        ['@message' => $e->getMessage()]
      );

      return new JsonResponse([
        'error' => TRUE,
        'message' => 'An unexpected error occurred.',
        'result_count' => 0,
        'results' => [],
      ], 500);
    }
  }

}
