# nfe204 - Catégorisation d'offres du site Shopbot

## Installation

```composer install```

## Configuration

Les paramètres sont situés dans le dossier app/config

## Utilisation

La console est située dans le dossier bin, elle permet de lister toutes les commandes disponibles:

```bin/console```

###### Export des offres depuis Solr

```bin/console solr:export --help```

Exemple:

```bin/console solr:export offer > offers.ndjson```

###### Import des offres dans Elasticsearch

```bin/console es:import --help```

Exemple:

```bin/console es:import offer var/data/offers.ndjson```

###### Classification

Classifie les offres en utilisant le classificateur spécifié. Le log des résultats est enregistré dans var/logs.

```bin/console classify --help```

Exemple:

```bin/console classify -c ft -b 10 -s 100```

###### Evaluation

Cette commande permet de lire un fichier de log pour évaluation les performances du classificateur.

```bin/console metric --help```

Exemple:

```bin/console metric -r var/logs/classification.log```
