<?php
/**
 * Created by PhpStorm.
 * User: mehran
 * Date: 7/30/17
 * Time: 12:50 PM
 */

namespace Rasulian\ZarinPal;


use App\Collection;
use wmateam\curling\CurlRequest;

class Payment
{
    public function doPayment($amount, $params = [], $callbackUrl = null, $description = null)
    {
        // What type of ZarinPal request we want?
        $requestType = config('zarinpal.testing')?'sandbox':'www';

        // Convert the parameters array to query string.
        $queryStrings = http_build_query($params, '', '&');

        $data = [
            'MerchantID' => config('zarinpal.params.merchant-id'),
            'Amount' => $amount,
            'CallbackURL' => sprintf('%s?%s',
                $callbackUrl?$callbackUrl:config('params.callback-url'), $queryStrings),
            'Description' => $description?$description:config('zarinpal.params.description')
        ];

        $url = sprintf('https://%s.zarinpal.com/pg/rest/WebGate/PaymentRequest.json', $requestType);
        $content = $this->doRequest($data, $url);

        if (is_null($content->get('error'))) {
            $authority = ltrim($content->get('Authority'));

            $out = collect([
                'result' => 'success',
                'code' => $content->get('Status'),
                'url' => sprintf('https://%s.zarinpal.com/pg/StartPay/%s', $requestType, $authority),
            ]);
        } else {
            $out = collect([
                'result' => 'warning',
                'code' => $content->get('Status'),
                'error' => $content->get('error')
            ]);
        }

        return $out;
    }

    public function verifyPayment($amount, $authority)
    {
        // What type of ZarinPal request we want?
        $requestType = config('zarinpal.testing')?'sandbox':'www';

        $data = [
            'MerchantID' => config('zarinpal.params.merchant-id'),
            'Amount' => $amount,
            'Authority' => ltrim($authority)
        ];

        $url = sprintf('https://%s.zarinpal.com/pg/rest/WebGate/PaymentVerification.json', $requestType);
        $content = $this->doRequest($data, $url);

        if (is_null($content->get('error'))) {
            $out = collect([
                'result' => 'success',
                'code' => $content->get('Status'),
                'refId' => ltrim($content->get('RefID'))
            ]);

        } else {
            $out = collect([
                'result' => 'warning',
                'code' => $content->get('Status'),
                'error' => $content->get('error')
            ]);
        }

        return $out;
    }

    /**
     * @param $data
     * @param $url
     * @return Collection
     */
    private function doRequest($data, $url)
    {
        $jsonParams = json_encode($data);
        $curl = new CurlRequest($url);
        $curl->setUserAgent('ZarinPal Rest Api v1');
        $curl->setHeader('Content-Type: application/json');
        $curl->setHeader(sprintf('Content-Length: %s', strlen($jsonParams)));
        $result = $curl->post($jsonParams, CurlRequest::RAW_DATA);

        // Getting the result's body
        $content = collect(json_decode($result->getBody(), true));

        // Putting the errors on the content if there is any
        $content->put('error', $this->getZarinPalError($content->get('Status')));

        if ($curl->getErrors())
            $content->put('error', 'تراکنش با خطا مواجه شد');

        return $content;
    }

    private function getZarinPalError($id)
    {
        switch ($id) {
            case '-1':
                return 'اطلاعات ارسال شده ناقص است.';
                break;
            case '-2':
                return 'آی پی یا مرچنت کد پذیرنده صحیح نیست.';
                break;
            case '-3':
                return 'با توجه به محدودیت های شاپرک امکان پرداخت با رقم درخواست شده میسر نمی باشد.';
                break;
            case '-4':
                return 'سطح تایید پذیرنده پایین تر از صطح نقره ای است.';
                break;
            case '-11':
                return 'درخواست مورد نظر یافت نشد.';
                break;
            case '-12':
                return 'امکان ویرایش درخواست میسر نمی باشد.';
                break;
            case '-21':
                return 'هیچ نوع عملیات مالی برای این تراکنش یافت نشد.';
                break;
            case '-22':
                return 'تراکنش نا موفق می باشد.';
                break;
            case '-33':
                return 'رقم تراکنش با رقم پرداخت شده مطابقت ندارد.';
                break;
            case '-34':
                return 'سقف تقسیم تراکنش از لحاظ تعداد با رقم عبور نموده است.';
                break;
            case '-40':
                return 'اجازه دسترسی به متد مربوطه وجود ندارد.';
                break;
            case '-41':
                return 'اطلاعات ارسال شده مربوط به AdditionalData غیر معتر می باشد.';
                break;
            case '-42':
                return 'مدت زمان معتبر طول عمر شناسه پرداخت بین ۳۰ دقیقه تا ۴۰ روز می باشد.';
                break;
            case '-54':
                return 'درخواست مورد نظر آرشیو شده است.';
                break;
            default:
                return null;
                break;
        }
    }
}