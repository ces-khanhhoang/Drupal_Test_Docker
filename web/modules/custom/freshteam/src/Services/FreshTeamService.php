<?php declare(strict_types=1);

namespace Drupal\freshteam\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Contains all the method to handle request of the site.
 *
 * @category Service
 *
 * @package Custom
 */
class FreshTeamService
{
  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client)
  {
    $this->httpClient = $http_client;
    (new Dotenv())->bootEnv(DRUPAL_ROOT . '/sites/default/files/private/.env');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('http_client')
    );
  }

  public function getJobsList()
  {
    try {
      $headers = [
        'headers' => [
          'Authorization' => 'Bearer ' . $_ENV['FRESHTEAM_APIKEY'],
          'accept' => 'application/json',
        ],
      ];
      $request = $this->httpClient->request('GET', "https://" . $_ENV['FRESHTEAM_DOMAIN'] . "/api/job_postings?status=published&sort_type=asc&sort=updated_at", $headers);
      if ($request->getStatusCode() != 200) {
        return;
      }
      return $request->getBody()->getContents();
    } catch (RequestException $e) {
      return $e->getMessage();
    }
  }
}
