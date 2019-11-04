<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "certificate".
 *
 * @property integer $id
 * @property string $priv_key
 * @property string $pub_key
 * @property integer $status
 * @property string $serial_no
 * @property string $subject
 * @property string $name
 * @property integer $priority
 * @property string $algorithm
 * @property string $issuer
 * @property string $valid_start_time
 * @property string $valid_end_time
 * @property string $contact_email
 * @property string $priv_content
 * @property string $pub_content
 * @property CertHost[] $certHosts
 * @property RelPorjCert[] $relProjCerts
 */
class Certificate extends BaseActiveRecord
{
    const DOWNLOAD_PRIV = 1;

    const DOWNLOAD_PUB = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'certificate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'priority'], 'integer'],
            [['priority', 'valid_start_time', 'valid_end_time'], 'required'],
            [['valid_start_time', 'valid_end_time', 'create_time', 'update_time', 'priv_content', 'pub_content'], 'safe'],
            [['priv_key', 'pub_key'], 'string', 'max' => 50],
            [['serial_no', 'subject'], 'string', 'max' => 300],
            [['algorithm', 'name'], 'string', 'max' => 50],
            [['issuer'], 'string', 'max' => 200],
            [['contact_email'], 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'priv_key' => '私钥KEY',
            'pub_key' => '公钥KEY',
            'status' => '状态',
            'serial_no' => '序列号',
            'subject' => 'Subject名称',
            'priority' => '优先级',
            'algorithm' => '签名算法',
            'issuer' => '颁发者',
            'valid_start_time' => '证书开始时间',
            'valid_end_time' => '证书结束时间',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'contact_email' => '联系人邮箱',
            'name' => '名称',
        ];
    }

    public function getCertHosts()
    {
        return $this->hasMany(CertHost::className(), ['certificate_id' => 'id']);
    }

    public function getRelProjCerts()
    {
        return $this->hasMany(RelPorjCert::className(), ['certificate_id' => 'id']);
    }

    public function initParsePubContent()
    {
        $info = $this->parsePubContent();
        if (!empty($info)) {
            $subject = !empty($info['subject']) ? $info['subject'] : [];
            $this->subject = "";
            foreach ($subject as $_k => $_v) {
                if (is_string($_v)) {
                    $this->subject .= sprintf("/%s=%s", $_k, $_v);
                } else if (is_array($_v)) {
                    $this->subject .= sprintf("/%s=%s", $_k, json_encode($_v));
                }
            }

            $issuer = !empty($info['issuer']) ? $info['issuer'] : [];
            $this->issuer = "";
            foreach ($issuer as $_k => $_v) {
                $this->issuer .= "/{$_k}={$_v}";
            }

            $this->serial_no = !empty($info['serialNumber']) ? $info['serialNumber'] : "";
            $this->algorithm = !empty($info['signatureTypeSN']) ? $info['signatureTypeSN'] : "";
            if (!empty($info['validFrom_time_t'])) {
                $this->valid_start_time = date("Y-m-d H:i:s", $info['validFrom_time_t']);
            }
            if (!empty($info['validTo_time_t'])) {
                $this->valid_end_time = date("Y-m-d H:i:s", $info['validTo_time_t']);
            }
        }
        return true;
    }

    public function parsePubContent()
    {
        $info = openssl_x509_parse($this->pub_content);
        return $info;
    }

    /**
     * 计算当前对象证书文件在缓存中的key
     * @return array
     */
    public function fileCacheKey()
    {
        $result = [
            "pub" => null,
            "priv" => null,
        ];

        $ts = time();
        $result['pub'] = "pub_" . md5($this->serial_no) . "_{$ts}";
        $result['priv'] = "priv_" . md5($this->serial_no) . "_{$ts}";

        return $result;
    }

    public static function validAsDict($status = null)
    {
        $query = self::find();

        if ($status !== null) {
            $query->where(["status" => $status]);
        }

        $certificates = $query->all();
        $dict = ArrayHelper::map($certificates, 'id', 'name');
        return $dict;
    }

}
