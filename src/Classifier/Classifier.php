<?php

namespace Nfe204\Classifier;

class Classifier extends AbstractClassifier implements ClassifierInterface
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
                            'match' => [
                                'offer_name' => $offer['offer_name']
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

        return $offer['category_id'] === $result['hits']['hits'][0]['_source']['category_id'];
    }
}