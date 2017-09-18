<?php

namespace Drupal\custom_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use \Drupal\node\Entity\Node;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "default_rest_resource",
 *   label = @Translation("Page rest resource"),
 *   uri_paths = {
 *     "canonical" = "/page_json/{api}/{nid}",
 *     "https://www.drupal.org/link-relations/create" = "/page_json"
 *   }
 * )
 */
class DefaultRestResource extends ResourceBase {
  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param $plugin_id
   *   The plugin_id for the plugin instance.
   * @param $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_api')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($api, $nid){
    $site_config = \Drupal::config('system.site');
    $site_api_key = $site_config->get('siteapikey');
    if (empty($api) || $api != $site_api_key) {
      throw new AccessDeniedHttpException(t('Access Denied'));
    }
    if (is_numeric($nid) == FALSE) {
      throw new AccessDeniedHttpException(t('Access Denied'));
    }
    $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($nid);
    if($node == FALSE){
      throw new AccessDeniedHttpException(t('Access Denied'));
    }
    $node_type = $node->getType();
    if ($node_type != 'page') {
      throw new AccessDeniedHttpException(t('Access Denied'));
    }
    $response = new ResourceResponse($node, 200);
    $response->addCacheableDependency($node);
    return $response;
  }
}
