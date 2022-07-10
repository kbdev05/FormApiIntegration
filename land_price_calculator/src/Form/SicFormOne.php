<?php
/**
 * @file
 * Contains \Drupal\land_price_calculator\Form\SicFormOne.
 */

namespace Drupal\land_price_calculator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\PrependCommand;

class SicFormOne extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'sic_form_one';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $data = null)
    {
        $form['#prefix'] = '<div id="sicformone">';
        $form['#suffix'] = '</div>';

        $form['case_type'] = array(
    '#type' => 'textfield',
    // '#title' => t('Case Type'),
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
      '#placeholder' => t('Residential and Industrial Zoning'),
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
       '#placeholder' => t('Residential and Industrial Zoning'),
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

        $form['precint'] = array(
     '#type' => 'hidden',
     '#title' => t('Precinct'),
     '#default_value' => $data['presinct'],
     '#attributes' => ['readonly' => 'readonly'],
    );

        $form['precint'] = array(
     '#type' => 'hidden',
     '#title' => t('Precinct'),
     '#default_value' => $data['presinct'],
     '#attributes' => ['readonly' => 'readonly'],
    );

        $form['total_net_development_area'] = array(
      '#type' => 'textfield',
      '#description' => '<div class="net_development_area"></div>',
      '#title' => t('Enter net development area (in hectares)'),
      '#placeholder' => t('Enter Net Development Area (in hectares)'),
      '#required' => true,
    );

        $form['fieldset'] = array(
 '#type' => 'webform_more',
    '#attributes' => array(
           'class' => array('spacing--bottom-l'),
           ),
 '#more_title' => 'What is this ?',
 '#more' => '<div class="webform-section"><p>A Net Development Area (NDA) is the area of land to which the development relates and is used to calculate the SIC charge. The NDA is generally the area of the development less any exemptions for schools, cemeteries, public recreation, public utilities etc. Further information can be found in the relevant Special Infrastructure Contributions Area Determination found in  <a href="https://www.planning.nsw.gov.au/Plans-for-your-area/Infrastructure-funding/Special-Infrastructure-Contributions" target="_blank">SIC information page</a> </p>
</div>',
);

        $form['request_source_system'] = array(
      '#type' => 'textfield',
      // '#title' => t('Request Source System'),
      '#attributes' => ['hidden' => 'hidden'],
      '#value' => 'Drupal',
    );

        $form['actions']['confirm'] = [
      '#type' => 'submit',
       '#attributes' => array(
           'class' => array('spacing--top-m'),
           ),
      '#value' => $this->t('Calculate the contribution'),
      '#suffix' => '<strong ><p class="spacing--top-m"><span class="icon icon--alert"></span><i>The provided contribution value is an estimate only. To confirm the estimate a full assessment is required.</i></p></strong>',
      '#ajax' => [
        'callback' => [$this, 'calculatePrice'],
        'url' => Url::fromRoute('land_price_calculator.sick_form_one'),
        'options' => [
          'query' => [
            FormBuilderInterface::AJAX_FORM_REQUEST => true,
          ],
        ],
      ],
    ];

        $form['actions']['confirm']['#ajax']['options']['query'] += \Drupal::request()->query->all();

        $form['#action'] = Url::fromRoute('land_price_calculator.sick_form_one')->toString();
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
        if (!is_numeric($form_state->getValue('total_net_development_area')) || preg_match('/\.\d{5,}/', $form_state->getValue('total_net_development_area'))) {
            $form_state->setErrorByName('total_net_development_area', $this->t('Please Enter a Valid Value.'));
        }
    }

    public function calculatePrice(array &$form, FormStateInterface $form_state): AjaxResponse
    {
        $response = new AjaxResponse();
        if ($form_state->hasAnyErrors()) {
            $status_messages = array('#type' => 'status_messages');
            $messages = \Drupal::service('renderer')->renderRoot($status_messages);
            if (!empty($messages)) {
                $response->addCommand(new PrependCommand('.landing-errors', $messages));
                $response->addCommand(new CssCommand('.landing-errors', ['border' => '3px solid red', 'display' => 'none']));
                $css = ['border' => '2px solid red'];
                $message = $this->t('Please enter a valid value for Total net development Area.');
                $response->addCommand(new CssCommand('input[name="total_net_development_area"]', $css));
                $response->addCommand(new HtmlCommand('.net_development_area', $message));
            }
        } else {
            $datajson = [];
            $datajson['CaseType'] = $form_state->getValue('case_type');
            $datajson['RegionName'] = $form_state->getValue('region_name');
            $datajson['Zone'] = $form_state->getValue('zone');
            $datajson['Precinct'] = $form_state->getValue('precint');
            $datajson['TotalNetDevelopableArea'] = $form_state->getValue('total_net_development_area');
            $datajson['RequestSourceSystem'] = $form_state->getValue('request_source_system');
            $jsonData = json_encode($datajson);
            // call price calculator service
            $service = \Drupal::service('calculator_api');
            $land_price_details = $service->rates($jsonData);
            if (array_key_exists('ErrorDetails', $land_price_details)) {
                $response->addCommand(new HtmlCommand('#sic-form-section', '<p><span class="icon icon--alert"></span>Please correct the following error in your details. '.$land_price_details['ErrorDetails']['ErrorDescription'].'</p>'));
            } else {
                $response->addCommand(new HtmlCommand('.user-sic-data', 'Your submitted data '.$this->user_sic_submission($datajson)));

                $response->addCommand(new HtmlCommand('#sic-form-section', '<blockquote><h5><span class="icon icon--success"></span>'.$form_state->getValue('region_name').' Areas Special Infrastructure Contribution rate is $'.number_format($land_price_details['ContributionRate']).' per hectare of net developable area. 
                </h5><h5><span class="icon icon--success"></span>The estimated contribution amount for the entered values is $'.number_format($land_price_details['ContributionAmount']).'. The estimate is based upon the figures entered.</h5> 
            
                <p><span class="icon icon--alert"></span><i>This estimate is only for Special Infrastructure Contribution (SIC). Other local contributions may apply.</i></p>
                
                <p>Notes:</p>
<ul>
<li>This calculation is an estimate of a SIC contribution. The required contribution for any development will be subject to a detailed assessment carried out by the Department of Planning Industry and Environment via application on the SIC online service &nbsp;<a href="https://www.planningportal.nsw.gov.au/special-infrastructure-contributions-online-service">https://www.planningportal.nsw.gov.au/special-infrastructure-contributions-online-service</a></li>
<li>As the contribution rate is indexed annually, the estimate is current till the end of this financial year, thereafter the contribution must be indexed.</li>

</ul>
<p>Please Note: This estimate only relates to a state level contribution there may be other contributions that are applicable to the development such local government contributions.</p>
                
                
                </blockquote>  <a type="button" class="button--primary button--right icon--chevron-right-white" onClick="history.go(0)" >Start again </a>'));
            }
        }

        return $response;
    }

    public function user_sic_submission($userdata)
    {
        $userhtml = '<ul>';

        $userhtml .= '<li>Zone : '.$userdata['Zone'].'</li>';
        // $userhtml .= '<li>Precinct : '.$userdata['Precinct'].'</li>';
        $userhtml .= '<li>Total net developable area : '.$userdata['TotalNetDevelopableArea'].' hectares.</li>';

        $userhtml .= '</ul>';

        return $userhtml;
    }
}
