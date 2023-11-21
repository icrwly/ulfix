<?php

namespace Drupal\ul_guidelines_forms\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use stdClass;

class SubmitRequestForm {

  /**
   * Division options array.
   *
   * @return array
   */
  public static function divisionOptions() {
    return [
      'Commercial & Industrial' => [
        'Appliances, HVAC/R, and Lighting',
        'Building and Life Safety Technologies',
        'Energy and Power Technologies',
        'Performance Materials',
        'Wire and Cable',
      ],
      'Consumer' => [
        'Consumer and Retail Services',
        'Consumer Technology',
        'Identity Management & Security',
        'Environment and Furniture',
        'Life and Health Sciences',
        'Small Appliances',
      ],
      'Software' => [
          'Product Supply Chain Intelligence',
          'EHS Sustainability',
      ],
      'Enterprise' => [
        'Global Accounts',
        'Corporate Strategy',
        'CPO, Accreditation and Standards',
        'Global Sourcing',
        'Corporate Marketing',
        'Global Finance',
        'Human Resources ',
        'Facilities',
        'Shared Service IT',
        'Global Security, Anti-Counterfeiting and Brand Protection',
        'Legal, Governmental Affairs and Ethics',
      ],
      'NFP' => [
        'Battery Safety',
        'Chemical Safety',
        'Data Science',
        'Education and Outreach',
        'Fire Safety',
        'Public Health and Safety',
        'Strategy and Marketing',
      ],
    ];
  }


  /**
   * Division options array flattened and used as values in
   * 'allowed_values_function' for the field storage definition.
   * @see field.storage.contact_message.field_request_division_group.yml
   *
   * @return array
   */
  public static function allowedDivisionOptions() {
    $division_options = self::divisionOptions();
    $options = [];
    foreach ($division_options as $key => $values) {
      $options = $options + array_combine($values, $values);
    }

    $options['Other'] = t('Other');
    return $options;
  }

  /**
   * Media options array.
   *
   * @return array
   */
  public static function mediaOptions() {
    return [
      'default' => [
        'Brand',
        'Corporate Marketing Programs',
        'Customer Promotions',
        'Digital',
        'Facilities',
        'Marks',
        'PR',
        'Video',
        'White Papers'
      ],
      'online' => [
        'Analytics',
        'BrightEdge',
        'Marketo',
        'Online',
        'Sprinklr',
        'Teamwork',
        'WebDAM'
      ],
      'legal' => [
        'Legal'
      ]
    ];
  }

  /**
   * Media options array flattened and used as values in
   * 'allowed_values_function' for the field storage definition.
   * @see field.storage.contact_message.field_request_media.yml
   *
   * @return array
   */
  public static function allowedMediaOptions() {
    $media_options = self::mediaOptions();
    $options = [];
    foreach ($media_options as $key => $values) {
      $options = $options + array_combine($values, $values);
    }

    return $options;
  }

  /**
   * Fetch list of media options.
   *
   * @param string $key
   */
  private function getMediaOptions($key) {

    if (!empty($key)) {
      $mediaOptions = self::mediaOptions();
      $options = $mediaOptions[$key];

      // Make key and values the same.
      return array_combine($options, $options);
    }

    return FALSE;
  }

  /**
   * Fetch list of media options.
   *
   * @param string $key
   */
  private function getDivisionOptions($key) {

    if (!empty($key)) {
      $divisionOptions = self::divisionOptions();
      $options = $divisionOptions[$key];
      // Must add this in order for Drupal validation to accept it.
      $options[] = t('Other');

      // Make key and values the same.
      return array_combine($options, $options);
    }

    return FALSE;
  }

  /**
   * Helper function for getting default option.
   *
   * @param string $value
   * @param array $options
   */
  private function getDefaultOption($value, $options) {
    if (!empty($value) && !in_array($value, $options)) {
      return 'Other';
    }
    else {
      return $value;
    }
  }

