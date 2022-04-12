<?php

class GlicoConfig
{

    const INFO = "/var/log/applications/gh/Glico/Glicoinfo.log";

/**
 * File location for fatal logs.
 */
    const FATAL = "/var/log/applications/gh/Glico/Glicofatal.log";

/**
 * File location for error logs.
 */
    const ERROR = "/var/log/applications/gh/Glico/Glicoerror.log";

/**
 * File location for debug logs.
 */
    const DEBUG = "/var/log/applications/gh/Glico/Glicodebug.log";

    const currencyCode = "GHS";

    const CHARGE_RATE = "0%";

    const CHARGE_RATE_VALUE = 0;

    const REQUEST_ORIGIN = "MULA_USSD";

    const DESTINATION_CLIENT = "GLICO";

    const MOMOAPI_RPC_FUNCTION = "MOMO.logChannelRequest";

    const MOMOAPI_SUCCESS = 200;

    const USSD_PUSH = "USSD_PUSH";

    const STK_LAUNCH = "STK_LAUNCH";

    const ACCESS_POINT = "*361*502#";

    const CHECKOUT_SERVICE_CODE = "RINV";

    const CHECKOUT_ASYNC_MODE = "ASYNC";

    const MULA_PROXY_CLIENT_ID = 106;

    const BUY_POLICY_SERVICE_ID = "2069";

    const BUY_POLICY_SERVICE_CODE = "GLICOPOLICY";

    const PAY_PREMIUM_SERVICE_ID = "2070";

    const PAY_PREMIUM_SERVICE_CODE = "GLICOPREMIUM";

/**
 * MTN MoMo API Configs
 */
    const MTNGH_CHECKOUT_API_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";

    const MTNMOMOAPI_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";
    const MTN = "MTN";
    const MTNCLIENT_ID = 20;

/**
 * AIRTEL MoMo API Configs
 */
    const AIRTELGH_CHECKOUT_API_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";

    const AIRTELMOMOAPI_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";
    const AIRTELCLIENT_ID = 4;
    const AIRTEL = "AIRTEL";

/**
 * TIGO MoMo API Configs
 */
    const TIGOGH_CHECKOUT_API_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";

    const TIGOMOMOAPI_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";
    const TIGO = "TIGO";
    const TIGOCLIENT_ID = 5;

/**
 * VODAFONE MoMo API Configs
 */
    const VODAFONEGH_CHECKOUT_API_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";

    const VODAFONEMOMOAPI_URL = "http://internal-cpg-core-lb-62008288.eu-west-1.elb.amazonaws.com:9007/hub/api/mula/";
    const VODAFONE = "VODAFONE";
    const VODAFONECLIENT_ID = 7;

    private static $mobileMoneyOptions = array("MTN Mobile Money", "TIGO Cash", "AIRTEL Money", "VODAFONE Cash");

    public static function getMobileMoneyOptions()
    {
        return self::$mobileMoneyOptions;
    }

    
    const Authentication_API_URL = "http://41.139.129.229:97/ussd_api/public/authenticate";

    const Validate_Policy_API_URL = "http://41.139.129.229:97/ussd_api/public/api/glico/validatePolicy";

    const Pay_Policy_API_URL = "http://41.139.129.229:97/ussd_api/public/api/glico/PayPolicy";

    const Buy_Policy_API_URL = "http://41.139.129.229:97/ussd_api/public/api/glico/BuyPolicy";

    const Packages_API_URL = "http://41.139.129.229:97/ussd_api/public/api/glico/getPolicyPackages";

    const Customer_Details_API_URL = "http://41.139.129.229:97/ussd_api/public/api/glico/getCustomerDet";

    const Registration_API_URL ="http://41.139.129.229:97/ussd_api/public/api/glico/clientReg";

    const Payload = array('client' => 'ussd_client', 'secret_key' => 'asfn349nasd8sdf9');

    const Head = array(
        "Content-Type: application/x-www-form-urlencoded",
        "Cookie: XSRF-TOKEN=eyJpdiI6InkrQTlrcjkydUN6blB6THhndGtkQmc9PSIsInZhbHVlIjoiT3ZPdktFRVRrOGJaNDhCb29YeURLa0dGRE4yXC9tNlpMZHNrOEJGYnQ4c1hRUFVIRm5hNzBFcjIxYUV0S1ZWTkoiLCJtYWMiOiI2MjlkODk0MzE0NjNhMjFlZjhkMzJiOWE5ZGQxOTM3YzA3M2I0Zjg4OTcwOGJlYTNjOWYyZTNhZjM4OTk3YmJkIn0%3D; laravel_session=eyJpdiI6InpZbkpMQURmdHRVUFlpeWtqNGdIZkE9PSIsInZhbHVlIjoiT0EzUGNOZFBESVphZmZsWnNrQ2IxSmQrSVhxUDBKdWM2MVJtRzRYczFZa01KWEd3QXYxYjFmbUhYUzJjOHlrXC8iLCJtYWMiOiJkNjQ2ODNmOTZhNTMzOGY0YzYwYTg2ZDc4M2UyODM3OTYzYjY1ZTE3OTA2YjRmZGY2ZGNjNmM5MTM2MGUyYjBmIn0%3D",
    );
}
