# Sign me in as

Pretashop module that allows admin user to login to front office as customer.

### Install

Install module as normal, then add these overrides.

#### override/controllers/front/AuthController.php

```
  /**
	 * Start forms process
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
    if (Tools::isSubmit('SubmitCreate'))
      $this->processSubmitCreate();

	  if (Tools::isSubmit('submitAccount') || Tools::isSubmit('submitGuestAccount'))
		  $this->processSubmitAccount();

	  if (Tools::isSubmit('SubmitLogin'))
		  $this->processSubmitLogin();
        
    if (Tools::isSubmit('logmein'))
		  $this->processLogmein();
	}
```


#### override/controllers/front/AuthController.php

```
    /**
     * Process login
     * --
     */
     protected function processLogmein()
	{

		Hook::exec('actionBeforeAuthentication');
		$email = trim(Tools::getValue('email'));

		if (empty($email))
			$this->errors[] = Tools::displayError('An email address required.');
		elseif (!Validate::isEmail($email))
			$this->errors[] = Tools::displayError('Invalid email address.');
		else
		{

            //get the customer row via email address
            $query = " select id_customer from ps_customer where email = '".$email."' ";
            $row = DB::getInstance()->executeS($query);

            //only if the row can be found via email
            if(isset($row[0]['id_customer'])){

                $customer = new Customer($row[0]['id_customer']);
			
                $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
                $this->context->cookie->id_customer = (int)($customer->id);
                $this->context->cookie->customer_lastname = $customer->lastname;
                $this->context->cookie->customer_firstname = $customer->firstname;
                $this->context->cookie->logged = 1;
                $customer->logged = 1;
                $this->context->cookie->is_guest = $customer->isGuest();
                $this->context->cookie->passwd = $customer->passwd;
                $this->context->cookie->email = $customer->email;

                // Add customer to the context
                $this->context->customer = $customer;

                if (Configuration::get('PS_CART_FOLLOWING') && (empty($this->context->cookie->id_cart) || Cart::getNbProducts($this->context->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id))
                    $this->context->cart = new Cart($id_cart);
                else
                {
                    $this->context->cart->id_carrier = 0;
                    $this->context->cart->setDeliveryOption(null);
                    $this->context->cart->id_address_delivery = Address::getFirstCustomerAddressId((int)($customer->id));
                    $this->context->cart->id_address_invoice = Address::getFirstCustomerAddressId((int)($customer->id));
                }
                $this->context->cart->id_customer = (int)$customer->id;
                $this->context->cart->secure_key = $customer->secure_key;
                $this->context->cart->save();
                $this->context->cookie->id_cart = (int)$this->context->cart->id;
                $this->context->cookie->write();
                $this->context->cart->autosetProductAddress();

                Hook::exec('actionAuthentication');

                // Login information have changed, so we check if the cart rules still apply
                CartRule::autoRemoveFromCart($this->context);
                CartRule::autoAddToCart($this->context);

                if (!$this->ajax)
                {
                    if (($back = Tools::getValue('back')) && $back == Tools::secureReferrer($back))
                        Tools::redirect(html_entity_decode($back));
                    Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : 'my-account'));
                }
                
            }
            else{
                
                d(array(
                    '---> Cound not find customer.',
                    $email
                ));
                $this->errors[] = Tools::displayError('Invalid email address.');
                
            }
			
		}
		if ($this->ajax)
		{
			$return = array(
				'hasError' => !empty($this->errors),
				'errors' => $this->errors,
				'token' => Tools::getToken(false)
			);
			die(Tools::jsonEncode($return));
		}
		else
			$this->context->smarty->assign('authentification_error', $this->errors);
	}
```
