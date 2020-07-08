<?php

namespace Drupal\cop_hpp_form\Form;

//Core libraries
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Routing\RedirectResponse;

//Commerce libraries
use Drupal\commerce_order\Form\CustomerFormTrait;
use Drupal\commerce_order\Order;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
//Guzzle Libraries
use Guzzle\Core\Url as GuzzleUrl;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;


/**
 * Provides the order add form.
 */
class PaymentForm extends FormBase {
  protected $order;
  protected $customer;

  public function __construct(CurrentRouteMatch $current_route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->order = $current_route_match->getParameter('commerce_order');
    $this->userStorage = $entity_type_manager->getStorage('user');
  }

  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'hosted_pay_page_redirect_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $customer = $this->order->getCustomer();

    // kint($customer->language()->getId());
    // kint($customer->get('field_first_name')->getValue()[0]['value']);
    // kint($this->order->get('total_price')->getValue()[0]);

    $form['ps_store_id'] = array(
      '#type' => 'textarea',
      '#title' => 'Store ID',
      '#default_value' => 'A2RGRtore3',
      '#required' => TRUE
    );

    $form['hpp_key'] = array(
      '#type' => 'textarea',
      '#title' => 'Store Key',
      '#default_value' => 'hpUJR27FMKBH',
      '#required' => TRUE
    );

    $form['charge_total'] = array(
      '#type' => 'textarea',
      '#title' => 'Total Price',
      '#default_value' => $this->order->get('total_price')->getValue()[0]['number'],
      '#required' => TRUE
    );

    $form['cust_id'] = array(
      '#type' => 'textarea',
      '#title' => 'Customer ID',
      '#default_value' => $customer->get('uid')->getValue()[0]['value'],
      '#required' => TRUE
    );

    $form['order_id'] = array(
      '#type' => 'textarea',
      '#title' => 'Order ID',
      '#default_value' => $this->order->get('order_id')->getValue()[0]['value'],
      '#required' => TRUE
    );

    $form['lang'] = array(
      '#type' => 'textarea',
      '#title' => 'Language',
      '#default_value' => $customer->language()->getId(),
      '#required' => TRUE
    );

    $form['gst'] = array(
      '#type' => 'textarea',
      '#title' => 'GST',
      '#default_value' => 'Calculate GST from order object',
      '#required' => TRUE
    );

    $form['pst'] = array(
      '#type' => 'textarea',
      '#title' => 'PST',
      '#default_value' => 'Calculate PST from order object',
      '#required' => TRUE
    );

    $form['hst'] = array(
      '#type' => 'textarea',
      '#title' => 'HST',
      '#default_value' => 'Calculate HST from order object',
      '#required' => TRUE
    );

    //Services only? or is shipping cost calculated by us?
    $form['shipping_cost'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipping Cost',
      '#default_value' => '0.0',
      '#required' => TRUE
    );

    //Hosted Pay Page should override this value - Doesn't matter
    $form['ECI'] = array(
      '#type' => 'textarea',
      '#title' => 'ECI',
      '#default_value' => '1',
      '#required' => TRUE
    );

    $form['first_name'] = array(
      '#type' => 'textarea',
      '#title' => 'First Name',
      '#default_value' => $customer->get('field_first_name')->getValue()[0]['value'],
      '#required' => TRUE
    );

