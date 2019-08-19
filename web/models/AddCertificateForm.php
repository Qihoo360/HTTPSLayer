<?php
/**
 * Created by PhpStorm.
 * User: zhangshuang
 * Date: 17/10/30
 * Time: 17:07
 */

namespace app\models;


use yii\base\Model;

/**
 * Class AddCertificateForm
 * @package app\models
 * @property  integer[] $certificate_ids
 */
class AddCertificateForm extends Project
{
    public $certificate_ids;

    public function rules()
    {
        return parent::rules() + [
                [['certificate_ids',], 'safe'],
            ];
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'certificate_ids' => '证书列表',
            ];
    }


    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!empty($this->id)) {
                /**
                 * @var $existed_relation integer[] 已经存在的
                 */
                $existed_relation = [];
                foreach ($this->relProjCerts as $relPorjCert) {
                    if (!in_array($relPorjCert->certificate_id, $this->certificate_ids)) { // 库里面不在更新列表里的关系删除
//                        RelPorjCert::deleteAll(["project_id" => $this->id, "certificate_id" => $relPorjCert->certificate_id]);
                        RelPorjCert::updateAll(["status" => \Constant::INVALID], ["project_id" => $this->id, "certificate_id" => $relPorjCert->certificate_id]); //  标记删除
                    } else { // 库里面存在在更新列表的保留
                        $existed_relation[] = $relPorjCert->certificate_id;
                    }
                }
                // 计算本次需要添加的= 在本次更新列表中,但不在库中
                $add_relation = array_diff($this->certificate_ids, $existed_relation);
                foreach ($add_relation as $_certificate_id) {
                    $_model = RelPorjCert::findOne(["project_id" => $this->id, "certificate_id" => $_certificate_id]);
                    $rel_proj_cert = $_model? $_model : new RelPorjCert();
                    $rel_proj_cert->certificate_id = $_certificate_id;
                    $rel_proj_cert->project_id = $this->id;
                    $rel_proj_cert->status = \Constant::VALID;
                    $rel_proj_cert->save();
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }

    }

}