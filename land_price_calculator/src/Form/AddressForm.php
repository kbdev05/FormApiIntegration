<?php
/**
 * @file
 * Contains \Drupal\land_price_calculator\Form\AddressForm.
 */

namespace Drupal\land_price_calculator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\HtmlCommand;

class AddressForm extends FormBase
{
    protected $formBuilder;

    public function __construct(FormBuilderInterface $form_builder)
    {
        $this->formBuilder = $form_builder;
    }

    public static function create(ContainerInterface $container): AddressForm
    {
        return new static(
      $container->get('form_builder')
    );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'land_address_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['search_address'] = array(
      '#type' => 'textfield',
      '#title' => t('<span class="icon icon--pin"></span>Enter the address'),
      '#placeholder' => t('Enter the address to see if you are in a SIC area'),
      '#attributes' => array(
           'class' => array('input__text--wide'),
           ),
      '#autocomplete_route_name' => 'land_price_calculator.autocomplete',
      '#ajax' => [
       'callback' => [$this, 'showRegionDialog'],
       'event' => 'autocompleteclose',
       'progress' => [
         'type' => 'throbber',
        //  'message' => t('Verifying entry...'),
       ],
       ['query' => ['ajax_form' => 1]],
      ],
      );
//         $form['fieldset1'] = array(
        //  '#type' => 'webform_more',
//      '#attributes' => array(
//           'class' => array('spacing--bottom-l'),
//           ),
        //  '#more_title' => 'Note',

        //  '#more' => '<div class="webform-section"><p>If the entered address is not recognised as a SIC area. The system should provide the following message ‘This address is not recognised by the calculator to be inside a current SIC area. Please refer to the <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a> for further information.</p></div>',
        //  );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }

    public function showRegionDialog(array &$form, FormStateInterface $form_state): AjaxResponse
    {
        $response = new AjaxResponse();
        $messages = ['#type' => 'status_messages'];
        $response->addCommand(new PrependCommand('.section--content-top', $messages));
        if (!$form_state->getErrors()) {
            $service = \Drupal::service('calculator_api');
            $land_filter = $form_state->getValue('search_address');
            $propdata = $service->addresslist($land_filter);
            foreach ($propdata as $data) {
                if ($data['address'] == $land_filter) {
                    $property_id = $data['propId'];
                }
            }

            $responsedata = $service->projectdetails($property_id);
            //$responsedata = $service->projectdetails(3663427);

            //Check if SIC region or not
            $checkSic = AddressForm::checkSic($responsedata, 'id','218');// layerName = Special Infrastructure Contributions
            if (true === $checkSic) {
                $calulator_data = [];
               
                $sick_region = AddressForm::getSicRegion($responsedata, 'id','218');// layerName = Special Infrastructure Contributions
                
                $sick_zone = AddressForm::getSicZone($responsedata,'id', '19'); // layerName = Land Zoning
                $sick_zonelanduse = AddressForm::getSicZoneLandUse($responsedata,'id', '19'); // layerName = Land Zoning
                $sick_presinct = AddressForm::getSicPresinct($responsedata, 'layerName', 'Precinct Boundaries');
                
                $zone = AddressForm::checkZone($sick_zone);
                // Make Sick form variables as per layers data

                $calulator_data['region'] = $sick_region['area'];
                $calulator_data['zone'] = $sick_zone;
                $calulator_data['zone_land_use'] = $zone;

                if ('Wyong Employment Zone SIC' == $sick_region['area'] || 'Warnervale Town Centre SIC' == $sick_region['area']) {
                    $calulator_data['presinct'] = 'Others';
                } elseif ('Illawarra Shoalhaven SCA' == $sick_region['area']) {
                    $ilsh_sick_presinct = AddressForm::getILSHSicPresinct($responsedata, 'id','218');// layerName = Special Infrastructure Contributions
                    $calulator_data['presinct'] = isset($ilsh_sick_presinct) ? $ilsh_sick_presinct : 'Not subject to precinct';
                    $calulator_data['subgrowth_area'] = AddressForm::getILSHSubGrowthAreas($responsedata, 'id','218');// layerName = Special Infrastructure Contributions
                } else {
                    $calulator_data['presinct'] = isset($sick_region['precinct']) ? $sick_region['precinct'] : isset($sick_presinct) ? $sick_presinct : 'Not subject to precinct';
                }

                $rebuilt_form = $this->formBuilder->rebuildForm('land_address_form', $form_state, $form);
                $response->addCommand(new ReplaceCommand('#land_address_form', $rebuilt_form));

                // Open the form in a modal dialog.

                $title = $this->t('Enter Your Property Details ');

                // Check the region
                if ('Western Sydney Growth Centres SIC' == $sick_region['area'] || 'Wyong Employment Zone SIC' == $sick_region['area'] || 'Warnervale Town Centre SIC' == $sick_region['area']) {
                    if (false === $zone) {
                        $response->addCommand(new HtmlCommand('#sic-form-section', '<p><b><span class="icon icon--alert"></span>This address is not recognised by the calculator to be inside a current SIC area. Please refer to the <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a> for further information.</b><p/>'));
                    } else {
                        $modal_form = $this->formBuilder->getForm('\Drupal\land_price_calculator\Form\SicFormOne', $calulator_data);
                        $modal_form['#attached']['library'][] = 'core/drupal.dialog.ajax';
                        // Return form without Modal popup
                        $response->addCommand(new HtmlCommand('.user-sic-data', ''));

                        $response->addCommand(new HtmlCommand('#sic-form-section', $modal_form, ['width' => '200']));
                        //Return Form with Modal Dialog
 //$response->addCommand(new OpenModalDialogCommand($title, $modal_form, ['width' => '500']));
                    }
                } elseif ('Gosford City Centre SIC' == $sick_region['area']) {
                    $modal_form = $this->formBuilder->getForm('\Drupal\land_price_calculator\Form\SicFormTwo', $calulator_data);
                    $modal_form['#attached']['library'][] = 'core/drupal.dialog.ajax';
                    $response->addCommand(new HtmlCommand('.user-sic-data', ''));
                    //$response->addCommand(new OpenModalDialogCommand($title, $modal_form, ['width' => '500']));
                    $response->addCommand(new HtmlCommand('#sic-form-section', $modal_form, ['width' => '200']));
                } elseif ('Illawarra Shoalhaven SCA' == $sick_region['area']) {
                    $modal_form = $this->formBuilder->getForm('\Drupal\land_price_calculator\Form\SicFormThree', $calulator_data);
                    $modal_form['#attached']['library'][] = 'core/drupal.dialog.ajax';
                    $response->addCommand(new HtmlCommand('.user-sic-data', ''));
                    $response->addCommand(new HtmlCommand('#sic-form-section', $modal_form, ['width' => '200']));
                } else {
                    $response->addCommand(new HtmlCommand('#sic-form-section', '<p><b><span class="icon icon--alert"></span>This address is not recognised by the calculator to be inside a current SIC area. Please refer to the <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a> for further information.</b><p/>'));
                }
            } else {
                $response->addCommand(new HtmlCommand('.user-sic-data', ''));
                $response->addCommand(new HtmlCommand('#sic-form-section', '<p><b><span class="icon icon--alert"></span>This address is not recognised by the calculator to be inside a current SIC area. Please refer to the <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a> for further information.</b><p/>'));
            }
        }

        return $response;
    }

