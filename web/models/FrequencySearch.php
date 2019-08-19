<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Frequency;

/**
 * FrequencySearch represents the model behind the search form about `app\models\Frequency`.
 */
class FrequencySearch extends Frequency
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'according', 'handle_way', 'status'], 'integer'],
            [['path', 'description', 'method', 'cookie_name', 'time_window', 'referer', 'arguments', 'white_ip', 'black_ip', 'create_time', 'update_time', 'create_user', 'update_user', 'update_operation'], 'safe'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Frequency::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'project_id' => $this->project_id,
            'handle_way' => $this->handle_way,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'method', $this->method]);

        $query->andFilterWhere(['!=', 'status', self::STATUS_DELETED]);

        $query->orderBy("id desc");

        return $dataProvider;
    }
}
