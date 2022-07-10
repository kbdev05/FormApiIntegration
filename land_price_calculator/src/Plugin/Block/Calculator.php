<?php

namespace Drupal\land_price_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 *
 *
 * @Block(
 *   id = "land_price_calculator",
 *   admin_label = @Translation("Land Price Calculator")
 * )
 */
class Calculator extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\land_price_calculator\Calculator
   */
  protected $calculatorservice;

  /**
   * Calculator constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param $calculator_api \Drupal\land_price_calculator\CalculatorApi
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $calculator_api) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->calculatorservice = $calculator_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('calculator_api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    try {
    $formaddress = \Drupal::formBuilder()->getForm('Drupal\land_price_calculator\Form\AddressForm');
    return [
      '#theme' => 'land_price_calculator',
      '#addressform' => $formaddress ,
    ];
  }
  catch (GuzzleException $error) {
    // Get the original response
    $response = $error->getResponse();
    $response_info = $response->getBody()->getContents();
    return [
      '#theme' => 'land_price_calculator',
      '#addressform' => $response_info ,
    ];
  }

  }

}
