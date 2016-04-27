<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}



/**
 * Signme in as a user.
 */
class Signmeinas extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'signmeinas';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Gary Constable';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Sign me in as');
        $this->description = $this->l('Use this module to sign in as a customer.');

        $this->confirmUninstall = $this->l('Do you really want to uninstall?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    
    
    /**
     * Install module mehod
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     * --
     */
    public function install()
    {
        Configuration::updateValue('SIGN-ME-IN-AS_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminCustomers');
    }
    
    
    
    /**
     * Uninstall module method
     * --
     * @return type
     */
    public function uninstall()
    {
        Configuration::deleteByName('SIGN-ME-IN-AS_LIVE_MODE');

        return parent::uninstall();
    }

    
    
    /**
     * Create the link and redirect to logn
     * --
     * @param type $customer
     */
    private function doLogin($customer)
    {
        $str = "/index.php?controller=authentication&email=".$customer->email."&logmein=1&back=my-account";
        header('Location: '. $str );
        exit();
	}
    
    
    
    /**
     * 
     * --
     */
    public function loginRedirect()
    {    
        $customer = new Customer( (int)tools::getValue("id_customer"));
        $this->doLogin($customer);
        exit();
    }
    
    
    
    /**
     * Is the current logged in user admin?
     * --
     * @return boolean
     */
    public function isAdmin()
    {
        $cookie = new Cookie('psAdmin');
        if ($cookie->id_employee){
            return true;
        }
        return false;
    }
    
    
    
    /**
     * is the log me in request present?
     * --
     * @return boolean
     */
    public function requestedLogmein()
    {
        if( tools::getValue("logmein") ){
            return true;
        }
        return false;
    }
    
    
    
    /**
     * Get the admin customers link
     * --
     * @return type
     */
    public function getAdminCustomersUrl()
    {    
        $o_customer = new Customer( (int)tools::getValue("id_customer") );
        $link = new Link();
        return $link->getAdminLink('AdminCustomers', true) . '&id_customer='.$o_customer->id.'&viewcustomer&logmein=true';
    }

    
        
    /**
     * Display link in the admin customers page using hook.
     * --
     * @return type
     */
    public function hookDisplayAdminCustomers()
    {
        //--> only if the user is an admin
        if( $this->isAdmin() === true )
        {
            //--> if we have the request in the url
            if( $this->requestedLogmein() === true ){
                $this->loginRedirect();
            }
            
            return '
            <div class="col-lg-12">
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-map-marker"></i> Log in as this customer. <span class="badge"></span>
                    </div>
                    <div class="panel-body">
                        <p><a href="'.$this->getAdminCustomersUrl().'">Click here to sign in as this customer.</a></p>
                    </div>
                </div>
            </div>';
        }
    }
}
