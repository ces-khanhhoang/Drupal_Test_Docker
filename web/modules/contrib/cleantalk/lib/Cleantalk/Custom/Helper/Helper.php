<?php

namespace Cleantalk\Custom\Helper;

class Helper extends \Cleantalk\Common\Helper\Helper
{

    /**
     * Get fw stats from the storage.
     *
     * @return    array
     * @example   array( 'firewall_updating' => false, 'firewall_updating_id' => md5(), 'firewall_update_percent' => 0, 'firewall_updating_last_start' => 0 )
     * @important This method must be overloaded in the CMS-based Helper class.
     */
    public static function getFwStats()
    {
        return array('firewall_updating_id' => \Drupal::state()->get('firewall_updating_id') ? \Drupal::state()->get('firewall_updating_id') : null, 'firewall_updating_last_start' => \Drupal::state()->get('firewall_updating_last_start') ? \Drupal::state()->get('firewall_updating_last_start') : 0, 'firewall_update_percent' => \Drupal::state()->get('firewall_update_percent') ? \Drupal::state()->get('firewall_update_percent') : null);
    }

    /**
     * Save fw stats on the storage.
     *
     * @param     array $fw_stats
     * @return    bool
     * @important This method must be overloaded in the CMS-based Helper class.
     */
    public static function setFwStats( $fw_stats )
    {
        \Drupal::state()->set('firewall_updating_id', isset($fw_stats['firewall_updating_id']) ? $fw_stats['firewall_updating_id'] : null);
        \Drupal::state()->set('firewall_updating_last_start', isset($fw_stats['firewall_updating_last_start']) ? $fw_stats['firewall_updating_last_start'] : 0);
        \Drupal::state()->set('firewall_update_percent', isset($fw_stats['firewall_update_percent']) ? $fw_stats['firewall_update_percent'] : 0);
    }

    /**
     * Implement here any actions after SFW updating finished.
     *
     * @return void
     */
    public static function SfwUpdate_DoFinisnAction()
    {
        \Drupal::state()->set('cleantalk_sfw_last_check', time());
    }

