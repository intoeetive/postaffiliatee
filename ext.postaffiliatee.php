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
 File: ext.postaffiliatee.php
-----------------------------------------------------
 Purpose: Post Affiliate Pro integration
=====================================================
*/

if ( ! defined('BASEPATH'))
{
	exit('Invalid file request');
}

class Postaffiliatee_ext {

	var $name	     	= 'PostaffiliatEE';
	var $version 		= '1.0';
	var $description	= 'Post Affiliate Pro integration';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.intoeetive.com/docs/postaffiliatee.html';
    
    var $settings = array();
    
    var $salt = "dkf_uJHY76G6g7vyt00x"; //just a random string
    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();
        $this->settings = $settings;
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        
        $hooks = array(
            array(
    			'hook'		=> 'user_edit_end',
    			'method'	=> 'user_edit_end',
    			'priority'	=> 10
    		),
            array(
    			'hook'		=> 'member_member_login_single',
    			'method'	=> 'affiliate_login',
    			'priority'	=> 10
    		)
            
    	);
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            $this->EE->db->insert('extensions', $data);
    	}	
    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}
    	
    	if ($current < '2.0')
    	{
    		// Update to version 1.0
    	}
    	
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');
    }
    
    
    // --------------------------------
    //  Settings
    // --------------------------------  
    
    function settings()
    {
        $settings = array();
        
        $sql = "SELECT m_field_id, m_field_label FROM exp_member_fields ORDER BY m_field_order ASC ";
        $q = $this->EE->db->query($sql);
        $fields[''] = '';
        foreach ($q->result_array() as $row)
        {
            $fields[$row['m_field_id']] = $row['m_field_label'];
        }
        
        $settings['pap_url'] = '';
        
        $settings['pap_path'] = '';

        $settings['affiliate_trigger'] = array('s', $fields, '');
        
        $settings['merchant_login'] = '';
        
        $settings['merchant_password'] = '';
        
        $settings['first_name_field'] = array('s', $fields, '');
        
        $settings['last_name_field'] = array('s', $fields, '');
        
        $status_a = array("A"=>$this->EE->lang->line('approved'), 
                        "D"=>$this->EE->lang->line('declined'), 
                        "P"=>$this->EE->lang->line('pending'));
        
        $settings['default_status'] = array('s', $status_a, 'P');
        

        return $settings;
    }
    
    
    
    function user_edit_end($member_id, $mdata, $cfields)
    {
        //var_dump($this->settings);
        if (!empty($this->settings['pap_url']) && !empty($this->settings['pap_path']) && !empty($this->settings['affiliate_trigger']) && !empty($this->settings['merchant_login']) && !empty($this->settings['merchant_password']))
        {
            $userdata = $mdata;
            //we want the complete data!
            $q = $this->EE->db->query("SELECT * FROM exp_member_data WHERE exp_members.member_id=".$member_id);
            foreach ($q->result_array() as $row)
            {
                foreach ($row as $key=>$val)
                {
                    $userdata[$key] = $val;
                }
            }
            return $this->_register_or_update_merchant($userdata);
        }
        //exit();
    }
    
  
    function _register_or_update_merchant($userdata)
    {
        require_once(rtrim($this->settings['pap_path'],'/').'/api/PapApi.class.php');

        $session = new Gpf_Api_Session(rtrim($this->settings['pap_url'],'/').'/scripts/server.php');
        if(!$session->login($this->settings['merchant_login'],$this->settings['merchant_password'])) {
            $this->EE->load->library('logger');
            $this->EE->logger->log_action($this->EE->lang->line('pap').": ".$session->getMessage());
            //die($session->getMessage());
            return false;
        }
        
        //trigger field not empty?
        if (trim($userdata['m_field_id_'.$this->settings['affiliate_trigger']])!='')
        {
            // loading affiliate
            $affiliate = new Pap_Api_Affiliate($session);
            $affiliate->setUsername($userdata['email']);
            try {
                if(!$affiliate->load()) {
                    //exists, but not able to load
                    $this->EE->load->library('logger');
                    $this->EE->logger->log_action($this->EE->lang->line('pap').": ".$affiliate->getMessage());
                }
            } catch (Exception $e) {
                //no affiliate? create one!
                $affiliate->setPassword(md5($userdata['email'].$this->salt));
                if (trim($userdata['m_field_id_'.$this->settings['first_name_field']])!='' && trim($userdata['m_field_id_'.$this->settings['last_name_field']])!='')
                {
                    $affiliate->setFirstname($userdata['m_field_id_'.$this->settings['first_name_field']]);
                    $affiliate->setLastname($userdata['m_field_id_'.$this->settings['last_name_field']]);
                }
                else
                {
                    $affiliate->setFirstname($this->EE->lang->line('default_firstname'));
                    $affiliate->setLastname($userdata['screen_name']);
                }
                $affiliate->setStatus($this->settings['default_status']);
                //var_dump($userdata);
                if(!$affiliate->add()) {
                    // cannot add record
                    $this->EE->load->library('logger');
                    $this->EE->logger->log_action($this->EE->lang->line('pap').": ".$affiliate->getMessage());
                    return false;
                } else {
                    // affiliate was successfully added
                    return true;
                }    
                            
            }
        }
    }
    
    
    
    function affiliate_login($row)
    {
        //trigger field not empty?
        if (!empty($this->settings['affiliate_trigger']))
        {
            $q = $this->EE->db->query("SELECT screen_name, email, exp_member_data.* FROM exp_members LEFT JOIN exp_member_data ON exp_members.member_id=exp_member_data.member_id WHERE exp_members.member_id=".$row->member_id);
            if ($q->row("m_field_id_".$this->settings['affiliate_trigger'])!='')
            {
                require_once(rtrim($this->settings['pap_path'],'/').'/api/PapApi.class.php');

                $session = new Gpf_Api_Session(rtrim($this->settings['pap_url'],'/').'/scripts/server.php');
                if(!$session->login($q->row('email'), md5($q->row('email').$this->salt), Gpf_Api_Session::AFFILIATE)) {
                    //die ($session->getMessage());
                    //unable to login? that's ok, dear...
                    /*$userdata = array();
                    foreach ($q->result_array() as $row)
                    {
                        foreach ($row as $key=>$val)
                        {
                            $userdata[$key] = $val;
                        }
                    }
                    return $this->_register_or_update_merchant($userdata);*/
                }
                header('Location: '.$session->getUrlWithSessionInfo($this->EE->config->item('site_url').'pap/affiliates/panel.php'));
                exit();
            }
        }
        return true;
    }
    
    

}
// END CLASS
