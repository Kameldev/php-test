
**Template Manager Refactoring - Processus et Principes**

---

## **ANALYSE DU CODE INITIAL - IDENTIFICATION COMPLÈTE DES PROBLÈMES**

### **1. VIOLATIONS PSR - Standards non respectés**

#### **Problème PSR-1 : Nommage et structure**
- Le code violait les conventions de nommage de base :

- Toutes les propriétés d'entités étaient publiques (public $id, public $subject)
- Aucune encapsulation avec getters/setters
- Accès direct aux propriétés dans tout le code
- Incohérences dans le nommage des variables avec mélange d'underscores et camelCase
- Noms de méthodes ne suivant pas le camelCase de manière cohérente
- Certaines variables utilisant snake_case au lieu de camelCase
- Patterns de nommage des propriétés incohérents entre les entités

#### **Problème PSR-4 : Organisation et autoloading**
- Aucun namespace nulle part
- Pas d'autoloading configuré dans `composer.json`
- `require_once` manuels partout dans les fichiers de test
- utilisation de "and" au lieu "&&" qui peut avoir des effets inattendus
- Structure de fichiers non conforme PSR-4

#### **Problème PSR-12 : Style et formatage**
- Indentation incohérente (mélange espaces/tabs)
- DocBlocks manquants ou incomplets
- Pas de lignes vides avant les `return`
- Style de code non uniforme

### **2. Utilisation du pattern SINGLETON - Architecture problématique**

#### **Le problème du SingletonTrait**
```php
trait SingletonTrait {
    protected static $instance = null;
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
```

#### **Utilisation problématique**
```php
// Dans TemplateManager::computeText()
$quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
$site = SiteRepository::getInstance()->getById($quote->siteId);
$destination = DestinationRepository::getInstance()->getById($quote->destinationId);
```

#### **Conséquences problématiques**
1. **Impossible à tester** : Pas de mock possible, toujours les vraies données
2. **Couplage fort** : Dépendance directe aux classes concrètes
3. **État global caché** : Modifications imprévisibles depuis n'importe où
4. **Violation DIP** : Dépend d'implémentations, pas d'abstractions

### **3. VIOLATIONS SOLID - Principes non respectés**

#### **Violation SRP : Single Responsibility Principle**
La méthode `computeText()` faisait TOUT :
- Récupération des données depuis les repositories
- Validation des données d'entrée
- Logique de remplacement des placeholders
- Gestion des cas d'erreur
- Rendering des quotes (via méthodes statiques)

#### **Violation OCP : Open/Closed Principle**
Pour ajouter un nouveau placeholder `[company:name]` :
- Il fallait MODIFIER la méthode `computeText()`
- Risque de casser l'existant
- Code fermé à l'extension

#### **Violation DIP : Dependency Inversion Principle**
```php
// Dépendance directe aux classes concrètes
$quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
```
- Pas d'interfaces
- Impossible d'injecter d'autres implémentations
- Couplage fort avec les repositories Singleton

### **4. PROBLÈMES ARCHITECTURAUX MAJEURS**

#### **Logique métier mélangée**
```php
private function computeText($text, array $data) {
    // Récupération de données
    $applicationContext = ApplicationContext::getInstance();
    $quote = (isset($data['quote']) && $data['quote'] instanceof Quote) ? $data['quote'] : null;
    
    if ($quote) {
        // Plus de récupération
        $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        $site = SiteRepository::getInstance()->getById($quote->siteId);
        $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
        
        // Conditions complexes
        if (strpos($text, '[quote:destination_link]') !== false) {
            $destinationForLink = DestinationRepository::getInstance()->getById($quote->destinationId);
        }
        
        // Remplacement de placeholders
        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        $containsSummary = strpos($text, '[quote:summary]');
        
        if ($containsSummaryHtml !== false || $containsSummary !== false) {
            // Plus de logique...
        }
        
        // Et encore plus de conditions...
    }
}
```

