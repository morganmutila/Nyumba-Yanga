<?php

// Auto load classes in case they have not been required
function my_autoload ($class_name){
    if(preg_match("/\A\w+\Z/", $class_name)){
        $path = CLASS_PATH .DS. strtolower($class_name) . ".class.php";
        if (file_exists($path)) {
            include($path);
        }
        else{
            die("The file {$class_name}.class.php can not be found");
        }   
    }    
}

spl_autoload_register("my_autoload");

function NY_SEARCH_ENGINE(){
    global $session, $found_location;

    $html  = "<form action=\"search.php\" method=\"GET\" style=\"position:relative;\">";
    $html .=    "<div class=\"input-group mb-2 mr-sm-2 mb-sm-0\">
                    <input type=\"text\" name= \"q\" class=\"form-control form-control-lg\" placeholder=\"Search location\" ";
    $html .=            "value=";
                        if(Input::get('q')){
                            $html .= $found_location;
                        }
    $html .=           ">";
    if(isset($session->location)){
        $html .=        "<div class=\"input-group-append\"><div class=\"input-group-text\"><i class=\"mdi mdi-map-marker-outline\"></i>".Location::findLocationOn($session->location)."</div></div>";
    }
    $html .=    "</div>";
    
    $html .= "</form>";
    return $html;
}

function NY_PAGINATION(){
    global $pagination, $page;

    $html = "<ul class=\"pagination\">"; 
            $pages = ceil($pagination->offset() - 1);
            if($pagination->total_pages() > 1){
                if($pagination->has_previous_page()){                                 
                    $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"".escape($_SERVER['PHP_SELF'])."?page=". $pagination->previous_page();
                    $html .= "\"><i class=\"mdi mdi-chevron-left\"></i></a></li>";     
                }
                if($pagination->previous_page() == 0){
                    $html .= "<li class=\"page-item disabled\"><span class=\"page-link\"><i class=\"mdi mdi-chevron-left\"></i></span></li>";           
                }   
                for($i = 1; $i <= $pagination->total_pages(); $i++){
                    if($i == $page){
                        $html .= "<li class=\"page-item active\"><span class=\"page-link\">{$i}</span></li>";
                    }else{
                        $html .= "<li class=\"page-item\"><a href=\"".escape($_SERVER['PHP_SELF'])."?page={$i}\" class=\"page-link\">{$i}</a></li>";
                    }
                }                                       

                if($pagination->total_pages() < $pagination->next_page()){
                    $html .= "<li class=\"page-item disabled\"><span class=\"page-link\"><i class=\"mdi mdi-chevron-right\"></i></span></li>";
                }               
                if($pagination->has_next_page()){                                         
                    $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"".escape($_SERVER['PHP_SELF'])."?page=". $pagination->next_page();
                    $html .= "\"><i class=\"mdi mdi-chevron-right\"></i></a></li>";
                }                                   
            }                           

    $html .= "</ul>";
    return $html;
}   

function sortby_filters($sortby){
    if (isset($sortby)):
        switch ($sortby) {
            case 'best':
                $sortby = "views DESC";
                break;
            case 'price_asc':
                $sortby = "price ASC";
                break;
            case 'price_desc':
                $sortby = "price DESC";
                break;        
            case 'new':
                $sortby = "added DESC";
                break;
            case 'beds':
                $sortby = "beds DESC";
                break;
        }
        return $sortby;
    else: 
        return "added DESC";   
    endif;
}

