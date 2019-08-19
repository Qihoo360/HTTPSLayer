<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\GlobalConfig;

/**
 * GlobalConfigSearch represents the model behind the search form about `\app\models\GlobalConfig`.
 */
class GlobalConfigSearch extends GlobalConfig
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['content'], 'safe'],
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
        $query = GlobalConfig::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false, // 禁用sort
            'pagination' => [
                'pageSize' => 80,
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $project_id = !empty($this->project_id) ? $this->project_id : 0;

        $query->andWhere(['project_id' => $project_id]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);
        $query->orderBy([
            "id" => SORT_DESC,
        ]);

        return $dataProvider;
    }
}