#### **Responsabilités mélangées dans Quote**
```php
class Quote {
    public $id;
    public $siteId;
    // ... propriétés de données
    
    // PROBLÈME : Rendering mélangé avec les données
    public static function renderHtml(Quote $quote) {
        return '<p>' . $quote->id . '</p>';
    }
    
    public static function renderText(Quote $quote) {
        return (string) $quote->id;
    }
}
```

## **PROCESSUS DE REFACTORING ÉTAPE PAR ÉTAPE**

### **Étape 1 : Modernisation de la structure (Commit 1)**

#### **Objectif**
Poser les bases d'un projet moderne avec autoloading

#### **Actions réalisées**
1. **Mise à jour `composer.json`** avec autoloading PSR-4
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

2. **Choix du namespace racine** `App\` (standard Symfony/Laravel)

#### **Principe appliqué**
**PSR-4** : Organisation des namespaces et autoloading moderne

### **Étape 2 : Application des standards PSR complets (Commit 2)**

#### **Objectif**
Transformer le code en respectant tous les standards PSR

#### **Actions réalisées**

##### **PSR-1 appliqué : Encapsulation stricte**
```php
// AVANT : Propriétés publiques
class Template {
    public $id;
    public $subject;
    public $content;
}

// APRÈS : Encapsulation avec getters/setters
class Template {
    private $id;
    private $subject;
    private $content;
    
    public function getId(): int { return $this->id; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject) { $this->subject = $subject; }
    // etc.
}
```

##### **PSR-4 appliqué : Namespaces cohérents**
- `App\Entity\Template`
- `App\Repository\QuoteRepository`
- `App\Context\ApplicationContext`

##### **PSR-12 appliqué : Style uniforme**
- Indentation 4 espaces cohérente
- DocBlocks complets avec `@param`, `@return`
- Imports organisés

#### **Principe appliqué**
**Standards modernes** : Code lisible

---

### **Étape 3 : Suppression du Singleton (Commit 3)**

#### **Objectif**
Rendre le code testable en supprimant l'utilisation du pattern Singleton

#### **Actions réalisées**
1. **Suppression du trait** `SingletonTrait.php`
2. **Suppression `use SingletonTrait`** dans tous les repositories
3. **Transformation en classes normales** instanciables

```php
// AVANT : Singleton non testable
class QuoteRepository implements Repository {
    use SingletonTrait;  // ← SUPPRIMÉ
}

// APRÈS : Classe normale injectable
class QuoteRepository implements Repository {
    // Juste les méthodes métier
}
```

#### **Principe appliqué**
**Testability First** : Supprimer les obstacles aux tests

#### **Impact immédiat**
Les repositories deviennent instantiables, donc injectables et mockables pour les tests.

---

### **Étape 4 : Création des contrats d'injection (Commit 4)**

#### **Objectif**
Créer les abstractions nécessaires pour l'injection de dépendances

#### **Actions réalisées**

##### **Contrats créés**
```php
// Pour les repositories
interface RepositoryInterface {
    public function getById($id);
}

// Pour le contexte applicatif  
interface ApplicationContextInterface {
    public function getCurrentUser(): User;
    public function getCurrentSite(): Site;
}

// Pour le pattern Strategy (préparation étape 6)
interface PlaceholderReplacerInterface {
    public function replace(string $text, array $data): string;
}
```

##### **Implémentation des interfaces**
Tous les repositories et le contexte implémentent maintenant leurs interfaces respectives.

#### **Principe appliqué**
**Dependency Inversion Principle** : Dépendre d'abstractions, pas d'implémentations

---

### **Étape 5 : Extraction des services (Commit 5)**

#### **Objectif**
Séparer les responsabilités selon le principe SRP

#### **Actions réalisées**

##### **Service d'orchestration créé**
```php
class PlaceholderReplacementService {
    private $replacers;
    
