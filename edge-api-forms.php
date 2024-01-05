<?php
/**
 * Plugin Name:       Edge API Forms
 * Description:       Edge API Forms is created by Zain Hassan.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Zain Hassan
 * Author URI:        https://hassanzain.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       edge-api
*/

if(!defined('ABSPATH')){
    exit;
}


add_action( 'init', 'create_post_type_edgeapi' );
// Add the hook
add_action( 'elementor_pro/forms/validation', function( $record, $ajax_handler ) {

    // Get the form name
    $form_name = $record->get_form_settings( 'form_name' );
    $form_name = $form_name && $form_name !== '' ? $form_name : '';
    $form_data = $record->get_formatted_data();
    if( $form_name !== '' && $form_name === get_option('edgeapi_form_name') ){
      // Get the form submission data
     
      $fname = $form_data['First name'];
      $lname = $form_data['Last name'];
      $email = $form_data['Email address'];
      $company = $form_data['Company'];
      $interest = $form_data['Interested in'];
      $location = $form_data['Location'];
      $message = $form_data['Message'];
      $team = $form_data['How big is your team?'];
      api_call_edgeapi($fname, $lname, $email, $company, $interest, $location, $message, $team);
     // print_r( $form_name );
    }
}, 10, 2 );



// Define custom post type function
function create_post_type_edgeapi() {
    add_menu_page(
        'Form Submission API',     // page title
        'Form Submission API',     // menu title
        'manage_options',   // capability
        'edge-api',     // menu slug
        'edge_submission_settings_page' // callback function
    );
}

function edge_submission_settings_page() {

  // Code for your settings page goes here
  if (isset($_POST['form_id'])) {
    update_option('edgeapi_form_name', sanitize_text_field($_POST['form_id']));
    update_option('edgeapi_client_id_id', sanitize_text_field($_POST['client_id']));
    update_option('edgeapi_client_secret_id', sanitize_text_field($_POST['client_secret']));
    update_option('edgeapi_username_id', sanitize_text_field($_POST['username']));
    update_option('edgeapi_password_id', sanitize_text_field($_POST['password']));
  }

  ?>
  <style>
    form {
    max-width: 400px;
    margin: 0 auto;
    }

    label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    }

    input[type="text"], input[type="password"], input[type="submit"] {
    display: block;
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
    }

    input[type="submit"] {
    background-color: #007bff;
    color: #fff;
    cursor: pointer;
    }

    input[type="submit"]:hover {
    background-color: #0062cc;
    }
  </style>
  <form action="" method="post">
    <p>
      <label for="form_id">Form Name</label>
      <input type="text" name="form_id" id="form_id" value="<?php echo esc_attr(get_option('edgeapi_form_name')); ?>"> 
    </p>
    <p>
      <label for="client_id">Client ID</label>
      <input type="text" name="client_id" id="client_id" value="<?php echo esc_attr(get_option('edgeapi_client_id_id')); ?>"> 
    </p>
    <p>
      <label for="client_secret">Client Secret</label>
      <input type="text" name="client_secret" id="client_secret" value="<?php echo esc_attr(get_option('edgeapi_client_secret_id')); ?>"> 
    </p>
    <p>
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="<?php echo esc_attr(get_option('edgeapi_username_id')); ?>"> 
    </p>
    <p>
      <label for="password">Password</label>
      <input type="text" name="password" id="password" value="<?php echo esc_attr(get_option('edgeapi_password_id')); ?>"> 
    </p>
    <input type="submit" value="Save">
  </form>
  <?php
}

function api_call_edgeapi($fname, $lname, $email, $company, $interest, $location, $message, $team){
    $curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://open.sandbox-api.zapfloorhq.com/oauth/token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
      "grant_type": "password",
      "client_id": "'. get_option('edgeapi_client_id_id') .'",
      "client_secret": "'. get_option('edgeapi_client_secret_id') .'",
      "username": "'. get_option('edgeapi_username_id') .'",
      "password": "'. get_option('edgeapi_password_id') .'",
      "scope": "LEAD:WRITE"
      }',
      CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json'
      ),
      ));
  
      $response = curl_exec($curl);
      $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
  
      if($status_code === 201){
          $token = json_decode($response, true)["access_token"];
          $curl = curl_init();
          curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://open.sandbox-api.zapfloorhq.com/v1/leads',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
          "lead": {
              "company_name": "'.$company.'",
              "email": "'.$email.'"
          }
          }',
          CURLOPT_HTTPHEADER => array(
              'Authorization: Bearer ' . $token,
              'Content-Type: application/json'
          ),
          ));
  
          $response = curl_exec($curl);
          $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          curl_close($curl);
          if($status_code === 201){
              $lead_id = json_decode($response, true)["data"]["id"];
  
              $curl = curl_init();
              curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://open.sandbox-api.zapfloorhq.com/v1/leads/'.$lead_id.'/contacts',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{
              "contact": {
                  "firstname": "'. $fname .'",
                  "lastname": "'. $lname .'",
                  "email": "'. $email .'"
              }
              }',
              CURLOPT_HTTPHEADER => array(
                  'Authorization: Bearer ' . $token,
                  'Content-Type: application/json'
              ),
              ));
  
              $response = curl_exec($curl);
              $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
              curl_close($curl);
              //  echo "<pre>";
              //  var_dump(json_decode($response, true));

              $curl = curl_init();
              curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://open.sandbox-api.zapfloorhq.com/oauth/token',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{
              "grant_type": "password",
              "client_id": "'. get_option('edgeapi_client_id_id') .'",
              "client_secret": "'. get_option('edgeapi_client_secret_id') .'",
              "username": "'. get_option('edgeapi_username_id') .'",
              "password": "'. get_option('edgeapi_password_id') .'",
              "scope": "DEAL:WRITE"
              }',
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
              ));
          
              $response = curl_exec($curl);
              $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
              curl_close($curl);

              if($status_code === 201){
                $token = json_decode($response, true)["access_token"];
                $location_id = json_decode($response, true)["user"]["locations_bankdetail_settings"][0]["location_id"];
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://open.sandbox-api.zapfloorhq.com/v1/leads/'.$lead_id.'/deals',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                "deal": {
                    "location_id": "'. $location_id .'"
                }
                }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ),
                ));

    
                $response = curl_exec($curl);
                $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
              }
          }
      }
}



