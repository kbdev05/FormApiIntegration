<?php
/**
 * @file
 * Contains \Drupal\land_price_calculator\Form\SicFormTwo.
 */

namespace Drupal\land_price_calculator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Form\FormElementHelper;

class SicFormTwo extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'sic_form_two';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $data = null)
    {
        $form['#prefix'] = '<div id="sicformtwo">';
        $form['#suffix'] = '</div>';

        $form['case_type'] = array(
      '#type' => 'textfield',
      '#value' => 'SIC',
      '#attributes' => ['hidden' => 'hidden'],
    );

        $form['region_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Region'),
      '#default_value' => $data['region'],
        '#placeholder' => t('Special Infrastructure Contributions Area'),
      '#attributes' => ['readonly' => 'readonly'],
    );
        $form['fieldset12'] = array(
 '#type' => 'webform_more',
     '#attributes' => array(
           'class' => array('spacing--bottom-l'),
           ),
 '#more_title' => 'Note',

 '#more' => '<div class="webform-section"><p>SICs have been implemented in several areas in New South Wales for further information refer to the <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a> .</p></div>',
 );

        if (!empty($data['zone_land_use']) && count($data['zone_land_use']) > 1) {
            $form['zone'] = array(
      '#type' => 'select',
      '#title' => t('Zoning : This site has multiple SIC payable zones.'),
     '#options' => $data['zone_land_use'],
     );
            $form['fieldset1'] = array(
 '#type' => 'webform_more',
     '#attributes' => array(
           'class' => array('spacing--bottom-l'),
           ),
 '#more_title' => 'Note',

 '#more' => '<div class="webform-section"><p>Residential and Industrial zonings have different contribution rates this calculator will provide an estimate for one zoning at a time.</p></div>',
 );
        } elseif (!empty($data['zone_land_use'])) {
            $form['zone'] = array(
      '#type' => 'select',
      '#title' => t('Zoning'),
     '#options' => $data['zone_land_use'],
     );
            $form['fieldset1'] = array(
 '#type' => 'webform_more',
     '#attributes' => array(
           'class' => array('spacing--bottom-l'),
           ),
 '#more_title' => 'Note',

 '#more' => '<div class="webform-section"><p>Residential and Industrial zonings have different contribution rates this calculator will provide an estimate for one zoning at a time.</p></div>',
 );
        } else {
            $form['zone'] = array(
      '#type' => 'hidden',
      '#title' => t('Zone.'),
      '#default_value' => $data['zone_land_use'],
     );
        }
        $form['total_development_cost'] = array(
      '#type' => 'number',
      '#description' => '<div class="total_development_cost"></div>',
      '#title' => t('Enter total development cost'),
      '#required' => true,
    );
        $form['fieldset1'] = array(
 '#type' => 'webform_more',
     '#attributes' => array(
           'class' => array('spacing--bottom-l'),
           ),
 '#more_title' => 'What is this ?',

 '#more' => '<div class="webform-section">Only certain land use zonings are liable to a SIC. Further information can be found in the relevant Special Infrastructure Contributions Area Determination found in <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a></div>
',
);
        $form['total_gross_floor_area'] = array(
      '#type' => 'textfield',
      '#description' => '<div class="total_gross_floor_area"></div>',
      '#title' => t('Enter the total gross floor area of the development (in square metres)'),
      '#required' => true,
    );
        $form['fieldset2'] = array(
 '#type' => 'webform_more',
'#more_title' => 'What is this ?',
    '#attributes' => array(
           'class' => array('spacing--bottom-l'),
           ),
 '#more' => '<div class="webform-section"><p>Gross floor area means the sum of the floor area of each floor of a building measured from the internal face of external walls, or from the internal face of walls separating the building from any other building, measured at a height of 1.4 metres above the floor, and includes:<br /> </p>
<ul>
<li>the area of a mezzanine, and</li>
<li>habitable rooms in a basement or an attic, and</li>
<li>any shop, auditorium, cinema, and the like, in a basement or attic,<br /> but excludes:</li>
<li>any area for common vertical circulation, such as lifts and stairs, and</li>
<li>any basement:</li>
<li>storage, and</li>
<li>vehicular access, loading areas, garbage and services, and</li>
<li>plant rooms, lift towers and other areas used exclusively for mechanical services or ducting,</li>
<li>car parking to meet any requirements of the consent authority (including access to that car parking), and</li>
<li>any space used for the loading or unloading of goods (including access to it), and</li>
<li>terraces and balconies with outer walls less than 1.4 metres high, and</li>
<li>voids above a floor at the level of a storey or storey above.</li>
</ul></div>',
);
        $form['exempted_gross_floor_area'] = array(
      '#type' => 'hidden',
      '#step' => .1,
      '#default_value' => 0,
      '#description' => '<div class="exempted_gross_floor_area"></div>',
      '#title' => t('Enter exempted gross floor area (in square metres)'),
      '#required' => true,
    );