    public function processTemplate(Template $template, array $data): Template {
        $processed = clone $template;
        $processed->setSubject($this->replacePlaceholders($processed->getSubject(), $data));
        $processed->setContent($this->replacePlaceholders($processed->getContent(), $data));
        return $processed;
    }
    
    private function replacePlaceholders(string $text, array $data): string {
        foreach ($this->replacers as $replacer) {
            $text = $replacer->replace($text, $data);
        }
        return $text;
    }
}
```

##### **Service de rendering séparé**
```php
// AVANT : Dans Quote (violation SRP)
class Quote {
    public static function renderHtml(Quote $quote) { /* ... */ }
}

// APRÈS : Service dédié (SRP respecté)
class QuoteRenderingService {
    public function renderHtml(Quote $quote): string { /* ... */ }
    public function renderText(Quote $quote): string { /* ... */ }
}
```

#### **Principe appliqué**
**Single Responsibility Principle** : Une classe = une responsabilité

#### **Logique de cette étape**
Au lieu d'avoir tout dans `computeText()`, nous créons des services spécialisés qui peuvent être composés ensemble.

---

### **Étape 6 : Implémentation du pattern Strategy (Commit 6)**

#### **Objectif**
Rendre le code extensible sans modification (OCP)

#### **Actions réalisées**

##### **Strategy pour les quotes**
```php
class QuotePlaceholderReplacer implements PlaceholderReplacerInterface {
    private $quoteRepository;
    private $siteRepository;
    private $destinationRepository;
    private $quoteRenderingService;
    
    public function __construct(/* injection des dépendances */) { /* ... */ }
    
    public function replace(string $text, array $data): string {
        // Logique spécifique aux placeholders quote
        $quote = $this->extractQuote($data);
        if (!$quote) return $this->cleanQuotePlaceholders($text);
        
        return $this->processQuotePlaceholders($text, $quote);
    }
}
```

##### **Strategy pour les users**
```php
class UserPlaceholderReplacer implements PlaceholderReplacerInterface {
    public function replace(string $text, array $data): string {
        // Logique spécifique aux placeholders user
    }
}
```

#### **Principe appliqué**
**Open/Closed Principle** : Ouvert à l'extension, fermé à la modification

#### **Bénéfice immédiat**
Ajouter un nouveau type de placeholder ne nécessite plus de modifier le code existant, 
juste créer une nouvelle classe.

---

### **Étape 7 : Injection de dépendances avec Factory (Commit 7)**

#### **Objectif**
Permettre l'injection tout en préservant la rétrocompatibilité

#### **Actions réalisées**

##### **Factory pour création avec dépendances**
```php
class TemplateManagerFactory {
    public static function createPlaceholderReplacementService(): PlaceholderReplacementService {
        // Création des repositories (sans singleton)
        $quoteRepository = new QuoteRepository();
        $siteRepository = new SiteRepository();
        $destinationRepository = new DestinationRepository();
        $applicationContext = new ApplicationContext();
        $quoteRenderingService = new QuoteRenderingService();

        // Création des replacers avec injection
        $quoteReplacer = new QuotePlaceholderReplacer(
            $quoteRepository,
            $siteRepository,
            $destinationRepository,
            $quoteRenderingService
        );

        $userReplacer = new UserPlaceholderReplacer($applicationContext);

        // Service avec tous les replacers
        return new PlaceholderReplacementService([$quoteReplacer, $userReplacer]);
    }
}
```

##### **TemplateManager refactorisé**
```php
class TemplateManager {
    private $placeholderService;
    
    // Injection optionnelle = rétrocompatibilité
    public function __construct(PlaceholderReplacementService $placeholderService = null) {
        $this->placeholderService = $placeholderService ?: TemplateManagerFactory::createPlaceholderReplacementService();
    }
    
