<?php
  class Customer{
    
    public function get_details($username=''){
      $username = sanitize($username) ;
      $customer = Api::get_one('store_customers','username',$username);
      if(empty($customer)){
        new_customer();
        $customer = Api::get_one('store_customers','username',$username);
      }
      return $customer;
    }


    public function get_num_orders($email=''){
      $email = sanitize($email);
      if(!empty($email)){
        $num_orders = Api::get_count('store_orders','email',$email);
        return $num_orders;
      }
    }


    public function list_customers(){
      if(is_admin()){
        $q =Api::get_many('store_customers','DESC',0,1000);
        $_SESSION['temp']  =$q;
        Plugins::load('slampacks>>views>>customers.list-customers');
      } else {
        deny_access();
      }
    }
      
    
    public function save($arr=[]){
      foreach($arr as $key=>$value){
        //~ $arr[$key]
      }
    }
  }
  
  function new_customer(){
    if(!empty($_POST)){
      $_POST['Created'] = date('c');
      $_POST['Modified'] = date('c');
      
      $sql_keys = '';
      $sql_values = "";
      foreach($_POST as $key=>$value){
        $field_key = sanitize($key);
        $field_value = sanitize($value);
        $sql_keys .= ','.$field_key;
        $sql_values .= ",'{$field_value}'";
      }
      $sql = "INSERT INTO `store_customers`(id".$sql_keys.") VALUES ('0'".$sql_values.")";
      //~ echo $sql;
      $q = query_db($sql,'Could not save new customer! ');
      if($q){
        //~ print_r($q);
        set_session_message('alert-success','Customer details Saved');
        toast('notice','Customer details saved!');
      }
    }
    Plugins::load('store>>views>>customer.new-customer-form');
  }

  function customers(){
    Customer::list_customers();
  }
?>