    public static function checkSic($array, $key, $val)
    {
        foreach ($array as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                return true;
            }
        }

        return false;
    }

    public static function getSicRegion($layers, $key, $val)
    {
        $sicdata = [];
        foreach ($layers as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                if ('Balmoral Road' == $item['results'][0]['title'] || 'Elderslie' == $item['results'][0]['title'] || 'Spring Farm' == $item['results'][0]['title']) {
                    $sicdata['precinct'] = $item['results'][0]['title'];
                    $sicdata['area'] = $item['results'][0]['Area'];
                } else {
                    $sicdata['area'] = $item['results'][0]['Area'];
                }
            }
        }

        return $sicdata;
    }

    public static function getSicZone($layers, $key, $val)
    {
        $zones = [];
        foreach ($layers as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                foreach ($item['results'] as $zone) {
                    $zones[] = $zone['Zone'];
                }
            }
        }

        return $zones;
    }

    public static function getSicZoneLandUse($layers, $key, $val)
    {
        foreach ($layers as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                return $item['results'][0]['Land Use'];
            }
        }
    }

    public static function getSicPresinct($layers, $key, $val)
    {
        foreach ($layers as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                return $item['results'][0]['PRECINCT'];
            }
        }
    }
    
    public static function getILSHSubGrowthAreas($layers, $key, $val)
    {
        $areas = [];
        foreach ($layers as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                foreach ($item['results'] as $area) {
                    $areas[$area['title']] = $area['title'];
                }
            }
        }
        return $areas;
    }
    
    public static function getILSHSicPresinct($layers, $key, $val)
    {
        foreach ($layers as $item) {
            if (isset($item[$key]) && $item[$key] == $val) {
                return $item['results'][0]['title'];
            }
        }
    }

    public static function checkZone($zone)
    {
        $zomemapping = ['R2' => 'Residential',
        'R1' => 'Residential',
        'R2' => 'Residential',
        'R3' => 'Residential',
        'R4' => 'Residential',
        'RE2' => 'Residential',
        'R1' => 'Residential',
        'R2' => 'Residential',
        'RE2' => 'Residential',
        'R1' => 'Residential',
        'R2' => 'Residential',
        'R3' => 'Residential',
        'R4' => 'Residential',
        'R5' => 'Residential',
        'RE2' => 'Residential',
        'B5' => 'Industrial',
        'B7' => 'Industrial',
        'B4' => 'Industrial',
        'B3' => 'Industrial',
        'IN1' => 'Industrial',
        'IN2' => 'Industrial',
        'E4' => 'Residential',
        'R1' => 'Residential',
        'R2' => 'Residential',
        'R3' => 'Residential',
        'R5' => 'Residential',
        'RE2' => 'Residential',
        'IN1' => 'Industrial',
        'IN2' => 'Industrial',
        'B5' => 'Industrial',
        'B7' => 'Industrial',
        'E4' => 'Residential',
        'R1' => 'Residential',
        'R2' => 'Residential',
        'IN1' => 'Industrial', ];
        $values = [];
        foreach ($zomemapping as $key => $value) {
            foreach ($zone as $zoneval) {
                if ($key == $zoneval) {
                    $values[$value] = $key.'-'.$value;
                }
            }
        }

        return $values;
    }
}