    // SIGNATURE PRÉSERVÉE (contrainte respectée)
    public function getTemplateComputed(Template $tpl, array $data) {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }
        return $this->placeholderService->processTemplate($tpl, $data);
    }
}
```

#### **Principe appliqué**
**Dependency Injection** + **Backward Compatibility**

#### **Stratégie de cette étape**
Permettre l'injection moderne tout en gardant l'usage simple pour les utilisateurs existants.

---

### **Étape 8 : Mise à jour des tests et exemples (Commit 9)**

#### **Objectif**
Adapter les fichiers existants à la nouvelle architecture

#### **Actions réalisées**
1. **Mise à jour des tests** avec les nouveaux namespaces
2. **Adaptation de l'exemple** avec autoloading
3. **Utilisation des getters/setters** partout
---

### **Étape 9 : Tests unitaires complets (Commit 9)**

#### **Objectif**
Ajouter une couverture de tests maximale avec mocks

#### **Actions réalisées**

##### **Tests avec mocks pour isolation complète**
```php
public function testQuotePlaceholderReplacement() {
    // Mock précis et déterministe
    $mockRepo = $this->createMock(RepositoryInterface::class);
    $mockRepo->method('getById')->willReturn(new Quote(1, 2, 3, new DateTime()));
    
    $replacer = new QuotePlaceholderReplacer($mockRepo, $mockSite, $mockDest, $mockRendering);
    $result = $replacer->replace('[quote:destination_name]', ['quote' => $quote]);
    
    $this->assertEquals('France', $result);
}
```

##### **Tests  cases**
- Données manquantes
- Exceptions des repositories
- Placeholders multiples
- Cas d'erreur

#### **Principe appliqué**
**Test-Driven Quality** : Chaque composant testé isolément
---

## **ARCHITECTURE FINALE OBTENUE**

### **Structure claire et modulaire**

### **Responsabilités claires**
- **TemplateManager** : Point d'entrée, préserve la compatibilité
- **PlaceholderReplacementService** : Orchestre les remplacements
- **QuotePlaceholderReplacer** : Gère uniquement les placeholders quote
- **UserPlaceholderReplacer** : Gère uniquement les placeholders user
- **QuoteRenderingService** : Rendering séparé des données
- **Factory** : Création centralisée avec bonnes dépendances

---

## **RÉSULTATS ET BÉNÉFICES**

### **Pour la compréhensibilité (objectif README)**
- **Noms explicites** : Chaque classe dit ce qu'elle fait
- **Responsabilités séparées** : Plus besoin de décortiquer une méthode de 80 lignes
- **Flux logique clair** : TemplateManager → Service → Replacers
- **Documentation complète** : DocBlocks sur toutes les méthodes

### **Pour la facilité de modification (objectif README)**
- **Nouveau placeholder** : Créer une classe, l'ajouter au service
- **Bug dans quotes** : Une seule classe à modifier
- **Nouveau repository** : Interface déjà définie
- **Tests de régression** : Détection automatique des problèmes

### **Standards professionnels**
- **PSR-1/4/12** respectés partout
- **SOLID** appliqué rigoureusement
- **Design patterns** modernes (Strategy, Factory, DI)
- **Testabilité** maximale avec mocks

### **Stabilité**
- **Signature préservée** : aucune régression
- **Rétrocompatibilité** totale via Factory
- **Tests complets** couvrant tous les cas

---

## **CONCLUSION**

La mission du README était de rendre le code **"compréhensible par le prochain développeur et facile à modifier"**.

- Le code est maintenant **auto-documenté** par sa structure
- L'architecture est **évidente** et suit les standards actuels
- Les **extensions futures** se feront sans modification du code existant
- Les **tests complets** garantissent la stabilité


## **REMARQUES**

- J'ai pris le choix de ne pas uprade de version de php (par respect des consignes), l'idéal est de passer sur une version plus récente
- Le code est préparé pour PHP 7 strict mode, mais on ne peut l'activer (en ajoutant la directive dans les fichiers : declare(strict_types=1);) que si le projet ne tourne plus sur PHP 5.5