  /*
* Get data from submit recursively
*/
  public static function get_fields_any(
    $arr,
    $fields_exclusions = '',
    $fields_exclusions_regexp_flag = 0,
    $message = [],
    $email = NULL,
    $nickname = ['nick' => '', 'first' => '', 'last' => ''],
    $subject = NULL,
    $contact = TRUE,
    $prev_name = ''
  ) {
    //Skip request if fields exists
    $skip_params = [
      'ipn_track_id',    // PayPal IPN #
      'txn_type',        // PayPal transaction type
      'payment_status',    // PayPal payment status
      'ccbill_ipn',        // CCBill IPN
      'ct_checkjs',        // skip ct_checkjs field
      'api_mode',         // DigiStore-API
      'loadLastCommentId', // Plugin: WP Discuz. ticket_id=5571
    ];

    // Fields to replace with ****
    $obfuscate_params = [
      'password',
      'pass',
      'pwd',
      'pswd',
    ];

    // Skip feilds with these strings and known service fields
    $skip_fields_with_strings = [
      // Common
      'ct_checkjs', //Do not send ct_checkjs
      'nonce', //nonce for strings such as 'rsvp_nonce_name'
      'security',
      // 'action',
      'http_referer',
      'timestamp',
      'captcha',
      // Formidable Form
      'form_key',
      'submit_entry',
      // Custom Contact Forms
      'form_id',
      'ccf_form',
      'form_page',
      // Qu Forms
      'iphorm_uid',
      'form_url',
      'post_id',
      'iphorm_ajax',
      'iphorm_id',
      // Fast SecureContact Froms
      'fs_postonce_1',
      'fscf_submitted',
      'mailto_id',
      'si_contact_action',
      // Ninja Forms
      'formData_id',
      'formData_settings',
      // E_signature
      'recipient_signature',
      'avatar__file_image_data',
      'task',
      'page_url',
      'page_title',
      'Submit',
      'formId',
      'key',
      'id',
      'hiddenlists',
      'ctrl',
      'task',
      'option',
      'nextstep',
      'acy_source',
      'subid',
      'ct_action',
      'ct_method',
      'form_build_id',
      'honeypot_time',
      'g-recaptcha-response',
      'captcha_response',
      'captcha_token',
      'captcha_sid',
      'please_attach_any_supporting_files',
      'op',
      'form_build_id',
    ];

    $skip_fields_with_strings_by_regexp = [
      // Ninja Forms
      'formData_fields_\d+_id',
      'formData_fields_\d+_files.*',
      // E_signature
      'output_\d+_\w{0,2}',
      // Contact Form by Web-Settler protection
      '_formId',
      '_returnLink',
      // Social login and more
      '_save',
      '_facebook',
      '_social',
      'user_login-',
      // Contact Form 7
      '_wpcf7',
    ];

    // Reset $message if we have a sign-up data
    $skip_message_post = [
      'edd_action', // Easy Digital Downloads
    ];

    foreach ($skip_params as $value) {
      if (@array_key_exists($value, $_GET) || @array_key_exists(
          $value,
          $_POST
        )) {
        $contact = FALSE;
      }
    }

    if (count($arr)) {
      foreach ($arr as $key => $value) {
        if (gettype($value) == 'string') {
          $decoded_json_value = json_decode($value, TRUE);
          if ($decoded_json_value !== NULL) {
            $value = $decoded_json_value;
          }
        }

        if (!is_array($value) && !is_object($value)) {
          //Add custom exclusions
          if (is_string($fields_exclusions) && !empty($fields_exclusions)) {
            $fields_exclusions_arr = explode(",", $fields_exclusions);
            if (is_array(
                $fields_exclusions_arr
              ) && !empty($fields_exclusions_arr)) {
              if ((int) $fields_exclusions_regexp_flag === 1) {
                //If regexp is set to use in fields, just merge arrays
                $skip_fields_with_strings_by_regexp = array_merge(
                  $skip_fields_with_strings_by_regexp,
                  $fields_exclusions_arr
                );
              }
              else {
                //No regexp logic
                foreach ($fields_exclusions_arr as &$fields_exclusion) {
                  if (preg_match('/\[*\]/', $fields_exclusion)) {
                    // I have to do this to support exclusions like 'submitted[name]'
                    $fields_exclusion = str_replace(
                      ['[', ']'],
                      ['_', ''],
                      $fields_exclusion
                    );
                  }
                }
                unset($fields_exclusion);
                $skip_fields_with_strings = array_merge(
                  $skip_fields_with_strings,
                  $fields_exclusions_arr
                );
              }
            }
          }
          if (in_array(
              $key,
              $skip_params,
              TRUE
            ) && $key != 0 && $key != '' || preg_match("/^ct_checkjs/", $key)) {
            $contact = FALSE;
          }

          if ($value === '') {
            continue;
          }

          // Skipping fields names with strings from (array)skip_fields_with_strings
          foreach ($skip_fields_with_strings as $needle) {
            if ($prev_name . $key === trim($needle)) {
              continue(2);
            }
          }

          // Skipping fields names with strings from (array)skip_fields_with_strings_by_regexp
          foreach ($skip_fields_with_strings_by_regexp as $needle) {
            if (preg_match(
                "/" . trim($needle) . "/",
                $prev_name . $key
              ) === 1) {
              continue(2);
            }
          }

          // Obfuscating params
          foreach ($obfuscate_params as $needle) {
            if (strpos($key, $needle) !== FALSE) {
              $value = static::obfuscate_param($value);
              continue(2);
            }
          }


          // Removes whitespaces
          $value = urldecode(trim($value)); // Fully cleaned message
          $value_for_email = trim(
            $value
          );    // Removes shortcodes to do better spam filtration on server side.

          // Email
          if (!$email && preg_match("/^\S+@\S+\.\S+$/", $value_for_email)) {
            $email = $value_for_email;
            // Names
          }
          elseif (preg_match("/name/i", $key)) {
            preg_match(
              "/((name.?)?(your|first|for)(.?name)?)$/",
              $key,
              $match_forename
            );
            preg_match(
              "/((name.?)?(last|family|second|sur)(.?name)?)$/",
              $key,
              $match_surname
            );
            preg_match(
              "/^(name.?)?(nick|user)(.?name)?$/",
              $key,
              $match_nickname
            );

            if (count($match_forename) > 1) {
              $nickname['first'] = $value;
            }
            elseif (count($match_surname) > 1) {
              $nickname['last'] = $value;
            }
            elseif (count($match_nickname) > 1) {
              $nickname['nick'] = $value;
            }
            else {
              $nickname[$prev_name . $key] = $value;
            }
            // Subject
          }
          elseif ($subject === NULL && preg_match("/subject/i", $key)) {
            $subject = $value;
            // Message
          }
          else {
            $message[$prev_name . $key] = $value;
          }
        }
        elseif (!is_object($value)) {
          $prev_name_original = $prev_name;
          $prev_name = ($prev_name === '' ? $key . '_' : $prev_name . $key . '_');

          $temp = self::get_fields_any(
            $value,
            $fields_exclusions,
            $fields_exclusions_regexp_flag,
            $message,
            $email,
            $nickname,
            $subject,
            $contact,
            $prev_name
          );

          $message = $temp['message'];
          $email = $temp['email'] ?: NULL;
          $nickname = $temp['nickname'] ?: NULL;
          $subject = $temp['subject'] ?: NULL;
          if ($contact === TRUE) {
            $contact = !($temp['contact'] === FALSE);
          }
          $prev_name = $prev_name_original;
        }
      }
      unset($key);
    }

    foreach ($skip_message_post as $v) {
      if (isset($_POST[$v])) {
        $message = NULL;
        break;
      }
    }

    //If top iteration, returns compiled name field. Example: "Nickname Firtsname Lastname".
    if ($prev_name === '') {
      if (!empty($nickname)) {
        $nickname_str = '';
        foreach ($nickname as $value) {
          $nickname_str .= ($value ? $value . " " : "");
        }
        unset($value);
      }
      $nickname = $nickname_str;
    }

    $return_param = [
      'email' => $email,
      'nickname' => $nickname,
      'subject' => $subject,
      'contact' => $contact,
      'message' => $message,
    ];

    return $return_param;
  }

  /**
   * Masks a value with asterisks (*) Needed by the getFieldsAny()
   * @return string
   */
  public static function obfuscate_param($value = null)
  {
    if ($value && (!is_object($value) || !is_array($value)))
    {
      $length = strlen($value);
      $value  = str_repeat('*', $length);
    }

    return $value;
  }

  /**
   * Get site url for remote calls.
   *
   * @return string@important This method can be overloaded in the CMS-based Helper class.
   */
  public static function getSiteUrl()
  {
    return ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . ( isset($_SERVER['SCRIPT_URL']) ? $_SERVER['SCRIPT_URL'] : '' );
  }
}