function search_filters($price_filters, $beds_filters){
    //if(isset($price_filters) AND isset($beds_filter)):
        switch ($price_filters) {
            case 'anyprice':
                $price_filters = "IS NOT NULL";
                break;
            case 'below2k':
                $price_filters = "<= 2000";
                break;
            case 'between2kto5k':
                $price_filters = "BETWEEN 2000 AND 5000";
                break;        
            case 'between5kto10k':
                $price_filters = "BETWEEN 5000 AND 10000";
                break;
            case 'between10kto15k':
                $price_filters = "BETWEEN 10000 AND 15000";
                break;
            case 'between15kto20k':
                $price_filters = "BETWEEN 15000 AND 20000";
                break;
            case 'above20k':
                $price_filters = ">= 20000";
                break;        
        }

        switch ($beds_filters) {
            case 'any':
                $beds_filters = "IS NOT NULL";
                break;
            case '1':
                $beds_filters = "= 1";
                break;
            case '2':
                $beds_filters = "= 2";
                break;        
            case '3':
                $beds_filters = "= 3";
                break;
            case '4':
                $beds_filters = "= 4";
                break;
            case 'above5':
                $beds_filters = ">= 5";
                break;       
        }
         return " AND price ".$price_filters . " AND beds " . $beds_filters;
    // else:    
    //     return "";
    // endif;
}

