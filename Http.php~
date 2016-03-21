<?php
/**
 * User: bruno
 * Date: 28/01/16
 * Time: 13.12
 */

namespace d4rkstar\arubasms;

use Yii;
use yii\base\Component;
use linslin\yii2\curl\Curl;

class Http extends Component {

    const ARUBA_QUALITY_NOTIFY = 'n';
    const ARUBA_QUALITY_HIGH = 'h';
    const ARUBA_QUALITY_AUTO = 'a';
    const ARUBA_QUALITY_MEDIUM = 'l';
    const ARUBA_QUALITY_LOW = 'll';

    const ARUBA_OPERATION_TEXT = 'TEXT';
    const ARUBA_OPERATION_WAPPUSH = 'WAPPUSH';
    const ARUBA_OPERATION_UCS2 = 'UCS2';
    const ARUBA_OPERATION_MULTITEXT = 'MULTITEXT';
    const ARUBA_OPERATION_MULTIUCS2 = 'MULTIUCS2';

    const ARUBA_CREDIT_TYPE_CREDIT = 'credit';
    const ARUBA_CREDIT_TYPE_NOTIFY = 'n';
    const ARUBA_CREDIT_TYPE_HIGH = 'h';
    const ARUBA_CREDIT_TYPE_AUTO = 'a';
    const ARUBA_CREDIT_TYPE_MEDIUM = 'l';
    const ARUBA_CREDIT_TYPE_LOW = 'll';

    const ARUBA_REPORT_TYPE_QUEUE = 'queue';
    const ARUBA_REPORT_TYPE_NOTIFY = 'notify';

    /** @var string $url HTTP URL for single send */
    public $urlSingle = 'http://admin.sms.aruba.it/sms/send.php';

    /** @var string $url HTTP URL for batch send*/
    public $urlBatch = 'http://admin.sms.aruba.it/sms/batch.php';

    /** @var string $url HTTP URL for send status*/
    public $urlBatchStatus = 'http://admin.sms.aruba.it/sms/batch-status.php';

    /** @var string $url HTTP URL for batch send*/
    public $urlCredit = 'http://admin.sms.aruba.it/sms/credit.php';


    /** @var string $user Client Login */
    public $user = '';

    /** @var string $pass Client password */
    public $pass = '';

    /** @var string $sender Default sender */
    public $sender = '';

    /** @var string Default quality for sent SMSs */
    public $quality = self::ARUBA_QUALITY_AUTO;

    /** @var string creditTypeCheck Default type for credit check */
    public $creditTypeCheck = self::ARUBA_CREDIT_TYPE_CREDIT;

    /** @var string reportType Default report type */
    public $reportType = self::ARUBA_REPORT_TYPE_QUEUE;

    /** @var string matchPattern Pattern to match Aruba's sms response */
    public $matchPattern = '/(OK|KO) (.*)/';

    /**
     * @param array $params [
     *  'rcpt'=>'',         // recipient in international format +XXYYYZZZZZZZ
     *  'data'=>'',         // message body (the lenght depends on operation type)
     *  'sender'=>'',       // message sender (required if no global sender is set)
     *  'qty'=>'',          // quality (see Http::ARUBA_QUALITY_* const),
     *  'operation' => '' , // type of message to send (see Http::ARUBA_OPERATION_* const)
     *  'url' => '',        // URL where the phone should connect to in case of operation WAPPUSH
     * ]
     * @param array $return return data
     * @return boolean
     *
     */
    public function sendSingle($params=[], &$return) {
        $mandatoryParams = ['user','pass', 'rcpt','data','sender','qty'];
        $staticParams = [
            'user'=>$this->user,
            'pass'=>$this->pass,
            'return_id'=>'1'
        ];

        $params = array_merge($params, $staticParams);

        if (!array_key_exists('sender', $params) && !empty($this->sender)) {
            $params['sender']=$this->sender;
        }

        if (!array_key_exists('qty', $params) && !empty($this->quality)) {
            $params['qty']=$this->quality;
        }

        $errors = $this->checkMandatoryParams($params, $mandatoryParams);
        if ($errors!==false) {
            $return = $errors;
            return false;
        }

        $post = http_build_query($params);
        $curl = new Curl();

        $result = $curl->setOption(
            CURLOPT_POSTFIELDS, $post
        )->post($this->urlSingle);

        Yii::info($result);

        if (preg_match($this->matchPattern, $result, $ret)) {
            if (count($ret)==3) {
                $return = [$ret[2]];
                return ($ret[1]=='OK');
            }
        }
        return false;
    }

