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