function pre($value){
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

function varpre($value){
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

function escape($string){
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

function new_listing($datetime){
    $datetime = new DateTime($datetime);
    $valid_for = new DateInterval(Config::get('new_listing'));

    $now = new DateTime();

    $expiry_date = clone $datetime;
    $expiry_date->add($valid_for);

    return $now < $expiry_date;
}

function end_post_date($datetime){
    $datetime = new DateTime($datetime);
    $valid_for = new DateInterval(Config::get('end_post_date'));

    $now = new DateTime();

    $expiry_date = clone $datetime;
    $expiry_date->add($valid_for);

    return $now < $expiry_date;
}

// Time functions
function time_ago($time){
    $formated_time = strtotime($time);
    $time_difference = time() - $formated_time;
 
    if($time_difference < 1 ) { return strtoupper('less than 1 second ago'); }
    
    $condition = array( 
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
    );
 
    foreach( $condition as $secs => $str ){

        $d = $time_difference / $secs;

        if( $d >= 1 ){
            $t = round( $d );
            return ucfirst($t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago');
        }

    }
}

function text_to_datetime($format){
    return strftime($format, time());
}

function mysql_datetime(){
    return text_to_datetime(Config::get('mysql_date_time_format'));
}

function datetime_to_text($datetime="") {
  $unixdatetime = strtotime($datetime);
  return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
} // End Time functions

function page_title($page_title=""){
    if(empty($page_title)  && !isset($page_title)){
        $page_title = "Nyumba Yanga · List your property";
    }
    else{
        $page_title = $page_title;
    }
    return $page_title;
}

function include_layout_template($template=""){
    global $page_title, $session, $user;
    include(INCLUDE_PATH .DS.'layouts'.DS.$template);
}

function output_message($message="",  $type="info") {
    $output = "";   
    if (!empty($message)) {    
        switch ($type) {
            case $type == 'success':
                $output .= "<p class=\"message-success\"><i class=\"mdi mdi-check-circle-outline mdi-18px\"></i><span>".$message."</span></p>";
                break;
            
            case $type == 'warning':
                $output .= "<p class=\"message-warning\"><i class=\"mdi mdi-alert-circle-outline mdi-18px\"></i><span>".$message."</span></p>";
                break;

            case $type == 'info':
                $output .= "<p class=\"message-info\"><i class=\"mdi mdi-information-outline mdi-18px\"></i><span>".$message."</span></p>";
                break;  

            case $type == 'danger':
            $output .= "<p class=\"message-danger\"><i class=\"mdi mdi-alert-circle-outline mdi-18px\"></i><span>".$message."</span></p>";
            break;

            case $type == 'text-danger':
            $output .= "<p class=\"text-danger\">".$message."</span></p>";
            break;   
        }
    }    
    return $output;
}

function flash($name, $type="info"){
    $output = "";
    if(Session::exists($name)){        
        switch ($type) {
            case $type == 'success':
                $output .= "<p class=\"message-success\"><i class=\"mdi mdi-check-circle-outline mdi-18px\"></i><span>".Session::flash($name)."</span></p>";
                break;
            
            case $type == 'warning':
                $output .= "<p class=\"message-warning\"><i class=\"mdi mdi-alert-circle-outline mdi-18px\"></i><span>".Session::flash($name)."</span></p>";
                break;

            case $type == 'info':
                $output .= "<p class=\"message-info\"><i class=\"mdi mdi-information-outline mdi-18px\"></i><span>".Session::flash($name)."</span></p>";
                break;  

            case $type == 'danger':
            $output .= "<p class=\"message-danger\"><i class=\"mdi mdi-alert-circle-outline mdi-18px\"></i><span>".Session::flash($name)."</span></p>";
            break;   
        }
    }    
    return $output;
}

function log_action($action, $message="") {
    $logfile = SITE_ROOT.DS.'logs'.DS.'log.txt';
    $new = file_exists($logfile) ? false : true;
  if($handle = fopen($logfile, 'a')) { // append
    $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
        $content = "{$timestamp} | {$action}: {$message}\n";
    fwrite($handle, $content);
    fclose($handle);
    if($new) { chmod($logfile, 0755); }
  } else {
    echo "Could not open log file for writing.";
  }
}

function create_form_select($name="", $count=null, $accept=""){
    if($count > 0){
        if($accept == "fractions"){
            $output = "<select name=\"{$name}\">";
                for($i=1; $i <= $count; $i = $i + 0.5){               
                    if ($i == $count) {
                        $output .= "<option value=\"$i\">$i+</option>";
                    }else{
                        $output .= "<option value=\"$i\">$i</option>";
                    }
                } 
            $output .= "</select>";
            return $output;
        }    
        else{
            $output = "<select name=\"{$name}\">";
                for($i=1; $i <= $count; $i++){               
                    if ($i == $count) {
                        $output .= "<option value=\"$i\">$i+</option>";
                    }else{
                        $output .= "<option value=\"$i\">$i</option>";
                    }
                } 
            $output .= "</select>";
            return $output;
        } 
    }
    return "";
}

function generate_form_select($name="", $empty_select=true, $key_values=array()){
    if(count($key_values)){
        $output = "<select name=\"{$name}\">";
            if ($empty_select) {
                $output .= "<option value=\"\">Please select --</option>";
            }
            foreach ($key_values as $key => $value) {
                $output .= "<option value=\"$value\" ";
                    if(Input::get($name) == $value){ $output .= "selected"; }
                $output .= ">".$key."</option>";
            }            
        $output .= "</select>";
        return $output;
    }
    return "";
}

function generate_form_checkbox($name="", $key_values=array()){
    $output = "";
    if(count($key_values)){        
        foreach ($key_values as $key => $value) {
            $output .= "<label><input type=\"checkbox\" name=\"{$name}\" value=\"{$value}\""; 
                if(Input::get($name) == $value){ 
                    $output .= "checked";
                }
            $output .="/>".$key."</label>";
        }            
        return $output;
    }
    return "";
}

//Money format
function amount_format($amount = '0', $symbol = 'K') {
    $amount = round($amount, 2);
    $sign = '';
    if ( substr($amount, 0, 1) == '-'){
        $sign = '-';
        $amount = substr($amount, 1);
    }
    if($symbol == " ") {        // If you want the format without any symbol then pass space, ie: " "
        $amount = $sign . number_format($amount, 0,'.','');
    } else {
        $amount = $sign . $symbol . number_format($amount, 0);
    }
    return $amount;
}

function fav_add($property_id=0){
    global $session;

    if ($session->isLoggedIn()){
        return '<a href="list_save.php?id='.$property_id.'"style="color:#fff;float:right;padding:.4rem;background:#0000004f;height:2.1rem;"><i class="mdi mdi-heart-outline mdi-24px"></i></a>';
    }
}

function fav_remove($property_id=0){
    global $session;

    if ($session->isLoggedIn()){
        return '<a href="list_unsave.php?id='.$property_id.'" style="color:#01e675;float:right;padding:.4rem;background:#0000004f;height:2.1rem;"><i class="mdi mdi-heart mdi-24px"></i></a>';
    }
}
