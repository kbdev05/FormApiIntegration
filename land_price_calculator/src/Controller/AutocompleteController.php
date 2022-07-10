<?php

namespace Drupal\land_price_calculator\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Form\FormState;

/**
 * Returns responses for Autocomplete Address form field.
 */

class AutoCompleteController extends ControllerBase
{
  /**
     * {@inheritdoc}
     */
    private $calculatorapi;
    public function __construct($calculatorapi)
    {
        $this->calculatorapi = $calculatorapi;
    }

    public static function create(ContainerInterface $container)
    {
        $calculatorapi = $container->get('calculator_api');
        return new static($calculatorapi);
    }

    public function handleAutocomplete(Request $request) {
        $results = [];
        $input = $request->query->get('q');
   // Get the typed string from the URL, if it exists.
        if (!$input) {
          return new JsonResponse($results);
        }

        $input = Xss::filter($input);

        $response = $this->calculatorapi->addresslist($input);

        $matches = []; 
       foreach($response as $data){
        $matches[] = ['value' => $data['address'], 'label' => $data['address']];

       }
    
        return new JsonResponse($matches);
    }
    
}