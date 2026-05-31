<?php

namespace PaymentGateways;

/**
 * Base Payment Gateway Class
 */
abstract class PaymentGateway {
    protected $apiKey;
    protected $apiSecret;
    protected $isProduction;
    protected $webhookUrl;

    public function __construct($isProduction = false) {
        $this->isProduction = $isProduction;
    }

    abstract public function initiatePayment($planId, $userId, $amount, $paymentDetails);
    abstract public function verifyPayment($transactionId);
    abstract public function generateCheckoutUrl($paymentData);

    protected function logTransaction($planId, $transactionId, $status, $response) {
        $logFile = __DIR__ . '/../../logs/payments.log';
        $logMessage = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'plan_id' => $planId,
            'transaction_id' => $transactionId,
            'status' => $status,
            'response' => $response
        ]) . "\n";

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    protected function sanitizeData($data) {
        return array_map(function($value) {
            return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
        }, $data);
    }
}

/**
 * GCash Payment Gateway
 */
class GCashGateway extends PaymentGateway {
    private $gcashApiUrl = 'https://api.gcash.com/v1';
    private $merchantId;

    public function __construct($merchantId, $isProduction = false) {
        parent::__construct($isProduction);
        $this->merchantId = $merchantId;
    }

    public function initiatePayment($planId, $userId, $amount, $paymentDetails) {
        // Extract GCash specific data
        $mobileNumber = $paymentDetails['mobile_number'] ?? null;
        
        if (!$mobileNumber) {
            return [
                'success' => false,
                'error' => 'Mobile number is required for GCash payment'
            ];
        }

        $payload = [
            'merchant_id' => $this->merchantId,
            'reference_number' => 'SINTA-' . $planId . '-' . time(),
            'amount' => number_format($amount, 2),
            'currency' => 'PHP',
            'mobile_number' => $mobileNumber,
            'description' => "Event Booking Payment - Plan ID: $planId",
            'callback_url' => getenv('APP_URL') . '/public/api-payment.php?action=gcash_callback',
            'redirect_url' => getenv('APP_URL') . '/public/api-payment.php?action=gcash_return&plan_id=' . $planId
        ];

        // In production, call actual GCash API
        if ($this->isProduction) {
            $response = $this->callGCashAPI('/payment/initiate', $payload);
        } else {
            // Simulate API response for testing
            $response = [
                'success' => true,
                'transaction_id' => 'GCASH-' . time(),
                'checkout_url' => 'https://app.gcash.com/payment/mock?ref=' . $payload['reference_number'],
                'reference_number' => $payload['reference_number']
            ];
        }

        $this->logTransaction($planId, $response['transaction_id'] ?? 'pending', 'initiated', $response);

        return $response;
    }

    public function verifyPayment($transactionId) {
        // Call GCash API to verify payment status
        $response = $this->callGCashAPI('/payment/verify', [
            'transaction_id' => $transactionId
        ]);

        $this->logTransaction(null, $transactionId, 'verified', $response);

        return $response;
    }

    public function generateCheckoutUrl($paymentData) {
        return "https://app.gcash.com/payment/checkout?ref=" . $paymentData['reference_number'];
    }

    private function callGCashAPI($endpoint, $data) {
        // Implementation for actual GCash API calls
        // This is a placeholder for the actual implementation
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->gcashApiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return json_decode($response, true);
    }
}

/**
 * PayMaya Payment Gateway
 */
class PayMayaGateway extends PaymentGateway {
    private $paymayaApiUrl = 'https://api.paymaya.com/v1';
    private $publicKey;
    private $secretKey;

    public function __construct($publicKey, $secretKey, $isProduction = false) {
        parent::__construct($isProduction);
        $this->publicKey = $publicKey;
        $this->secretKey = $secretKey;
    }

    public function initiatePayment($planId, $userId, $amount, $paymentDetails) {
        $mobileNumber = $paymentDetails['mobile_number'] ?? null;

        if (!$mobileNumber) {
            return [
                'success' => false,
                'error' => 'Mobile number is required for PayMaya payment'
            ];
        }

        $payload = [
            'requestReferenceNumber' => 'SINTA-' . $planId . '-' . time(),
            'amount' => [
                'value' => $amount,
                'currency' => 'PHP'
            ],
            'buyer' => [
                'firstName' => $paymentDetails['first_name'] ?? '',
                'lastName' => $paymentDetails['last_name'] ?? '',
                'phone' => $mobileNumber
            ],
            'totalAmount' => [
                'value' => $amount,
                'currency' => 'PHP'
            ],
            'description' => "Event Booking Payment - Plan ID: $planId",
            'redirectUrl' => [
                'success' => getenv('APP_URL') . '/public/api-payment.php?action=paymaya_return&plan_id=' . $planId,
                'failure' => getenv('APP_URL') . '/public/api-payment.php?action=paymaya_failure&plan_id=' . $planId,
                'cancel' => getenv('APP_URL') . '/public/api-payment.php?action=paymaya_cancel&plan_id=' . $planId
            ]
        ];

        if ($this->isProduction) {
            $response = $this->callPayMayaAPI('/checkout/v1/readyToPayCheckout', $payload);
        } else {
            // Mock response
            $response = [
                'success' => true,
                'checkoutId' => 'PM-' . time(),
                'redirectUrl' => 'https://staging-api.paymaya.com/checkout?id=mock-' . $payload['requestReferenceNumber'],
                'requestReferenceNumber' => $payload['requestReferenceNumber']
            ];
        }

        $this->logTransaction($planId, $response['checkoutId'] ?? 'pending', 'initiated', $response);

        return $response;
    }

