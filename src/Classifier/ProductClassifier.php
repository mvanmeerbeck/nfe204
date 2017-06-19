<?php

namespace Nfe204\Classifier;

class ProductClassifier extends AbstractClassifier implements ClassifierInterface
{
    public function predict(array $offer)
    {
        $result = $this->client->search([
            'index' => 'document',
            'size' => 1,
            'type' => 'product',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'product_name' => $offer['offer_name']
                            ]
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
