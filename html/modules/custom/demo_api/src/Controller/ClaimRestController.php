<?php

namespace Drupal\demo_api\Controller;

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

class ClaimRestController extends ControllerBase {
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

  //Function for demo_api.get_all_excemption from demo_api.routing.yml
  public function getAllExemption() {
    $limit = NULL;
    if(\Drupal::request()->query->get('limit')) {
      $limit = \Drupal::request()->query->get('limit');
    }

    $offset = NULL;
    if(\Drupal::request()->query->get('offset')) {
      $offset = \Drupal::request()->query->get('offset');
    }

    $nodeID = 0;
    if(\Drupal::request()->query->get('node_id')) {
      $nodeID = \Drupal::request()->query->get('node_id');
    }

    \Drupal::logger('demo_api')->notice($nodeID);

    $data = [];//Used to store the data to be returned
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $DBQuery = \Drupal::entityQuery('node')
            ->condition('type','exemption_claim')
            ->range($offset,$limit);

    if($nodeID) {
      $DBQuery->condition('nid',$nodeID);
    }


    //Query to get the data from the DB
    $nids = $DBQuery->execute();

    //Load the nodes and count how many will be returned
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
    $count = count($nids);

    foreach ($nodes as $key => $value) {
      $data[$value->id()] = array(
        'field_claimant_name' => $value->get('field_claimant_name')->getValue(),
        'field_date_of_decision' => $value->get('field_date_of_decision')->getValue(),
        'field_date_of_filing' => $value->get('field_date_of_filing')->getValue(),
        'field_date_order_iossued' => $value->get('field_date_order_iossued')->getValue(),
        'field_decision_on_claim_validity' => $value->get('field_decision_on_claim_validity')->getValue(),
        'field_decision_on_compliance' => $value->get('field_decision_on_compliance')->getValue(),
        'field_expiry_date_of_valid_claim' => $value->get('field_expiry_date_of_valid_claim')->getValue(),
        'field_list_of_sds_non_compliance' => $value->get('field_list_of_sds_non_compliance')->getValue(),
        'field_order_issued_' => $value->get('field_order_issued_')->getValue(),
        'field_product_identifier' => $value->get('field_product_identifier')->getValue(),
        'field_registry_number' => $value->get('field_registry_number')->getValue(),
        'field_status_of_the_order' => $value->get('field_status_of_the_order')->getValue()
      );
    }

    //Serialize the data into JSON format and add it to the response
    $response_result['count'] = $count;
    // $response_result['data'] = $serializer->serialize($data, "json", ['plugin_id' => 'node']);
    $response_result['data'] = $serializer->normalize($data,'json');

    // $json = Json::encode($data);
    // $response_result['data'] = $json;


    $response = new JsonResponse($response_result);
    // $response->addCacheableDependency($response_result);//Required to avoid error

    return $response;
  }

  //Function for demo_api.get_documentation from demo_api.routing.yml
  public function claimsDocumentation() {
    $documentation = array();
    // $documentation = "Placeholder for documentation";
    // $serializer = \Drupal::service('serializer');
    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(new JsonEncoder()));//Used to serialize the data in JSON format

    $documentation['General Information'] = "This API allows you to request information about claims held in the system. To request all claims simply making a GET request to 'localhost/api/get_claims?_format=json'.";
    $documentation['Limit'] = "To limit the number of claims you request, add '&limit=X' to the URL where 'X' is the number of claims you wish to get";
    $documentation['Offset'] = "To skip over a certain number of records, add '&offset=Y' to the URL where 'Y' is the number of claims you wish to ignore";
    $documentation['Node_ID'] = "To request a specific claim, add '&node_id={ID}' to the URL where {ID} is the node ID of the claim";
    $documentation['Fields'] = "To get the list of fields available make a GET request to 'localhost/api/claim/fields?_format=json'";
    $documentation['Fields_details'] = "To get a detailed description of a field make a GET request to 'localhost/api/claims/fields_details?_format=json&field={field_name}' where '{field_name}' is the name of the field you are interested in.";

    $doc = $serializer->normalize($documentation,'json');

    $response = new JsonResponse($doc);
    // $response->addCacheableDependency($documentation);

    return $response;
  }

  //Function for demo_api.get_field_details from demo_api.routing.yml
  //Returns specific details of a given field
  public function claimFields() {
    $fieldDetail = array();


    return $response;
  }

  //Function for demo_api.get_field_list from demo_api.routing.eio_symlink
  public function claimFieldList() {
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldDefinitions('node','exemption_claim');
    $fieldList = array();

    //Get a list of all fields usable by the API
    foreach ($fields as $key => $value) {
      if(strpos($key,'field_') !== FALSE) {
        $fieldList[] = $key;
      }
    }

    $response = new JsonResponse($fieldList);

    return $response;
  }

  public function submitClaim() {
    $body = \Drupal::request()->getContent();

    $content = json_decode($body,TRUE);

    $content_type = $content['content_type'][0]['value'];

    if($content_type === 'exemption_claim') {
      $paragraphs = NULL;

      // Check if a value was entered for non compliance field - stored as a paragraph
      if(!empty($content['field_list_of_sds_non_compliance'])) {
        // Create a new paragraph
        // We create the paragraph first to add the paragraph as a field to the node
        $paragraphs = Paragraph::create([
          'type' => 'sds_non_compliances',
        ]);

        // Set fields
        $paragraphs->set('field_non_compliances',$content['field_list_of_sds_non_compliance'][0]['field_non_compliances']);
        $paragraphs->set('field_corrective_measures',$content['field_list_of_sds_non_compliance'][0]['field_corrective_measures']);
        $paragraphs->isNew();
        $paragraphs->save();
      }

      //Create a new node
      $node = Node::create(
        array(
          'type' => 'exemption_claim',
          'title' => 'Exemption Claim',
          'field_claimant_name' => $content['field_claimant_name'][0]['value'], // Text
          'field_date_of_decision' => $content['field_date_of_decision'][0]['value'], // Date
          'field_date_of_filing' => $content['field_date_of_filing'][0]['value'], // Date`
          'field_date_order_iossued' => $content['field_date_order_iossued'][0]['value'], // Date
          'field_decision_on_claim_validity' => $content['field_decision_on_claim_validity'][0]['target_id'], //Entity Reference - taxonomy
          'field_decision_on_compliance' => $content['field_decision_on_compliance'][0]['target_id'], // Entity Reference - taxomy
          'field_expiry_date_of_valid_claim' => $content['field_expiry_date_of_valid_claim'][0]['value'], // Date
          'field_list_of_sds_non_compliance' => $paragraphs, // Paragraph
          'field_order_issued_' => $content['field_order_issued_'][0]['value'], // Boolean
          'field_product_identifier' => $content['field_product_identifier'][0]['value'], // Text
          'field_registry_number' => $content['field_registry_number'][0]['value'], // Integer
          'field_status_of_the_order' => $content['field_status_of_the_order'][0]['value'], // Text
        )
      );

      $node->save();
    }

    $result = [
      'message' => 'New node created',
      'nid' => $node->id(),
      'url' => $node->url(),
    ];

    // $body = [];
    $response = new JsonResponse($result);

    return $response;
  }

  public function access(AccountInterface $account) {
    //add logic

    return AccessResult::allowedIf($account->getAccount()->hasRole('administrator'));
  }
}
