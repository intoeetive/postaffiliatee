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
 File: upd.postaffiliatee.php
-----------------------------------------------------
 Purpose: Post Affiliate Pro integration
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}



class Postaffiliatee_upd {

    var $version = '1.0';
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
        $this->EE->lang->loadfile('postaffiliatee');  
    } 
    
    function install() { 
        
        $this->EE->load->dbforge(); 
        
        //----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}

        $data = array( 'module_name' => 'Postaffiliatee' , 'module_version' => $this->version, 'has_cp_backend' => 'n' ); 
        $this->EE->db->insert('modules', $data); 
        
        //$data = array( 'class' => 'Postaffiliatee' , 'method' => 'submit' ); 
        //$this->EE->db->insert('actions', $data); 
        
        return TRUE; 
        
    } 
    
    function uninstall() { 
        
        $this->EE->load->dbforge(); 
        
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Postaffiliatee')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Postaffiliatee'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Postaffiliatee'); 
        $this->EE->db->delete('actions'); 
        
        return TRUE; 
    } 
    
    function update($current='') { 
        $this->EE->load->dbforge(); 
        
        if ($current < 3.0) { 
            // Do your 3.0 v. update queries 
        } 
        return TRUE; 
    } 
	

}
/* END */
?>