<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Certificate;

/**
 * CertificateSearch represents the model behind the search form about `app\models\Certificate`.
 * @property string $host  域名
 * @property string $project_name  业务名
 * @property integer $remain_day  即将在X天之内到期
 * @property string $create_time_start
 * @property string $create_time_end
 */
class CertificateSearch extends Certificate
{
    /**
     * @var $host ;
     */
    public $host;

    /**
     * @var $project_name string 业务名
     */
    public $project_name;

    public $remain_day;

    public $create_time_start;

    public $create_time_end;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'priority', 'remain_day'], 'integer'],
            [['priv_key', 'pub_key', 'serial_no', 'subject', 'algorithm', 'issuer', 'valid_start_time', 'valid_end_time', 'contact_email', 'host', 'name'], 'safe'],
            [['host', 'project_name', 'create_time_start', 'create_time_end'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            'host' => '域名',
            'remain_day' => '证书剩余天数',
            'create_time_start' => '证书添加日期最早',
            'create_time_end' => '证书添加日期最晚',
            'project_name' => '业务名',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $tbl_certificate = Certificate::tableName();
        $tbl_cert_host = CertHost::tableName();
        $tbl_rel_proj_cert = RelPorjCert::tableName();
        $tbl_project = Project::tableName();

        $query = CertificateSearch::find();

        // add conditions that should always apply here

        $query->leftJoin($tbl_cert_host, "{$tbl_cert_host}.certificate_id = {$tbl_certificate} .id ");
        $query->leftJoin($tbl_rel_proj_cert, "{$tbl_rel_proj_cert}.certificate_id = {$tbl_certificate} . id");
        $query->leftJoin($tbl_project, "{$tbl_project}.id = {$tbl_rel_proj_cert}.project_id");

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        if (!empty($this->create_time_start)) {
            $query->andWhere(['>=', "{$tbl_certificate}.create_time", $this->create_time_start . " 00:00:00"]);
        }

        if (!empty($this->create_time_end)) {
            $query->andWhere(['<=', "{$tbl_certificate}.create_time", $this->create_time_end . " 23:59:59"]);
        }

        if (!empty($this->remain_day) && $this->remain_day > 0) {
            $current_time = date("Y-m-d H:i:s");
            $off_date = date("Y-m-d H:i:s", (time() + intval($this->remain_day) * 86400));
            $query->andWhere(['<=', "{$tbl_certificate}.valid_end_time", $off_date]);
            $query->andWhere(['>=', "{$tbl_certificate}.valid_end_time", $current_time]);

        }

        // grid filtering conditions
        $query->andFilterWhere([
            "{$tbl_certificate}.id" => $this->id,
            "{$tbl_certificate}.status" => $this->status,
            "{$tbl_certificate}.priority" => $this->priority,
        ]);

        $query->andFilterWhere(['like', "{$tbl_certificate}.priv_key", $this->priv_key])
            ->andFilterWhere(['like', "{$tbl_certificate}.pub_key", $this->pub_key])
            ->andFilterWhere(["like", "{$tbl_certificate}.serial_no", $this->serial_no])
            ->andFilterWhere(["like", "{$tbl_certificate}.subject", $this->subject])
            ->andFilterWhere(["like", "{$tbl_certificate}.name", $this->name])
            ->andFilterWhere(["like", "{$tbl_certificate}.issuer", $this->issuer])
            ->andFilterWhere(["like", "{$tbl_certificate}.contact_email", $this->contact_email])
            ->andFilterWhere(["like", "{$tbl_project}.name", $this->project_name]);

        if (!empty($this->host)) {
            /**
             * @var $all_cert_hosts CertHost[]
             */
            $all_cert_hosts = CertHost::find()->all();
            $cert_host_ids = [];
            if (!empty($all_cert_hosts)) {
                foreach ($all_cert_hosts as $_cert_host) {
                    if (\Utils::hostMatch($_cert_host->name, $this->host)) {
                        $cert_host_ids[] = $_cert_host->id;
                    }
                }
            }
            if (!empty($cert_host_ids)){
                $query->andFilterWhere(["in", "{$tbl_cert_host}.id", $cert_host_ids]);
            }
        }

        $query->groupBy([
            "{$tbl_certificate}.id"
        ]);

        return $dataProvider;
    }
}
