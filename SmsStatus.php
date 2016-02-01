<?php
/**
 * User: bruno
 * Date: 01/02/16
 * Time: 10.41
 */

namespace d4rkstar\arubasms;

use Yii;
use yii\base\Model;

class SmsStatus extends Model {

    public $id;
    public $timestamp;
    public $dest;
    public $status;
    public $status_text;

    public function rules()
    {
        return [
            [['id', 'timestamp','dest','status','status_text'], 'safe'],
        ];
    }

    public function attributes()
    {
        return [
            'id',
            'timestamp',
            'dest',
            'status',
            'status_text',
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'=>Yii::t('app','Id'),
            'timestamp'=>Yii::t('app','Time stamp'),
            'dest'=>Yii::t('app','Destination'),
            'status' =>Yii::t('app','Status'),
            'status_text'=>Yii::t('app','Status Text'),
        ];
    }

}