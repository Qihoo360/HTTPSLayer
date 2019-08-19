<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/27
 * Time: 19:00
 */

namespace app\models;

/**
 * Class CertificateForm
 * @package app\models
 * @property  $host_names
 */
class CertificateForm extends Certificate
{
    public $host_names;

    public function rules()
    {
        return parent::rules() + [
            [['host_names',], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            'host_names' => '域名信息',
        ];
    }


    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!empty($this->pub_content)) {
                $info = $this->parsePubContent();
                $this->host_names = [];
                if (!empty($info['subject']['CN'])) {
                    $this->host_names[] = trim($info['subject']['CN']);
                }
                if (!empty($info['extensions']['subjectAltName'])) {
                    $str = $info['extensions']['subjectAltName'];
                    $_info_1 = explode(",", $str);
                    foreach ($_info_1 as $_str2) {
                        $item = trim($_str2);
                        if (strpos($item, "DNS:") === 0) {
                            $_host = str_replace("DNS:", "", $item);
                            $_host = trim($_host);
                            if (!empty($_host) && !in_array($_host, $this->host_names)) {
                                $this->host_names[] = $_host;
                            }
                        }
                    }
                }

            }


            $save_ok = parent::save();

            if ($save_ok) {
                $exist_hosts = [];
                if (!empty($this->certHosts)) {
                    foreach ($this->certHosts as $_host) {
                        $exist_hosts[] = $_host->name;
                    }
                }

                $current_hosts = !empty($this->host_names) ? $this->host_names : [];

                $add_hosts = array_diff($current_hosts, $exist_hosts);

                $del_hosts = array_diff($exist_hosts, $current_hosts);

                if (!empty($add_hosts)) {
                    foreach ($add_hosts as $host_name) {
                        $cert_host = new CertHost();
                        $cert_host->name = $host_name;
                        $cert_host->certificate_id = $this->id;
                        $cert_host->save();
                    }
                }
                if (!empty($del_hosts)) {
                    foreach ($del_hosts as $host_name) {
                        CertHost::deleteAll(['certificate_id' => $this->id, 'name' => $host_name]);
                    }
                }

            }
            $transaction->commit();
            return $save_ok;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }

    }

}