<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserAccessFilter
 *
 * @author andre
 */
class UserAccessFilter extends CFilter {
    
    protected function preFilter($filterChain) {
        parent::preFilter($filterChain);
    }
    
    protected function testToken() {
        if (!function_exists('apache_request_headers')) {
            function apache_request_headers() {
                $arh = array();
                $rx_http = '/\AHTTP_/';
                foreach ($_SERVER as $key => $val) {
                    if (preg_match($rx_http, $key)) {
                        $arh_key = preg_replace($rx_http, '', $key);
                        $rx_matches = array();
                        $rx_matches = explode('_', $arh_key);
                        if (count($rx_matches) > 0 && strlen($arh_key) > 2) {
                            foreach ($rx_matches as $ak_key => $ak_val) {
                                $rx_matches[$ak_key] = ucfirst($ak_val);
                            }
                            $arh_key = implode('_', $rx_matches);
                        }
                        $arh[$arh_key] = $val;
                    }
                }

                return $arh;
            }
        }
        
        
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/Token (.*)/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                $token = $matches[1];
                $userToken = UserTokens::model()->findByAttributes(array('token'=>$token));
                if ($userToken != null)
                    return true;
            }
        }
        
        header("HTTP/1.0 001 Unauthorized");
        exit;
        
        return false;
    }
    
    private function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 && strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    $arh_key = implode('_', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        
        return $arh;
    }


    protected function testUser($data) {
        if (!isset($data['uniqueid'])) {
            $result = array('status'=>'400');
            echo json_encode($result);
            return false;
        }
        
        $user = $this->getUser($data['uniqueid']);
        if (!$user || $user['enabled'] == 0) {
            $result = array('status'=>'401');
            echo json_encode($result);
            return false;
        }
        
        return true;
    }


    protected function getUser($uniqueid) {
        $query = 'select id, uniqueid, name, description, photo, enabled from users where uniqueid  = :uniqueid';
        $command = Yii::app()->db->cache(0)->createCommand($query);
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(':uniqueid', $uniqueid, PDO::PARAM_STR);
        $user = $command->query();
        $user = $user->read();
        
        return $user;
    }
}
