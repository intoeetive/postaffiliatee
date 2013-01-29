<?php

/*
=====================================================
 postaffiliatEE
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011 Yuriy Salimovskiy
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2011. Please see
 http://expressionengine.com/docs/license.html
=====================================================
 File: mcp.postaffiliatee.php
-----------------------------------------------------
 Purpose: Post Affiliate Pro integration
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}



class Postaffiliatee_mcp {

    var $version = '1.0';
    
    var $settings = array();
    
    var $perpage = 50;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    
    function index()
    {
        
    }    


}
/* END */
?>