    /**
     * @param array $params [
     *  'id'=>'' the request_id of the sending
     *  'type'=>'' the type of report (see Http::ARUBA_TYPE_REPORT_* const),
     * ]
     * @param $return array
     * @return bool
     */
    public function checkStatus($params= [], &$return) {
        $mandatoryParams = ['user','pass', 'id','type','schema'];
        $staticParams = [
            'user'=>$this->user,
            'pass'=>$this->pass,
            'schema'=>'1'
        ];

        $params = array_merge($params, $staticParams);

        if (!array_key_exists('type', $params) && !empty($this->reportType)) {
            $params['type']=$this->reportType;
        }


        $errors = $this->checkMandatoryParams($params, $mandatoryParams);
        if ($errors!==false) {
            $return = $errors;
            return false;
        }

        $post = http_build_query($params);
        $curl = new Curl();

        $result = $curl->setOption(
            CURLOPT_POSTFIELDS, $post
        )->post($this->urlBatchStatus);

        Yii::info($result);

        if (substr($result,0,2)=='KO') {
            if (preg_match($this->matchPattern, $result, $ret)) {
                if (count($ret) == 3) {
                    $return = [$ret[2]];
                }
            }
            return false;
        }
        $return = [self::parseStatusMessage($result)];
        return true;
    }

    /**
     * @param array $params [
     *  'type'=>'',         // optional parameter (see Http::ARUBA_CREDIT_TYPE_* const)
     * ]
     * @param array $return return data
     * @return bool
     */
    public function checkCredit($params = [], &$return) {
        $mandatoryParams = ['user','pass'];
        $staticParams = [
            'user'=>$this->user,
            'pass'=>$this->pass,
        ];

        $params = array_merge($params, $staticParams);

        if (!array_key_exists('type', $params) && !empty($this->type)) {
            $params['type']=$this->type;
        }

        $errors = $this->checkMandatoryParams($params, $mandatoryParams);
        if ($errors!==false) {
            $return = $errors;
            return false;
        }

        $post = http_build_query($params);
        $curl = new Curl();

        $result = $curl->setOption(
            CURLOPT_POSTFIELDS, $post
        )->post($this->urlCredit);

        Yii::info($result);

        if (preg_match($this->matchPattern, $result, $ret)) {
            if (count($ret)==3) {
                $return = [$ret[2]];
                return ($ret[1]=='OK');
            }
        }

        return false;
    }

    /**
     * Check if mandatory params are set
     * @param array $params key-based array with parameter=>value to be checked
     * @param array $mandatoryParams key based array of mandatory parameters
     * @param bool $checkEmpty check if all the values are not empty
     * @return array|bool array with errors or false
     */
    private function checkMandatoryParams($params = [], $mandatoryParams = [], $checkEmpty=true) {
        $errors = [];
        foreach($mandatoryParams as $mandatory) {
            if (!array_key_exists($mandatory, $params)) {
                $errors[$mandatory] = Yii::t('app','Paramter {param} is missing',['param'=>$mandatory]);
            } else {
                if ($checkEmpty) {
                    $value = isset($params[$mandatory]) ? $params[$mandatory] : '';
                    if (empty($value) ) {
                        $errors[$mandatory] = Yii::t('app','Paramter {param} is empty',['param'=>$mandatory]);
                    }
                }
            }
        }
        return (count($errors)>0) ? $errors : false;
    }

    /**
     * @param array $status
     * @return SmsStatus[] array of status messages
     */
    private static function parseStatusMessage($statusText) {
        $statuses = [];
        $statusLines = explode("\n", $statusText);
        $isFirst = true;
        foreach($statusLines as $line) {
            if ($isFirst) {
                $isFirst = false;
                continue;
            }
            $data = explode(",", $line);
            if (count($data)==5) {
                $attrs = [
                    'id'=>$data[0],
                    'timestamp'=>$data[1],
                    'dest'=>$data[2],
                    'status'=>$data[3],
                    'status_text'=>$data[4],
                ];
                $status = new SmsStatus();
                $status->attributes=$attrs;
                $statuses[] = $status;
            }
        }
        return $statuses;
    }
}