    public function verifyPayment($transactionId) {
        $response = $this->callPayMayaAPI('/checkout/v1/readyToPayCheckout/' . $transactionId, [], 'GET');
        $this->logTransaction(null, $transactionId, 'verified', $response);
        return $response;
    }

    public function generateCheckoutUrl($paymentData) {
        return $paymentData['redirectUrl'] ?? null;
    }

    private function callPayMayaAPI($endpoint, $data = [], $method = 'POST') {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $this->paymayaApiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->publicKey . ':' . $this->secretKey)
            ],
            CURLOPT_TIMEOUT => 30
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        } else {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return json_decode($response, true);
    }
}

/**
 * Bank Transfer Gateway
 */
class BankTransferGateway extends PaymentGateway {
    
    public function initiatePayment($planId, $userId, $amount, $paymentDetails) {
        $bankName = $paymentDetails['bank_name'] ?? null;
        $accountNumber = $paymentDetails['account_number'] ?? null;
        $accountHolder = $paymentDetails['account_holder'] ?? null;

        if (!$bankName || !$accountNumber || !$accountHolder) {
            return [
                'success' => false,
                'error' => 'Bank details are required'
            ];
        }

        $referenceNumber = 'BANK-' . $planId . '-' . strtoupper(substr(md5(time()), 0, 8));

        $response = [
            'success' => true,
            'transaction_id' => $referenceNumber,
            'reference_number' => $referenceNumber,
            'bank_details' => [
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_holder' => $accountHolder
            ],
            'amount' => $amount,
            'instruction' => "Please transfer ₱" . number_format($amount, 2) . " to the account above with reference number: $referenceNumber"
        ];

        $this->logTransaction($planId, $referenceNumber, 'initiated', $response);

        return $response;
    }

    public function verifyPayment($transactionId) {
        // For bank transfers, verification is manual by admin
        return [
            'success' => true,
            'status' => 'pending_verification',
            'message' => 'Bank transfer payment is pending manual verification by admin'
        ];
    }

    public function generateCheckoutUrl($paymentData) {
        return null; // Bank transfer doesn't have a checkout URL
    }
}

/**
 * ATM Card Payment Gateway
 */
class ATMCardGateway extends PaymentGateway {
    private $atmApiUrl = 'https://api.bancnet.com.ph/v1';
    private $merchantId;

    public function __construct($merchantId, $isProduction = false) {
        parent::__construct($isProduction);
        $this->merchantId = $merchantId;
    }

    public function initiatePayment($planId, $userId, $amount, $paymentDetails) {
        $referenceNumber = 'ATM-' . $planId . '-' . time();

        $payload = [
            'merchant_id' => $this->merchantId,
            'reference_number' => $referenceNumber,
            'amount' => number_format($amount, 2),
            'currency' => 'PHP',
            'description' => "Event Booking Payment - Plan ID: $planId",
            'callback_url' => getenv('APP_URL') . '/public/api-payment.php?action=atm_callback',
            'redirect_url' => getenv('APP_URL') . '/public/api-payment.php?action=atm_return&plan_id=' . $planId
        ];

        if ($this->isProduction) {
            $response = $this->callATMAPI('/payment/initiate', $payload);
        } else {
            $response = [
                'success' => true,
                'transaction_id' => $referenceNumber,
                'checkout_url' => 'https://atm.bancnet.com.ph/payment/mock?ref=' . $referenceNumber,
                'reference_number' => $referenceNumber
            ];
        }

        $this->logTransaction($planId, $response['transaction_id'] ?? 'pending', 'initiated', $response);

        return $response;
    }

    public function verifyPayment($transactionId) {
        $response = $this->callATMAPI('/payment/verify', [
            'transaction_id' => $transactionId
        ]);

        $this->logTransaction(null, $transactionId, 'verified', $response);

        return $response;
    }

    public function generateCheckoutUrl($paymentData) {
        return $paymentData['checkout_url'] ?? null;
    }

    private function callATMAPI($endpoint, $data) {
        // Placeholder for actual ATM API implementation
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->atmApiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}

?>
