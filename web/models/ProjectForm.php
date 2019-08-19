<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/30
 * Time: 17:49
 */

namespace app\models;

/**
 * Class ProjectForm
 * @package app\models
 * @property  $host_names
 */
class ProjectForm extends Project
{
    public $host_names;

    const QFE_CAPTCHE_PREFIX = 'qcaptcha_so_com_';

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
            $save_ok = parent::save();
            if ($save_ok) {
                $exist_hosts = [];
                if (!empty($this->projHosts)) {
                    foreach ($this->projHosts as $_host) {
                        $exist_hosts[] = $_host->name;
                    }
                }
                $exist_hosts = array_unique($exist_hosts);

                $current_hosts = !empty($this->host_names) ? $this->host_names : [];
                foreach($current_hosts as $k => $v) {
                    $host_info = parse_url($v);
                    $host = (isset($host_info['scheme']) && isset($host_info['host'])) ? $host_info['host'] : '';
                    $host = (empty($host) && isset($host_info['path'])) ? $host_info['path'] : $host;
                    $c_hosts[] = $host;
                }
                $current_hosts = array_unique($c_hosts);

                $add_hosts = array_diff($current_hosts, $exist_hosts);
                $del_hosts = array_diff($exist_hosts, $current_hosts);

                // 先删除
                if (!empty($del_hosts)) {
                    foreach ($del_hosts as $host_name) {
                        ProjHost::deleteAll(['project_id' => $this->id, 'name' => $host_name]);
                    }
                }

                // 后添加
                if (!empty($add_hosts)) {
                    foreach ($add_hosts as $host_name) {
                        $proj_host = new ProjHost();
                        /**
                         * @var ProjectHost $exist
                         */
                        $exist = ProjHost::find()->where(["name" =>$host_name ])->one();
                        if ($exist) {
                            $proj_id = $exist->project_id;
                            /**
                             * @var Project $proj
                             */
                            $proj = Project::find()->where(["id" => $proj_id])->one();
                            $proj_name = $proj->name;
                            $this->addError('host_names', "Host: {$host}与项目`{$proj_name}`域名重复");
                            return false;
                        }

                        $proj_host->name = \Utils::xssStrip($host_name);
                        $proj_host->project_id = $this->id;
                        $proj_host->save();

                        // 写入redis，提供qfe验证码
                        $rkey = self::QFE_CAPTCHE_PREFIX. $host_name;
                        $crypt = [
                            'encrypt_salt' => md5("$#($^#%@{$host_name}^&*D!@#". time()),
                            'decrypt_salt' => md5("(#&$^$&!{$host_name}*#@¥*:}". time()),
                        ];
                        RedisQfe::getInstance()->set(RedisQfe::DB_FREQUENCYCONFIG_FILE, $rkey, json_encode($crypt));

                    }
                }

            }
            $transaction->commit();
            return $save_ok;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            $transaction->rollBack();
            return false;
        }

    }
}