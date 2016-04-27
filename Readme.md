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
