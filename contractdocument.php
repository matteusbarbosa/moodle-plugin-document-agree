<?php 

function make_token($user_id, $obj_id){
    return md5($user_id.$obj_id);
}

function check_token($token, $user_id, $obj_id){
   return $token == make_token($user_id, $obj_id);
}
