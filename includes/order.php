<?php

class Orders{
  public function new_order($customer=[],$type='',$type_id=''){
    $email = $customer['email'] ? $customer['email'] : $_SESSION['email'];
    $type = sanitize($type);
    
    $type_id = sanitize($type_id);
    //~ $customer_id = $customer['id'];
    $card = Api::get_one($type,'id',$type_id);
    //~ print_r($card);
    $order_details = " {$card['name']} {$card['identifier']}";
    $order_total = $card['price'];
    $created = date('c');
    
    $q = query_db("INSERT INTO `store_orders`(`id`, `product`, `product_id`, `email`, `order_total`, `order_status`, `order details`, `created`) 
    VALUES ('0','{$type}','{$type_id}','{$email}','{$order_total}','pending payment','{$order_details}','{$created}')",
    'Could not save new order!');
    
    
  }
  
  
  
  function checkout($order_id=''){
    $order = Api::get_one('store_orders','id',$order_id);
    $customer = Api::get_one('store_customers','id',$order['customer_id']);
    Slampacks_Paypal::load_paypal($order);
    $_SESSION['temp'] = $order;
    Plugins::load('slampacks>>views>>orders.checkout-success');
  }
  
  public function order_paid_in_full($order_id=''){
    $order_id = sanitize($order_id);
    $order = Api::get_one('store_orders','id',$order_id);
    if($order['order_status'] == 'paid'){
      return true;
    } else {
      return false;
    }
  }
  
  public function bonus_given($order_id=''){
    $order_id = sanitize($order_id);
    $order = Api::get_one('store_orders','id',$order_id);
    
    if($order['bonus_given'] == 'yes'){
      return true;
    } else {
      return false;
    }
  }
}

function my_orders(){
  if(is_logged_in() && is_my_profile_page()){
    $email = sanitize($_SESSION['email']);
    $q = query_db("SELECT * FROM store_orders WHERE email='{$email}'",'Could not get my orders! ');
    $_SESSION['temp'] = $q;
    Plugins::load('slampacks>>views>>orders.my-orders');
  }
}

function orders($customer_email=''){
  if(is_admin()){
    $email = sanitize($customer_email);
    if(!empty($customer_email)){
      $q = query_db("SELECT * FROM store_orders WHERE email='{$email}'",
      'Could not get Orders from '.$email.'! ');
    } else {
      $q = Api::get_many('store_orders','DESC');
    }
    $_SESSION['temp'] = $q;
    Plugins::load('slampacks>>views>>orders.orders');
  } else {
    deny_access();
  }
}

?>
