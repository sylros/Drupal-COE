<?php

namespace Drupal\apm_rest\Controller;

use Drupal\node\Entity\Node;
use Drupal\Component\Serialization\Json;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\paragraphs\Entity\Paragraph;

class RestController extends ControllerBase {
  /**
  * Entity query factory. *
  * @var \Drupal\Core\Entity\Query\QueryFactory
  */

  protected $entityQuery;

 /**
  * Constructs a new ClaimRestController object.
  * @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
  * The entity query factory.
  */

  public function __construct(QueryFactory $entity_query) {
   $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */

  public static function create(ContainerInterface $container) {
     return new static(
       $container->get('entity.query')
   );
  }

  public function getDataCentre() {
    $nodeId = 0;
    if(\Drupal::request()->query->get('node_id')) {
      $nodeId = \Drupal::request()->query->get('node_id');
    }

    $data = [];
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','data_centre');

    if($nodeId) {
      $DBQuery->condition('nid',$nodeId);
    }

    $nid = $DBQuery->execute();
    $node = NULL;

    if(empty($nid)) {
      $data[] = 'No matching data centre with node id: ' . $nodeId;
    } else {
      foreach ($nid as $key => $value) {
        $node = Node::load($value);

        $data[$value] = array(
          'field_title' => $node->get('title')->getValue()[0]['value'],
          'field_abbreviation' => $node->get('field_abbreviation')->getValue()[0]['value'],
          'field_servers' => $this->getMultiValueReferenceField($node,'field_servers')
        );
      }
    }

    $response_result['data'] = $serializer->normalize($data,'json');
    $response = new JsonResponse($response_result);
    return $response;
  }

  public function getServer() {
    $nodeId = 0;
    if(\Drupal::request()->query->get('node_id')) {
      $nodeId = \Drupal::request()->query->get('node_id');
    }

    $data = [];
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','server');

    if($nodeId) {
      $DBQuery->condition('nid',$nodeId);
    }

    $nid = $DBQuery->execute();
    $node = NULL;

    if(empty($nid)) {
      $data[] = 'No matching server with node id: ' . $nodeId;
    } else {
      foreach ($nid as $key => $value) {
        $node = Node::load($value);

        $data[$value] = array(
          'field_title' => $node->get('title')->getValue()[0]['value'],
          'field_database' => $node->get('field_database')->getValue()[0]['value'],
          'field_data_center' => $this->getMultiValueReferenceField($node,'field_data_center'),
          'field_decomision_date' => $node->get('field_decomision_date')->getValue()[0]['value'],
          'field_environments' => $this->getMultiValueReferenceField($node,'field_environments'),
          'field_is_a_db_server' => $node->get('field_is_a_db_server')->getValue()[0]['value'],
          'field_list_of_IPs' => $this->getMultiValueReferenceField($node,'field_list_of_IPs'),
          'field_os' => $node->get('field_os')->getValue()[0]['value'],
          'field_type' => $node->get('field_type')->getValue()[0]['value']
        );
      }
    }

    $response_result['data'] = $serializer->normalize($data,'json');
    $response = new JsonResponse($response_result);
    return $response;
  }

  public function getEnvironment() {
    $nodeId = 0;
    if(\Drupal::request()->query->get('node_id')) {
      $nodeId = \Drupal::request()->query->get('node_id');
    }

    $data = [];
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','environments');

    if($nodeId) {
      $DBQuery->condition('nid',$nodeId);
    }

    $nid = $DBQuery->execute();
    $node = NULL;

    if(empty($nid)) {
      $data[] = 'No matching environment with node id: ' . $nodeId;
    } else {
      foreach ($nid as $key => $value) {
        $node = Node::load($value);
        $databaseServer = Node::load($node->get('field_database_server')[0]['target_id']);
        $data[$value] = array(
          'field_title' => $node->get('title')->getValue()[0]['value'],
          'field_applications' => $this->getMultiValueReferenceField($node,'field_application'),
          'field_apache_version' => $node->get('field_apache_version')->getValue()[0]['value'],
          'field_database_name' => $node->get('field_database_name')->getValue()[0]['value'],
          'field_domain' => $node->get('field_domain')->getValue()[0]['value'],
          'field_php_version' => $node->get('field_php_version')->getValue()[0]['value'],
          'field_server_path' => $node->get('field_server_path')->getValue()[0]['value'],
          'field_supports_https' => $node->get('field_supports_https')->getValue()[0]['value'],
          'field_type' => $node->get('field_type')->getValue()[0]['value'],
          'field_url' => strip_tags($node->get('field_url')->getValue()[0]['value']),
        );

        $serverID = $node->get('field_server')->getValue()[0]['target_id'];

        if($serverID) {
          $server = Node::load($node->get('field_server')->getValue()[0]['target_id']);
          $data[$value]['field_server'] = array(
            'nid' => $node->get('field_server')->getValue()[0]['target_id'],
            'title' => $server->get('title')->getValue()[0]['value']
          );
        }

        if($databaseServer !== NULL) {
          $data[$value]['field_database_server'] = array(
            'nid' => $node->get('field_database_server')->getValue()[0]['target_id'],
            'title' => $databaseServer->get('title')->getValue()[0]['value'],
          );
        }
      }
    }

    $response_result['data'] = $serializer->normalize($data,'json');
    $response = new JsonResponse($response_result);
    return $response;
  }

  public function getApplication() {
    $nodeId = 0;
    if(\Drupal::request()->query->get('node_id')) {
      $nodeId = \Drupal::request()->query->get('node_id');
    }

    $data = [];
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','application');

    if($nodeId) {
      $DBQuery->condition('nid',$nodeId);
    }

    $nid = $DBQuery->execute();
    $node = NULL;

    if(empty($nid)) {
      $data[] = 'No matching environment with node id: ' . $nodeId;
    } else {
      foreach ($nid as $key => $value) {
        $node = Node::load($value);
        // $appStore = Node::load($node->get('field_app_store')->getValue()[0]['target_id']);
        // $environment = Node::load($node->get('field_environments')->getValue()[0]['target_id']);

        $data[$value] = array(
          'field_title' => $node->get('title')->getValue()[0]['value'],
          'field_apm_id' => $node->get('field_apm_id')->getValue()[0]['value'],
          'field_decomision_date' => $node->get('field_decomision_date')->getValue()[0]['value'],
          'field_issues_url' => strip_tags($node->get('field_issues_url')->getValue()[0]['value']),
          'field_repository_url' => strip_tags($node->get('field_repository_url')->getValue()[0]['value']),
          'field_users' => $this->getMultiValueReferenceField($node,'persons'),
          'field_is_cost_recovered' => $node->get('field_is_cost_recovered_')->getValue()[0]['value'],
        );
      }
    }

    $response_result['data'] = $serializer->normalize($data,'json');
    $response = new JsonResponse($response_result);
    return $response;
  }

  public function getPerson() {
    $nodeId = 0;
    if(\Drupal::request()->query->get('node_id')) {
      $nodeId = \Drupal::request()->query->get('node_id');
    }

    $data = [];
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','Person');

    if($nodeId) {
      $DBQuery->condition('nid',$nodeId);
    }

    $nid = $DBQuery->execute();
    $node = NULL;

    if(empty($nid)) {
      $data[] = 'No matching person with node id: ' . $nodeId;
    } else {
      foreach ($nid as $key => $value) {
        $node = Node::load($value);

        $data[$value] = array(
          'field_title' => $node->get('title')->getValue()[0]['value'],
          'field_email' => $node->get('field_email')->getValue()[0]['value'],
        );

        $roles = $node->get('field_roles')->getValue();
        foreach ($roles as $k => $v) {
          if($v['value'] === 'CEG') {
            $data[$value]['roles'][] = $v['value'];
          } else if($v['value'] === 'C') {
            $data[$value]['roles'][] = 'Client';
          } else if($v['value'] === 'D') {
            $data[$value]['roles'][] = 'Developer';
          }
        }

        if($node->get('field_is_currently_employed')->getValue()[0]['value']) {
          $data[$value]['field_is_currently_employed'] = 'TRUE';
        } else {
          $data[$value]['field_is_currently_employed'] = 'FALSE';
        }

        $apps = $node->get('field_application_list')->getValue();

        if(!empty($apps)) {
          foreach ($apps as $k => $v) {
                      // if(!empty($dev->get('field_person')->getValue())) {
            if($v['target_id'] != 0) {
              $app = Node::load($v['target_id']);
              $data[$value]['applications'][] = array(
                // 'test' => $v['target_id'],
                'nid' => $app->id(),
                'title' => $app->get('title')->getValue()[0]['value'],
              );
            }
          }
        }
      }
    }

    $response_result['data'] = $serializer->normalize($data,'json');
    $response = new JsonResponse($response_result);
    return $response;
  }

  //Persons field corresponds to developers, cegs and clients
  public function getMultiValueReferenceField($entity, $field) {
    $data = array();

    if($field === 'field_servers') {
      $servers = $entity->get('field_servers')->getValue();

      foreach ($servers as $key => $value) {
        $node = Node::load($value['target_id']);
        $data[] = array(
          'nid' => $node->id(),
          'title' => $node->get('title')->getValue()[0]['value'],
        );
      }

    } else if ($field === 'field_environments') {
      $environments = $entity->get('field_environments')->getValue();

      foreach ($environments as $key => $value) {
        $environment = Node::load($value['target_id']);

        $data[] = array(
          'nid' => $environment->id(),
          'title' => $environment->get('title')->getValue()[0]['value'],
        );
      }

    } else if ($field === 'field_applications') {
      $applications = $entity->get('field_applications')->getValue();

      foreach ($applications as $key => $value) {
        $data[] = array(
          'nid' => $value->id(),
          'title' => $value->get('title')->getValue(),
        );
      }

    } else if ($field === 'persons') {
      $developers = $entity->get('field_list_of_developers')->getValue();
      $ceg = $entity->get('field_list_of_ceg')->getValue();
      $clients = $entity->get('field_list_of_clients')->getValue();
      $person = NULL;

      $data = array(
        'developers' => array(),
        'ceg' => array(),
        'clients' => array(),
      );

      //Get list of developers
      if(!$this->isArrayEmpty($developers)) {
        foreach ($developers as $key => $value) {
          $dev = Paragraph::load($value['target_id']);

          if(!empty($dev->get('field_person')->getValue())) {
            $pid = $dev->get('field_person')->getValue()[0]['target_id'];
            $person = Node::load($pid);
            $data['developers'][] = array(
              'nid' => $pid,
              'title' => $person->get('title')->getValue()[0]['value'],
            );
          }
        }
      }

      //Get list of Ceg
      if(!$this->isArrayEmpty($ceg)) {
        foreach ($ceg as $key => $value) {
          $c = Paragraph::load($value['target_id']);

          if(!empty($c->get('field_ceg')->getValue())) {
            $pid = $c->get('field_ceg')->getValue()[0]['target_id'];
            $person = Node::load($pid);
            $data['ceg'][] = array(
              'nid' => $c->get('field_ceg')->getValue()[0]['target_id'],
              'title' => $person->get('title')->getValue()[0]['value'],
            );
          }
        }
      }

      //Get list of clients
      if(!$this->isArrayEmpty($clients)) {
        foreach ($clients as $key => $value) {
          $client = Paragraph::load($value['target_id']);

          if(!empty($client->get('field_client')->getValue())) {
            $pid = $client->get('field_client')->getValue()[0]['target_id'];
            $person = Node::load($pid);
            $data['clients'][] = array(
              'nid' => $client->get('field_client')->getValue()[0]['target_id'],
              'title' => $person->get('title')->getValue()[0]['value'],
            );
          }
        }
      }
    }

    return $data;
  }

  //Used to determine if a 2d array is empty
  public function isArrayEmpty($array) {
    if(array_keys($array)) {
      foreach (array_keys($array) as $key => $value) {
        if(!empty($array[$value])) {
          return FALSE;
        }
      }

      return TRUE;
    }

    return FALSE;
  }
}
 ?>
