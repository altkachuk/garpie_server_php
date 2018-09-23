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

Yii::import('application.filters.UserAccessFilter');

class UserAccessGetFilter extends UserAccessFilter {
    
    protected function preFilter($filterChain) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        return $this->testUser($data);
    }
}