//         $form['fieldset3'] = array(
        //  '#type' => 'webform_more',
//      '#attributes' => array(
//           'class' => array('spacing--bottom-l'),
//           ),
        // '#more_title' => 'What is this ?',
        //  '#more' => '<div class="webform-section"><p>Where the development includes the proposed use of a building for a designated community purpose, as well as for another purpose, the costs of the development (as calculated under clause 10) can be reduced by an amount calculated by multiplying those costs by the following: gross floor area of building for designated community purpose/gross floor area of building.</p></div>',
        // );
        $form['request_source_system'] = array(
      '#type' => 'textfield',
      // '#title' => t('Request Source System'),
      '#default_value' => 'Drupal',
      '#attributes' => ['hidden' => 'hidden'],
    );

        // $form['actions']['cancel'] = [
        //   '#type' => 'submit',
        //   '#value' => $this->t('Cancel'),
        //   '#attributes' => [
        //     'class' => ['dialog-cancel'],
        //   ],
        // ];

        $form['actions']['confirm'] = [
      '#type' => 'submit',
      '#value' => $this->t('Calculate the contribution'),
      '#suffix' => '<strong ><p class="spacing--top-m"><span class="icon icon--alert"></span><i>The provided contribution value is an estimate only. To confirm the estimate a full assessment is required.</i></p></strong>',
       '#ajax' => [
        'callback' => [$this, 'calculatePrice'],
        'url' => Url::fromRoute('land_price_calculator.sick_form_two'),
        'options' => [
          'query' => [
            FormBuilderInterface::AJAX_FORM_REQUEST => true,
          ],
        ],
      ],
    ];
        $form['actions']['confirm']['#ajax']['options']['query'] += \Drupal::request()->query->all();

        $form['#action'] = Url::fromRoute('land_price_calculator.sick_form_two')->toString();
        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (empty($form_state->getValue('total_gross_floor_area')) || !is_numeric($form_state->getValue('total_gross_floor_area')) || preg_match('/\.\d{5,}/', $form_state->getValue('total_gross_floor_area'))) {
            $form_state->setErrorByName('total_gross_floor_area', $this->t('Please Enter a Valid Value.'));
        }
    }

    public function calculatePrice(array &$form, FormStateInterface $form_state): AjaxResponse
    {
        $response = new AjaxResponse();
        if ($form_state->hasAnyErrors()) {
            $status_messages = array('#type' => 'status_messages');
            $messages = \Drupal::service('renderer')->renderRoot($status_messages);
            $response->addCommand(new PrependCommand('.landing-errors', $messages));

            //Hiding the Drupal status messages for this form after rendering them
            $response->addCommand(new CssCommand('.landing-errors', ['border' => '3px solid red', 'display' => 'none']));
            $css = ['border' => '2px solid red'];
            $errors = $form_state->getErrors();

            // Loop through all form errors and check if we need to display a link.
            foreach ($errors as $name => $error) {
                $form_element = FormElementHelper::getElementByName($name, $form);
                $title = FormElementHelper::getElementTitle($form_element);
                if ('Enter total development cost' == $title->__toString()) {
                    $message = $this->t('Please enter valid total Development Cost.');
                    $response->addCommand(new CssCommand('input[name="total_development_cost"]', $css));
                    $response->addCommand(new HtmlCommand('.total_development_cost', $message));
                }
                if ('Enter the total gross floor area of the development (in square metres)' == $title->__toString()) {
                    $message = $this->t('Please Enter Valid Total Gross Floor Area.');
                    $response->addCommand(new CssCommand('input[name="total_gross_floor_area"]', $css));
                    $response->addCommand(new HtmlCommand('.total_gross_floor_area', $message));
                }
            }
        } else {
            $datajson = [];
            $datajson['CaseType'] = $form_state->getValue('case_type');
            $datajson['RegionName'] = $form_state->getValue('region_name');
            $datajson['Zone'] = $form_state->getValue('zone');
            $datajson['TotalDevelopmentCost'] = $form_state->getValue('total_development_cost');
            $datajson['TotalGrossFloorArea'] = $form_state->getValue('total_gross_floor_area');
            $datajson['ExemptedGrossFloorArea'] = $form_state->getValue('exempted_gross_floor_area');
            $datajson['RequestSourceSystem'] = $form_state->getValue('request_source_system');
            $jsonData = json_encode($datajson);
            $service = \Drupal::service('calculator_api');
            $land_price_details = $service->rates($jsonData);
            if (array_key_exists('ErrorDetails', $land_price_details)) {
                $response->addCommand(new HtmlCommand('#sic-form-section', '<p><span class="icon icon--alert"></span> Please correct the following errors in the  Details. '.$land_price_details['ErrorDetails']['ErrorDescription'].'</p>'));
            } else {
                $response->addCommand(new HtmlCommand('.user-sic-data', 'Your submitted data '.$this->user_sic_submission($datajson)));

                $response->addCommand(new HtmlCommand('#sic-form-section', '<blockquote><h5><span class="icon icon--success"></span>The Gosford City Special Infrastructure Contribution rate is '.$land_price_details['ContributionRate'].'% of the cost of development. 
                </h5><h5><span class="icon icon--success"></span>The estimated contribution amount for the entered value is $'.number_format($land_price_details['ContributionAmount']).'. This estimate is based upon the figures entered. </h5> 
            
                <p><span class="icon icon--alert"></span><i>This estimate is only for Special Infrastructure Contribution (SIC). Other local contributions may apply.</i></p>
                
                <p>Please Note:</p>
<ul>
<li>This calculation is an estimate of a SIC contribution. The required contribution for any development will be subject to a detailed assessment carried out by the Department of Planning Industry and Environment via application on the SIC online service &nbsp;<a href="https://www.planningportal.nsw.gov.au/special-infrastructure-contributions-online-service">https://www.planningportal.nsw.gov.au/special-infrastructure-contributions-online-service</a></li>
<li>As the contribution rate is indexed annually, the estimate is current till the end of this financial year, thereafter the contribution must be indexed.</li>

</ul>
<p>Please Note: This estimate only relates to a state level contribution there may be other contributions that are applicable to the development such local government contributions.</p>
                
                
                
                
                </blockquote>
                
                <a type="button" class="button--primary button--right icon--chevron-right-white" onClick="history.go(0)" >Start again </a>
                
                
                '));
            }
        }

        return $response;
    }

    public function user_sic_submission($userdata)
    {
        $userhtml = '<ul>';

        $userhtml .= '<li>Zone : '.$userdata['Zone'].'</li>';
        $userhtml .= '<li>Total development cost : $'.number_format($userdata['TotalDevelopmentCost']).'</li>';
        $userhtml .= '<li>Total gross floor area : '.$userdata['TotalGrossFloorArea'].' square metres.</li>';

        $userhtml .= '</ul>';

        return $userhtml;
    }
}