  /**
   * Alter the submit request form.
   * @param array                                $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {

    // Set custom '_none' values for drop-downs.
    $dropdowns = [
      'field_request_type' => t('Please select a request type'),
      'field_request_media' => t('Please select a media type'),
      'field_request_priority' => t('Select your request\'s priority level'),
      'field_request_business' => t('Select the Business Unit related to your request'),
      'field_request_division_group' => t('Select the division/group related to your request'),
      'field_request_region' => t('Select your global region'),
    ];
    // Loop through each dropdown field to set none text.
    foreach ($dropdowns as $field_name => $none_text) {
      // Check that field exists and it is a drop down with options.
      if (isset($form[$field_name]['widget']['#options']['_none'])) {
        // Update the text for the none option.
        $form[$field_name]['widget']['#options']['_none'] = $none_text;
      }
    }

    // Custom ajax form elements.
    // These two forms below control the media and division fields which get
    // automatically populated with values depending on the selection.
    $form['field_request_type']['widget']['#ajax'] = [
      'callback' => [$this, 'updateMediaOptionsCallback'],
      'wrapper' => 'field-request-media',
      'event' => 'change',
    ];
    $form['field_request_media']['#attributes']['id'] = 'field-request-media';

     // Business unit ajax call to populate its corresponding divisions.
    $form['field_request_business']['widget']['#ajax'] = [
      'callback' => [$this, 'updateDivisionOptionsCallback'],
      'wrapper' => 'field-request-division',
      'event' => 'change',
     ];

    $form['field_request_division_group']['#attributes']['id'] = 'field-request-division';

    // Prep custom validator.
    // This validation ensures that dynamic fields are marked as required.
    $custom_validator = 'ul_guidelines_forms_states_require_validate';

    // Prep fields that require custom validation.
    $validation_fields = [
      'field_request_priority_explain' => [
        'field_request_priority' => ['value' => 'Exceptional']
      ],
      'field_request_region_explain' => [
        'field_request_region' => ['value' => 'Other']
      ],
      'field_request_division_group_exp' => [
        'field_request_division_group' => ['value' => 'Other']
      ],
      'field_request_media' => [
        'field_request_type' => ['!value' => '_none']
      ],
      'field_request_division_group' => [
        'field_request_business' => ['!value' => '_none']
      ],
    ];

    // Loop through each field that needs custom validation.
    foreach ($validation_fields as $field_name => $conditions) {
      // Check that the field exists.
      if (isset($form[$field_name])) {
        // Loop through the conditions and add to the '#states' attribute.
        foreach ($conditions as $sub_field_name => $value) {
          // Check that the sub field exists.
          if (isset($form[$sub_field_name])) {
            // Add state condition for visibility and required state.
            $form[$field_name]['#states']['visible']['select[name="' . $sub_field_name . '"]'] = $value;
            $form[$field_name]['#states']['required']['select[name="' . $sub_field_name . '"]'] = $value;
          }
        }
        // Check that success in adding states, if so add validator.
        if (isset($form[$field_name]['#states'])) {
          $form[$field_name]['#element_validate'][] = $custom_validator;
        }
      }
    }

    // Setting default form values from user information.
    // Loading CRC Data to automatically set division and region.
    $account = \Drupal::currentUser();
    $crc_user = \Drupal::service('ul_crc')->getUserByEmail($account->getEmail());

    if (!empty($crc_user['user'])) {
      $default = $this->getDefaultOption(
        $crc_user['user']['division']['name'],
        $form['field_request_division_group']['widget']['#options']
      );

      if (!empty($default)) {
        $form['field_request_division_group']['widget']['#default_value'] = $default;
        if ($default == 'Other') {
          $form['field_request_division_group_exp']['widget'][0]['value']['#default_value'] = $crc_user['user']['division']['name'];
        }
      }
        $business_default = '';
        foreach (self::divisionOptions() as $business => $divisions) {
          if (in_array($default, $divisions)) {
              $business_default = $business;
            }
        }

      $form['field_request_business']['widget']['#default_value'] = $business_default;
      $default = $this->getDefaultOption(
        $crc_user['user']['region'],
        $form['field_request_region']['widget']['#options']
      );

      if (!empty($default)) {
        $form['field_request_region']['widget']['#default_value'] = $default;
        if ($default == 'Other') {
          $form['field_request_region_explain']['widget'][0]['value']['#default_value'] = $crc_user['user']['region'];
        }
      }
    }

    // Remove the preview button.
    unset($form['actions']['preview']);
    // Rename the submit button label.
    $form['actions']['submit']['#value'] = t('Submit Request');
  }

  /**
   * Ajax callback function to update media options.
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function updateMediaOptionsCallback($form, FormStateInterface $formState) {

    $type = $formState->getValue('field_request_type');
    $options = [];
    if (!empty($type[0]['value'])) {
      switch ($type[0]['value']) {
        case 'Legal Review':
          $options = $this->getMediaOptions('legal');
          break;
        case 'Online Issue or Update':
          $options = $this->getMediaOptions('online');
          break;
        default;
          $options = $this->getMediaOptions('default');
          break;
      }
    }

    $none_text = $form['field_request_media']['widget']['#options']['_none'];
    $options = ['_none' => $none_text] + $options;
    $form['field_request_media']['widget']['#options'] = $options;

    return $form['field_request_media'];
  }

  /**
   * Ajax callback function to update division options.
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function updateDivisionOptionsCallback($form, FormStateInterface $formState) {

    $type = $formState->getValue('field_request_business');
    $options = [];
    if (!empty($type[0]['value'])) {
        $options = $this->getDivisionOptions($type[0]['value']);
    }

    $none_text = $form['field_request_division_group']['widget']['#options']['_none'];
    $options = ['_none' => $none_text] + $options;
    $form['field_request_division_group']['widget']['#options'] = $options;

    return $form['field_request_division_group'];
  }

  /**
   * Alter email message being sent for the submit Request form.
   * @param array $message
   */
  public function mailAlter(array &$message) {
    /** @var \Drupal\contact\Entity\Message $contact_message */
    $contact_message = $message['params']['contact_message'];

    // Set a default subject.
    $subject = 'Help Request';
    // Setup array of helpdesk id's to form fields.
    $fields = [
      // Helpdesk key => form field id
      'Subject' => 'field_request_subject',
      'Priority' => 'field_request_priority',
      'Priority_Explanation' => 'field_request_priority_explain',
      'Type_of_Request' => 'field_request_type',
      'Division_Group' => 'field_request_division_group',
      'Division_Group_Explanation' => 'field_request_division_group_exp',
      'Region' => 'field_request_region',
      'Region_Explanation' => 'field_request_region_explain',
      'Description' => 'field_request_subject',
      'Media_Type' => 'field_request_media',
      'Visibility' => 'field_request_visibility',
      'Background_Context' => 'field_request_background_context',
      'Your_Request' => 'field_request_description',
      'Attachments' => 'field_request_attachments',
      'Submitter_First_Name' => 'field_first_name',
      'Submitter_Last_Name' => 'field_last_name',
    ];

    // Re-format body to match what helpdesk is expecting.
    $new_body = [];
    // Loop through each help desk field.
    foreach ($fields as $helpdesk_id => $field_id) {
      // Does field exist as part of the contact message object?
      if ($contact_message->hasField($field_id)) {
        // Get the field.
        $field = $contact_message->get($field_id);
        // Switch action depending on helpdesk field.
        switch ($helpdesk_id) {
          case 'Description':
            if ($value = $field->getValue()) {
              $value = $value[0]['value'];
              $subject = $value;
              $new_body[$helpdesk_id] = $value;
            }
            break;
          case 'Attachments':
            $attachments = $field->getValue();
            $new_body[$helpdesk_id] = ($attachments ? 'Yes' : 'No');
            // If there are attachments add them to the email.
            if ($attachments) {
              foreach ($attachments as $attachment) {
                // SwiftMailer only supports attachments sent as stdClass.
                $managed_file = File::load($attachment['target_id']);
                $file = new stdClass();
                $file->uri = $managed_file->getFileUri();
                $file->filename = $managed_file->getFilename();
                $file->filemime = $managed_file->getMimeType();
                $message['params']['files'][] = $file;
                // MailGun expects attachments in an attachments array.
                $message['params']['attachments'][] = $file->uri;
              }
            }
            break;
          default:
            if ($value = $field->getValue()) {
              $value = $value[0]['value'];
              $new_body[$helpdesk_id] = $value;
            }
            break;
        }
      }
    }
    // Get current user.
    $user = User::load(\Drupal::currentUser()->id());
    // Add user email address.
    $new_body['Submitter_Email'] = $user->getEmail();

    // Check for first name value.
    if ($first_name = $user->get('field_first_name')->getValue()) {
      $new_body['Submitter_First_Name'] = $first_name[0]['value'];
    }

    // Check for last name value.
    if ($last_name = $user->get('field_last_name')->getValue()) {
      $new_body['Submitter_Last_Name'] =  $last_name[0]['value'];
    }

    // Add some hardcoded values.
    //$new_body['plain_text'] = 'plain_text:1';
    //$new_body['event'] = 'event:create';

    $new_body = $this->formatRequestMail($new_body);

    // Replace existing mail body.
    $message['body'] = [$new_body];
    // Change email subject.
    $message['subject'] = ' Brand Hub Help Desk - ' . $subject . ' - ' . date('Y-m-d H:i:s');
  }

