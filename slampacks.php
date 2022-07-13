<?php
class Slampack{
	
	public function pick_random(){ 
    //~ echo 'about to'; exit;
		$q = query_db("SELECT * FROM cards WHERE (num_pulled < pull_rate) Order by rand() LIMIT 1",
    'Could not pull random card');
    
   
		$bonus =  $q['result'][0];
    
    $bonus_id = $bonus['id'];
    $bonus_pack = $bonus['pack'];
    
    #Update num pulled
    $q = query_db("UPDATE cards SET num_pulled=num_pulled+1 WHERE id='{$bonus_id}'",
    'Could not update num pulled in Cards! ');
    
    #Update pack 
    $q = query_db("UPDATE packs SET num_pulled=num_pulled+1 WHERE name='{$bonus_pack}'",
    'Could not update num pulled in Packs! ');
    
    return $bonus;
    
	}
}

function get_cards(){

	Plugins::load('slampacks>>views>>slampacks-image');
	Plugins::load('slampacks>>views>>cards.get-cards-form');

	$pack = sanitize($_GET['pack']) ? $_GET['pack'] : '';
	$display = sanitize($_GET['display']) ? $_GET['display'] : '';
	$sort = sanitize($_GET['sort-input']) ? $_GET['sort-input'] : '';

	if(empty($sort)){
		$pack_cards = glob(dirname(__FILE__).'/images/Cards/'.$pack.'/*');
		$p[$pack]=$pack_cards;
		$_SESSION['temp'] = $p;
    } else {
		if($sort == 'A-Z'){
			$order = "Order by name Asc";
		} else if($sort == 'Z-A'){
			$order = "Order by name Desc";
		} else if($sort == 'rarity-desc'){
			$order = "Order by pull_rate Desc";
		} else if($sort == 'rarity-asc'){
			$order = "Order by pull_rate asc";
		}

		$sql = "SELECT * FROM cards WHERE pack='{$pack}'  {$order}";
		$pack_cards = query_db("{$sql}",
		'Could not get cards! ');
		
		$_SESSION['temp'] = $pack_cards;
		
	}
  
	if(!empty($pack) && $pack != 'All'){
		if($display == 'rows'){
			Plugins::load('slampacks>>views>>cards.card-results-row');
		} else if($display == 'grid'){
			Plugins::load('slampacks>>views>>cards.card-results-grid');
		}
	} else{
		$_SESSION['temp'] = query_db("SELECT * FROM packs",'Could not get packs! ');
		
		if($display == 'grid' || empty($display) ){
			Plugins::load('slampacks>>views>>cards.card-results-grid-all');
		} else if($display == 'rows'){
			Plugins::load('slampacks>>views>>cards.card-results-rows-all');
		}
     //~ redirect_to(BASE_PATH);
	}
	
}

function get_cards_in_all_packs($view="row"){
   
  if(empty($_POST['pack']) || $_POST['pack'] == 'All'){
    $packs_dir = glob(dirname(__FILE__).'/images/Cards/*');
    //~ print_r($packs_dir);
    $folder_names = [];
    $count = 0;
    foreach ($packs_dir as $folder){
      //~ echo $folder.' <br>';
      $folder_name = ltrim($folder,DIR_PATH.'Plugins/slampacks/images/Cards/');
      //~ print_r($folder_name);
      $files = glob($folder.'/*');
      foreach($files as $file){
        $cards[$folder_name]['files'][] = str_ireplace(DIR_PATH,BASE_PATH,$file);
      }
      
    }
     //~ print_r($cards).' <br>';
     $_SESSION['temp'] = $cards;
    Plugins::load('slampacks>>views>>cards.get-cards-form');
    if($view == 'row'){
      Plugins::load('slampacks>>views>>cards.card-results-row');
    } else if($view == 'single'){
      Plugins::load('slampacks>>views>>cards.card-results-single');
    }
  }
}


function full_card($folder_name='',$identifier=''){
	$folder_name = sanitize($folder_name);
	if(strtolower($identifier) == 'packs'){
		$q = query_db("SELECT * FROM packs WHERE name='{$folder_name}' ",'Could not get full Pack card! ');
	  //~ $_SESSION['temp'] =$q['result'][0];
	} else {
		$identifier = strstr(sanitize(str_ireplace('--','#',$identifier)),'.',true);
  //~ echo $identifier;
	  $q = query_db("SELECT * FROM cards WHERE pack='{$folder_name}' AND identifier='{$identifier}' ",'Could not get full card! ');
  }
	$q['type'] = $folder_name;
	$q['identifier'] = $identifier;
	$_SESSION['temp'] =$q;
 	Plugins::load('slampacks>>views>>cards.full-card');
}


function get_packs(){
  $packs = glob(PLUGINS_PATH.'slampacks/images/Packs/*');
  $_SESSION['temp'] = $packs;
  Plugins::load('slampacks>>views>>cards.packs-anime');
}

function get_bonus($order_id=''){
  if(Orders::order_paid_in_full($order_id) && !Orders::bonus_given($order_id)){
    $bonus_card = Slampack::pick_random();
    $wallet_bonus = $bonus_card['price'];
    $username = $_SESSION['username']; 
    update_user_funds('bonus',$username,$wallet_bonus,'Bonus Card Drawn Value. ','USD');
    toast('notice','You have been Awarded a bonus card and $'.$wallet_bonus.' has been added to your wallet! ');
    $_SESSION['temp'] = $bonus_card;
    Plugins::load('slampacks>>views>>cards.bonus.bonus-card');
  }
}

function about(){
	Plugins::load('slampacks>>views>>about');
}
?>
