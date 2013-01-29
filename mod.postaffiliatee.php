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
 File: mod.postaffiliatee.php
-----------------------------------------------------
 Purpose: Post Affiliate Pro integration
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Postaffiliatee {

    var $return_data	= ''; 						// Bah!
    
    var $settings 		= array();    
    

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 

        $this->EE->lang->loadfile('postaffiliatee');  
   
    }
    /* END */
    



}
/* END */

/**
* TODO:
* - setting to require email confirmation
*/
?>