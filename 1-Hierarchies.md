# Entités hiérarchisées avec Doctrine

## Introduction : les généralisations

Il est tout à fait possible de répliquer avec Doctrine des hiérarchies d'entités comme on le fait régulièrement dans la programmation objet.

Créer une hiérarchie n'est pas possible via une commande de Symfony. Le processus nécessite donc de modifier les classes d'entités manuellement une fois celles-ci créées. 

Il existe deux manières d'implémenter une hiérarchie d'entités :
1. Modéliser uniquement les sous-classes en laissant la superclasse être une ressource « technique ».
2. Modéliser la hiérarchie entière, en considérant _toutes_ les classes comme des entités.

:nerd_face: Pour ce support de cours, nous utiliserons le terme de superclasse et de sous-classe, synonyme de classe Mère et de classe Fille, ou encore classe Parent et classe Enfant.

## Superclasses de modèle

Une superclasse de modèle est une classe (abstraite ou concrète) qui permet de mutualiser des propriétés et méthodes pour un certain nombre de sous-classes. La caractéristique d'une superclasse de modèle est qu'elle **n'est pas une entité elle-même**. Cela peut introduire des limitations par la suite lors des requêtes, car cette classe n'est pas « _requêtable_ », naturellement.

D'autres limitations sont associées à ces classes, notamment du point de vue des associations entre entités. Les superclasses de modèle ne peuvent être impliquées que dans des associations **unidirectionnelles**, ce qui implique que la classe est le versant _propriétaire_ de l'association.

- Une conséquence de cela est qu'il est impossible de définir une association `OneToMany` qui partirait de la superclasse de modèle.
- Une autre conséquence est que les associations `ManyToMany` ne sont possibles que dans certains cas de figure précis (pas d'association entre deux sous-classes, par exemple).

Une superclasse de modèle est définie exactement comme toute autre classe ; elle est juste étiquetée avec l'attribut `MappedSuperClass`.

```php
#[MappedSuperclass]
class Client
{
    #[Column(type="integer")]
    protected $client_id;

    #[Column(type="string")]
    protected $lastName;

    #[OneToOne(targetEntity="Cart")]
    protected $cart;

    // ... etc.
}
```
La classe ci-dessus permet de définir plusieurs classes de clients qui partageraient toutes une association `OneToOne` avec un panier.

Bien qu'elles puissent répondre à un certain nombre de cas d'utilisation, les superclasses de modèle sont souvent trop limitées. On ne pourrait par exemple pas représenter la notion d'emprunt dans une bibliothèque où les livres sont typiquement empruntés plusieurs fois (donc`OneToMany`).

La solution générale sera donc plutôt d'utiliser une autre technique que nous appellerons « héritage de table », faute de dénomination facilement traduisible (« _Inheritance Mapping_ ») de la part de Doctrine. 

## Héritage de table

La définition l'héritage de table se fait, comme dans le cas précédent grâce aux attributs. De la même manière, il existe une classe-mère et des classes-filles.

Admettons que nous voulions créer des `Document` qui seraient soit des `Livre`, soit des `DVD`, soit des `Journal`, etc.

Dans un premier temps, nous devrons créer les entités (on supposera qu'il existe un diagramme de classes UML qui les spécifie). Conformément à l'héritage de la POO, les entités ne contiennent que les propriétés qui leur sont spécifiques.
1. Dans la classe-mère (ici `Document`), il faudra naturellement changer l'accessibilité de propriétés, qui sont `private` par défaut lorsque l'on utilise `make:entity`.
2. Dans les classes-filles, on peut supprimer toute référence à `id`, qui sera héritée. Même si ceci n'est pas indispensable, cela rend le code plus clair.

Maintenant vient la déclaration d'un lien hiérarchique, ce qui se fait dans la classe-mère :
```php
// Import de la classe définissant les annotations
use Doctrine\ORM\Mapping as ORM;

#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name:"documentType", type:"string")]
#[ORM\DiscriminatorMap(["DOO" => Document::class, "L01" => Book::class, "D02" => Book::class, "J03" = News::class])]
class Document {
  /* ... */
}
```
- L'attribut `DiscriminatorColumn` indique qu'une colonne sera ajoutée dans la table SQL pour dire quel est le type de document de l'enregistrement considéré ;
- L'attribut `DiscriminatorMap` contient la liste de toutes valeurs possibles de cette colonne, c'est-à-dire toutes les classes répertoriées dans la hiérarchie ; 
  - Nous remarquons que chaque classe est associée à une étiquette arbitraire 
  - Nous remarquons aussi que la classe mère est elle-même incluse dans la liste
- Enfin, l'attribut `InheritanceType` indique comment cette hiérarchie sera implémentée dans la base SQL. Nous avons deux possibilités :
  - `SINGLE_TABLE` : Toutes les propriétés des différentes classes sont fusionnées dans une seule table ; l'avantage est de ne pas avoir à faire de jointure pour reconstituer un objet, mais l'on peut potentiellement avoir beaucoup de cellules vides ;
  - `JOINED` : Chaque entité correspond à une table et la reconstitution des objets se fait par le biais de clefs étrangères ; les avantages et inconvénients sont inversés, comme on s'en doute.

> **N.B.** L'attribut `DiscriminatorMap` peut maintenant être engendrée automatiquement par Doctrine si elle n'est pas explicitement décrite.

Les classes filles sont juste des sous-classes au sens de la POO. Par exemple :
```php
class Book extends Document
{
   /* ... */
}
```
L'héritage de table est bien plus souple que les superclasses de modèle, en particulier parce que les classes-mères peuvent être abstraites ou concrètes au choix du développeur.

Un avantage de déclarer une classe-mère concrète est que l'on pourra écrire des requêtes concernant l'ensemble des sous-classes. Dans le cas de la bibliothèque, on peut vouloir la liste des documents qui ont été empruntés cette semaine, indépendamment du fait que ce soit des livres, de CD, des revues, etc. La classe-mère peut disposer, comme les autres, d'une classe de requêtes. 

Si l'on souhaite empêcher cela, il suffit de rendre la classe-mère `abstract`.

### Considérations d'efficacité

Dans le cas de l'héritage de table, les deux stratégies ont des avantages et des inconvénients inversés. Cela étant dit, les deux solutions sont fonctionnellement strictement identiques et le choix n'a donc pas forcément une importance cruciale.

>**N.B.** D'une manière générale, et cela est cohérent avec les bonnes pratiques de la POO, il n'est pas recommandé, pour des questions de clarté et de performance de créer des hiérarchies profondes. On se limitera — sauf cas particulier – à un seul niveau d'héritage.

#### Du pont de vue de la conception

La stratégie `SINGLE_TABLE`, est préférable lorsque la hiérarchie des types est simple et stable. Ajouter un nouveau type à la hiérarchie revient à ajouter de nouvelles colonnes à la table ; cela peut avoir un impact négatif sur la gestion interne des index et des colonnes.

La stratégie `JOINED` offre plus de souplesse puisque ajouter ou supprimer une entité revient à créer ou supprimer une table et cela aura donc un impact limité sur le schéma et la gestion interne de la base de données.

#### Du pont de vue de la performance

La stratégie `SINGLE_TABLE` est naturellement plus performante dans bien des cas puisqu'elle ne recourt jamais à des jointures.
Une manière d'améliorer les performances dans la stratégie `JOINED` est d'avoir recours aux « objets partiels » (cf. DQL).

#### Du point de vue de SQL

La stratégie `JOINED` est clairement plus conforme aux bonnes pratiques de SQL que la stratégie `SINGLE_TABLE`, celle-ci entraînant des dépendances fonctionnelles indésirables.