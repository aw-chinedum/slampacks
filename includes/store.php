<?php


    
class Store{
  
  

}

function buy_now($type='',$type_id=''){
  if(is_logged_in()){
  $type = sanitize($type);
  $type_id = sanitize($type_id);
  $email = $_SESSION['email'];

  # Get Customer
  $customer = Customer::get_details($username);

  # Create Order
  Orders::new_order($customer,$type,$type_id);
  $q = query_db("SELECT * FROM store_orders WHERE product='{$type}' AND product_id='{$type_id}' AND email='{$email}' order by id desc LIMIT 1",'Could not get order!');
  $order = $q['result'][0];
  $_SESSION['temp'] = $order;

  Plugins::load('slampacks>>views>>orders.order-summary');
  } else {
    log_in_to_continue(' to place an order!');
  }
}

?>
