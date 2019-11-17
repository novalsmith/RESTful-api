<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'third_party/REST_Controller.php';
require APPPATH . 'third_party/Format.php';

use Restserver\Libraries\REST_Controller;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load these helper to create JWT tokens
        $this->load->helper(['jwt', 'authorization']);
    }

  
    public function hello_get()
    {
        $tokenData = 'Hello World!';
        // Create a token
        $token = AUTHORIZATION::generateToken($tokenData);
        // Set HTTP status code
        $status = parent::HTTP_OK;
        // Prepare the response
        $response = ['status' => $status, 'token' => $token];
        // REST_Controller provide this method to send responses
        $this->response($response, $status);
    }


    public function login_post()
    {
        // Have dummy user details to check user credentials
        // send via postman
        $dummy_user = [
            'username' => 'noval',
            'password' => 'smith'
        ];
        // Extract user data from POST request
        $username = $this->post('username');
        $password = $this->post('password');
        // Check if valid user
        if ($username === $dummy_user['username'] && $password === $dummy_user['password']) {

            // Create a token from the user data and send it as reponse
            $token = AUTHORIZATION::generateToken(['username' => $dummy_user['username']]);
            // Prepare the response
            $status = parent::HTTP_OK;
            $response = ['status' => $status, 'token' => $token];
            $this->response($response, $status);
        } else {
            $this->response(['msg' => 'Invalid username or password!'], parent::HTTP_NOT_FOUND);
        }
    }

    private function verify_request()
    {
        // Get all the headers
        $headers = $this->input->request_headers();
        // Extract the token
        $token = $headers['Authorization'];
        // Use try-catch
        // JWT library throws exception if the token is not valid
        try {
                // if($token != 'apitoken')
                // {
                // $status = parent::HTTP_UNAUTHORIZED;
                // $response = array(
                //     'status' => $status,
                //     'message' =>  "Periksa kembali Api token anda"
                //     // 'data' => $res
                // );

                // $this->response($response, $status);
                // exit();
                // }else{
                        // Validate the token
                        // Successfull validation will return the decoded user data else returns false
                        $data = AUTHORIZATION::validateToken($token);
                        if ($data === false) {
                            $status = parent::HTTP_UNAUTHORIZED;
                            // $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                            $response = array(
                                'status' => $status,
                                'message' =>  "Periksa kembali Api token anda"
                                // 'data' => $res
                            );

                            $this->response($response, $status);
                            exit();
                        } else {
                            return $data;
                        }
                //  }

        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = parent::HTTP_UNAUTHORIZED;
            // $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];


            $response = array(
                'status' => $status,
                'message' =>  "Periksa kembali Api token anda" 
                // 'data' => $res
            );

            $this->response($response, $status);
        }
    }


    public function get_me_data_post()
    {
        // Call the verification method and store the return value in the variable
        $data = $this->verify_request();
        // Send the return data as reponse
        $status = parent::HTTP_OK;
        $response = ['status' => $status, 'data' => $data];
        $this->response($response, $status);
    }

    public function member_get()
    {
        $idmember = $this->input->get('idmember');
  
        $qs = $_SERVER['QUERY_STRING'];
        
        $KeyValidate = explode("=", $qs);
         
        // Call the verification method and store the return value in the variable
        // $data = $this->verify_request();
        // Send the return data as reponse
        $status = "";
        $res="";
        $message = '';

      


        if($idmember ==""){
           
           
            $row = $this->db->get('member');
           
            if($row->num_rows() >0){
                $res = $row->result();
                $status =  parent::HTTP_OK;
                $message = 'sukses';
            }else{
                $res ="";
                $status =  parent::HTTP_NOT_FOUND;
                $message = 'data tidak ditemukan';
            }


        }  else{
            if($KeyValidate[0] != "idmember") {

                $res = $KeyValidate;
                $status =  parent::HTTP_BAD_REQUEST;
                $message = 'Parameter harus idmember';
            } else { 

            $row = $this->db->get_where('member',array('idmember'=>$idmember));

            if ($row->num_rows() > 0) {
                $res = $row->result();
                $status =  parent::HTTP_OK;
                $message = 'sukses';
            } else {
                $res = "";
                $status =  parent::HTTP_NOT_FOUND;
                $message = 'data tidak ditemukan';
            }

            
        }
       
    }

        $response = array(
            'status' => $status,
            'message' => $message,
            'data' => $res
        );
        $this->response($response, $status);
     }

    public function member_post()
    {
        $idmember = $this->post('idmember');
        $nama = $this->post('nama');
        $nomor = $this->post('nomor');

        $tmp = array(
            'idmember' => $idmember,
            'nama'      => $nama,
            'nomor'     => $nomor
        );
       
        $status = parent::HTTP_OK;
 
            $row = $this->db->insert('member',$tmp);
            $res = $row;
     
        $response = array(
            'status' => $status,
            'message' => 'sukses',
            'data' => $res
        );

        $this->response($response, $status);
    }


    public function member_put()
    {

       $Setidmember = $this->put('idmember');
        $idmember = $this->put('idmember');
        $nama = $this->put('nama');
        $nomor = $this->put('nomor');

        $tmp = array(
          'idmember' => $idmember,
            'nama'      => $nama,
            'nomor'     => $nomor
        );
        $this->db->where('idmember', $Setidmember);
        $row = $this->db->update('member', $tmp);

        if ($row) {
            $res = "";
            $status =  parent::HTTP_OK;
            $message = 'sukses';
        } else {
            $res = "";
            $status =  parent::HTTP_NOT_FOUND;
            $message = 'data tidak ditemukan';
        }


        $response = array(
            'status' => $status,
            'message' => $message,
            'data' => $res
        );

        $this->response($response, $status);
    }


    public function member_delete()
    {

        $Setidmember = $this->delete('idmember');
       
        $this->db->where('idmember', $Setidmember);
        $row = $this->db->delete('member');
 
        $response = array(
            'status' => true,
            'message' => 'berhasil terhapus');
           

        $this->response($response, parent::HTTP_OK);
    }

 


}