    $form['last_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Last Name',
      '#default_value' => $customer->get('field_last_name')->getValue()[0]['value'],
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['company_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Company Name',
      '#default_value' => 'Get company name from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['billing address'] = array(
      '#type' => 'textarea',
      '#title' => 'Billing Address',
      '#default_value' => 'Get billing address from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['city'] = array(
      '#type' => 'textarea',
      '#title' => 'City',
      '#default_value' => 'Get city from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['state_province'] = array(
      '#type' => 'textarea',
      '#title' => 'State/Province',
      '#default_value' => 'Get state or province info from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['postal_code'] = array(
      '#type' => 'textarea',
      '#title' => 'Postal code',
      '#default_value' => 'Get postal code from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['phone_number'] = array(
      '#type' => 'textarea',
      '#title' => 'Phone Number',
      '#default_value' => 'Get phone number from customer object',
      '#required' => TRUE
    );

    //Need to add company info to user class
    $form['fax_number'] = array(
      '#type' => 'textarea',
      '#title' => 'Fax Number',
      '#default_value' => 'Get fax number from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_first_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment First Name',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_last_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Last Name',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_company_name'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Company Name',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_address'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment address',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_city'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment City',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_state_province'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment State/Province',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_postal_code'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Postal Code',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_country'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment Country',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_telephone'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment telephone',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    //If shipping address is different than company address / details
    $form['shipment_fax_number'] = array(
      '#type' => 'textarea',
      '#title' => 'Shipment fax number',
      '#default_value' => 'Get data from customer object',
      '#required' => TRUE
    );

    $form['actions']['#type'] = 'actions';
    // kint($form['actions']);
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Make Payment'),
      '#button_type' => 'primary',
    );

    $form['#redirect_url'] = 'https://esqa.moneris.com/HPPDP/index.php';
    // unset($form['#attached']['library']);
    //
    // $data['store_id'] = 'A2RGRtore3';
    // $data['store_key'] = 'hpUJR27FMKBH';
    // $data['total_charge'] = $this->order->get('total_price')->getValue()[0]['number'];
    // $data['customer_id'] = $customer->get('uid')->getValue()[0]['value'];
    // $data['order_id'] = $this->order->get('order_id')->getValue()[0]['value'];
    // $form = PaymentOffsiteForm::buildRedirectForm($form,$form_state,$host,$data,'POST');
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $method = 'POST';
    // $client = new GuzzleClient();
    // $url = 'https://esqa.moneris.com/HPPDP/index.php';
    // $header = array();
    //
    // $body;
    //
    // $request = new GuzzleRequest($method,$url,$headers)

    $host = 'https://esqa.moneris.com/HPPDP/index.php';

    // dpm($form_state->getValue('store_id'));

    // $fields = array();
    $fields = array (
      'ps_store_id' => $form_state->getValue('ps_store_id'),
      'hpp_key' => $form_state->getValue('hpp_key'),
      'charge_total' => $form_state->getValue('charge_total'),
      'cust_id' => $form_state->getValue('cust_id'),
      'order_id' => $form_state->getValue('order_id'),
      'lang' => $form_state->getValue('language'),
      'gst' => $form_state->getValue('gst'),
      'pst' => $form_state->getValue('pst'),
      'hst' => $form_state->getValue('hst'),
      'shipping_cost' => $form_state->getValue('shipping_cost'),
      'ECI' => $form_state->getValue('ECI')
    );

    $headers = array(
      'Content-type' => 'application/x-www-form-urlencoded',
      // 'location' => $host
    );

    // $response = \Drupal::httpClient()->post($host,['form_params' => $fields]);

    $response = \Drupal::httpClient()->post($host, [
      'form_params' => $fields,
      'headers' => [
        'Content-type' => 'application/x-www-form-urlencoded',
      ],
      'allow_redirects' => TRUE,
      'Accept' => 'text/html',
    ]);

    $redirect = new TrustedRedirectResponse($host,302,$headers);//,302,$fields,$headers);//,302,$headers);
    $form_state->setResponse($redirect);
    // $post = \Drupal::request();
    // $post->server->set('REQUEST_URI',$host);
    // $post->headers->set('host',$host);
    // kint($post);
    // die();


    // $response->send();
    // $form_state->setResponse($response);

    // kint($response);
    // kint($form_state);
    // die();
    // kint($response);
    // kint($response->getBody());
    // kint($response->getBody()->getContents());
    // $form_state->setResponse($response->getBody()->getContents());
    // $form_state->setRedirect($response->getBody()->getContents());
    // die();

    // kint($redirect);
    // // $redirect->setRouteParameters($fields);
    // $form_state->setRedirect($redirect);
    // $redirect = $form_state->getRedirect();
    // $redirect->setRouteParameters($fields);
    // // $form_state->setRedirect(NULL);
    // kint($redirect);
    // kint($form_state);
    // die();
    //
    // $form_state->setRedirect($host,$fields,$headers);

    // $url = Url::fromUri($host);
    // $redirect = new TrustedRedirectResponse($host);
    // $redirect->setContent($fields);
    // kint($redirect);
    // die();
    // $url->setRouteParameters($fields);
    // kint($url);
    // kint($url->isExternal());
    // $form_state->setResponse($redirect);
    // die();


    // kint($fields);
    // die();
    //
    // $client = \Drupal::httpClient();
    // kint($client);
    // die();
    //
    // $status = $response->getStatusCode();

    // $request = new GuzzleRequest('POST',$host,$headers);//,$fields);
    // kint($request);

    // $client = new GuzzleClient(['base_uri' => $host]);
    // kint($client);
    // $options = array(
    //   'form_params' => $fields,
    //   'headers' => $headers,
    // );
    // $response = $client->post($options);
    //
    // kint($response);
    //
    // die();
    // $response = \Drupal::httpClient()->post('https://esqa.moneris.com/HPPDP/index.php', [
    //   'form_params' => $fields,
    //   'headers' => [
    //     'Content-type' => 'application/x-www-form-urlencoded',
    //   ],
    //   'allow_redirects' => TRUE
    // ]);

    // kint($response->getBody());
    // kint($response->getStatusCode());
        // die();

    // $sResponse = new Response($response->getBody(),$response->getStatusCode(),$headers);
    // kint($sResponse);
    // die();

    // kint($response);
    // kint($response->getBody()->__toString());
    // die();
    // $form_state->setResponse($sResponse);
    // kint($form_state);
    // $client = \Drupal::httpClient();
    // die();
    // kint($response);
    // kint($form_state);
    // kint($response->getBody());
    // die();
    // kint($response->getBody()->getMetadata());
    // $uri = $response->getBody()->getMetadata()['uri'];
    // $url = Url::fromUri($host);
    // kint($url);
    // kint($response->getBody()->getContents());
    // kint($response->getBody()->__toString());
    // die();
    // $headers = array();

    // $form_state->setResponse($response);

    // $form_state->disableRedirect();
    // $redirect = new TrustedRedirectResponse($host);//,$fields,$headers);
    // kint($redsirect);
    // die();
    // $metadata = $redirect->getCacheableMetadata();
    // $metadata->setCacheMaxAge(0);
    // $redirect->send();

    // $form_state->setResponse($redirect);
    // kint($redirect->getContent()  );
    // die();
    // $form_state->setResponse($response);

    // kint($url->toString());
    // die();
    // $form_state->setResponse($url);

    // $form_state->disableRedirect();
    // kint($form_state);
    // kint($form_state->getRedirect());
    // kint($form_state->isRedirectDisabled());
    //
    // die();
  }
}
 ?>
