# laravel-zarinpal
A laravel package for ZarinPal gateway based on REST

This pacakge enables you to accept and verify payments from ZarinPal gateway which is based on REST.

## Installation
The package can be installed through Composer:
```
composer require rasulian/laravel-zarinpal
```
  
You'll need to register the service provider:
```
// config/app.php

'providers' => [
    // ...
    Rasulian\ZarinPal\ZarinPalServiceProvider::class,
];
```

To publish the config file to config/zarinpal.php run:
```
php artisan vendor:publish --provider="Rasulian\ZarinPal\ZarinPalServiceProvider"
```

This is the default contents of the configuration:

```
// config/zarinpal.php

<?php

return [
  'params' => [
    'merchant-id' => '',

    // Leave it empty if you're passing the callback url when doing the request
    'callback-url' => '',

    // A summary of your product or application, if needed
    'description' => '',
  ],

  // Set to true if you want to test the payment in sandbox mode
  'testing' => false
];


```

## Usage

**1. Redirecting the customer to the Zarin Pal**

Let's get technical. In the controller in which you will redirect the customer to the ZarinPal you must inject the payment gateway like so:

```
  use Rasulian\ZarinPal\Payment;

  class CheckoutConfirmOrderController extends Controller {


    /**
     * @param $zarinPal
     */
    protected $zarinPal;

    public function __construct(Payment $zarinPal)
    {
      ...
      $this->zarinPal = $zarinPal;
      ...
    }
```

In the same controller in the method in which you redirect the customer to the ZarinPal you must set the $order that you've probably build up during the checkout-process.

```
public function doPayment(Request $request)
{
    $invoice = $this->invoiceRepo->getCurrentInvoice();
    // Doing the payment
    $payment = $this->zarinPal->request(
    
        // The total price for the order
        $invoice->totalPrice,
        
        // Pass any parameter you want when the customer successfully do the payment
        // and gets back to your site
        ['paymentId' => $invoice->payment_id],
        
        // Callback URL
        route('checkout.payment.callback'),
        
        // A summary of your product or application
        'Good product'
    );

    // Throw an exception if the payment request result had any error
    if ($payment->get('result') == 'warning')
        throw new Exception($payment->get('error'));

    // Redirect the customer to the ZarinPal gateway to do the payment
    return redirect()->away($payment->get('url'));
}
```


**2. Verifying the payment**

So now we've redirected the customer to the payment provider. The customer did some actions there (hopefully he or she paid the order) and now gets redirected back to our shop site.

The payment provider will redirect the customer to the url of the route that is specified in the third parameter of the`request` method or in the `description` option of the config file.

We must validate if the redirect to our site is a valid request.

In the controller that handles the request:

```
  use Rasulian\ZarinPal\Payment;

  class CheckoutPaymentVerificationController extends Controller {


    /**
     * @param $zarinPal
     */
    protected $zarinPal;

    public function __construct(Payment $zarinPal)
    {
        ...
        $this->zarinPal = $zarinPal;
        ...
    }
    
    ...
```

Then, in the same controller, in the method you use to handle the request coming from the payment provider, use the `verify` method:

```
public function verifyPayment(Request $request)
{
    $authority = $request->input('Authority');
    $invoice = $this->invoiceRepo->getCurrentInvoice();

    $verify = $this->zarinPal->verify($invoice->totalPrice, $authority);

    if ($verify->get('result') == 'success') {

        ...
        // Do the needed stuff If the verify was success
        ...

        // If not, we can check which status code is given back to us from the ZarinPal gateway
        // and show a message error correspond to the status code.

    } else if (in_array($verify->get('code'), [-42, -54])) {
        return view('shopping.payment')->with(['error' => $verify->get('error')]);
    }
}
```
