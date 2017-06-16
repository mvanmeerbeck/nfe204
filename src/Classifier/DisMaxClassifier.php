<?php

namespace Nfe204\Classifier;

class MoreLikeThisClassifier extends AbstractClassifier implements ClassifierInterface
{
    public function predict(array $offer)
    {
        $result = $this->client->search([
            'index' => 'offer',
            'size' => 1,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'more_like_this' => [
                                'fields' => ['offer_name'],
                                'like' => ['_id' => $offer['offer_id']],
                                'min_term_freq' => 1,
                            ]
                        ],
                        'must_not' => [
                            'term' => ['shop_id' => $offer['shop_id']]
                        ],
                        'filter' => [
                            'exists' => ['field' => 'category_id']
                        ]
                    ]
                ]

            ]
        ]);

        if ($result['hits']['total'] > 0) {
            $this->addPrediction($offer['category_id'], $result['hits']['hits'][0]['_source']['category_id']);
        } else {
            $this->addPrediction($offer['category_id'], 0);
        }
    }
}