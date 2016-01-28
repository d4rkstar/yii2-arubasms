<?php
/**
 * User: bruno
 * Date: 28/01/16
 * Time: 13.12
 */

namespace d4rkstar\arubasms;

use Yii;
use yii\base\Component;

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



    /** @var string $url HTTP URL for single send */
    public $urlSingle = 'http://admin.sms.aruba.it/sms/send.php';

    /** @var string $url HTTP URL for batch send*/
    public $urlBatch = 'http://admin.sms.aruba.it/sms/batch.php';

    /** @var string $url HTTP URL for send status*/
    public $urlBatchStatus = 'http://admin.sms.aruba.it/sms/batch-status.php';

    /** @var string $url HTTP URL for batch send*/
    public $urlCredit = 'http://admin.sms.aruba.it/sms/credit.phpp';


    /** @var string $user Client Login */
    public $user = '';

    /** @var string $pass Client password */
    public $pass = '';

    public $sender = '';

    public $quality = self::ARUBA_QUALITY_AUTO;


    public function sendSingle() {

    }

    public function checkCredit() {

    }


}