  /**
   * Build email body for the HelpDesk email.
   *
   * @param $content
   * @return string
   */
  private function formatRequestMail(&$content) {

    // This content gets consumed by a third-party service that is why we
    // are not doing this with a theme. The characters need to be very precise.
    $fields = [];
    $fields[] = ['field' => 'Priority'];
    $fields[] = ['field' => 'Priority_Explanation'];
    $fields[] = ['field' => 'Type_of_Request'];
    $fields[] = ['field' => 'Division_Group'];
    $fields[] = ['field' => 'Division_Group_Explanation'];
    $fields[] = ['field' => 'Region'];
    $fields[] = ['field' => 'Region_Explanation'];
    $fields[] = ['field' => 'Description'];
    $fields[] = ['field' => 'Media_Type'];
    $fields[] = ['field' => 'Visibility'];
    $fields[] = ['field' => 'Background_Context', 'after' => PHP_EOL];
    $fields[] = ['field' => 'Your_Request', 'after' => PHP_EOL];
    $fields[] = ['field' => 'Attachments'];
    $fields[] = ['field' => 'Submitter_Email'];
    $fields[] = ['field' => 'Submitter_First_Name'];
    $fields[] = ['field' => 'Submitter_Last_Name'];
    $fields[] = ['field' => 'Date_Created', 'default' => date('Y-m-d H:i:s')];

    $body = [];
    // Loop through each of the fields
    foreach ($fields as $field) {
      $helpdesk_id = $field['field'];
      $default_value = '';
      if (isset($field['default'])) {
        $default_value = $field['default'];
      }
      // Add either the submitted value or the default value.
      $body[] = $helpdesk_id . ':' . (isset($content[$helpdesk_id]) ? $content[$helpdesk_id] : $default_value) . (isset($field['after']) ? $field['after'] : '');
    }

    // Combine array into string.
    $message = implode(PHP_EOL, $body);

    return $message;
  }
}