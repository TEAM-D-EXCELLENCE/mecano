# Conventions backend — `apps/api`

Laravel 13, PHP 8.4. Ces conventions complètent [02 — Architecture applicative](../01-architecture/02-architecture-applicative.md).

## Règles fondatrices

1. **Aucune vue.** `resources/views` est supprimé. Aucun endpoint ne renvoie de HTML.
2. **Un contrôleur ne contient pas de métier.** Il valide, délègue à une action, renvoie une ressource. Une dizaine de lignes.
3. **Une action = un cas d'usage.** Une classe, une méthode publique `handle()`.
4. **Aucun appel direct à un service externe depuis le métier.** Toujours derrière un contrat.
5. **Toute écriture qui touche une donnée publique invalide les tags de revalidation.**

## Contrôleurs

```php
final class CarController extends Controller
{
    public function store(StoreCarRequest $request, CreateCar $createCar): JsonResponse
    {
        $car = $createCar->handle(CarData::fromRequest($request));

        return AdminCarResource::make($car)
            ->response()
            ->setStatusCode(201);
    }
}
```

Ce qu'un contrôleur ne fait pas : requête Eloquent, `if` métier, appel de service externe, calcul.

Un contrôleur de plus de 15 lignes par méthode signale du métier au mauvais endroit.

## Actions

```php
final readonly class ChangeCarStatus
{
    public function __construct(private FrontendRevalidator $revalidator) {}

    public function handle(Car $car, CarStatus $to): Car
    {
        if (! $car->status->canTransitionTo($to)) {
            throw new InvalidStatusTransition($car->status, $to);
        }

        if ($to === CarStatus::Available && ! $car->hasMainPhoto()) {
            throw new CarNotPublishable('Une photo principale est requise.');
        }

        DB::transaction(function () use ($car, $to) {
            $car->status = $to;
            $car->published_at ??= $to === CarStatus::Available ? now() : null;
            $car->sold_at = $to === CarStatus::Sold ? now() : null;
            $car->save();
        });

        $this->revalidator->revalidate(["car:{$car->slug}", 'cars', 'home']);

        return $car->fresh();
    }
}
```

- `final readonly`, dépendances injectées par le constructeur.
- Reçoit des modèles et des objets de données, **jamais une `Request`**.
- Lance des exceptions métier nommées, jamais `abort(422)`.
- Les invariants métier sont vérifiés ici : c'est le seul endroit où ils ne peuvent pas être contournés.

## Validation

Toute la validation dans une `FormRequest`, une par écriture. Jamais de `$request->validate()` dans un contrôleur.

```php
final class StoreCarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'brand_id'     => ['required', 'integer', Rule::exists('brands', 'id')->where('is_active', true)],
            'model'        => ['required', 'string', 'max:120'],
            'year'         => ['required', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'mileage_km'   => ['required', 'integer', 'min:0', 'max:2000000'],
            'price_xaf'    => ['required', 'integer', 'min:1'],
            'fuel'         => ['required', Rule::enum(FuelType::class)],
            'transmission' => ['required', Rule::enum(TransmissionType::class)],
            'color'        => ['required', 'string', 'max:40'],
            'condition'    => ['required', Rule::enum(VehicleCondition::class)],
            'description'  => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['brand_id.exists' => 'Cette marque n\'existe pas ou a été désactivée.'];
    }
}
```

Les messages sont **en français** : ils sont affichés au mécanicien.

## Ressources

Deux familles strictement séparées, `Public/` et `Admin/`, sans classe partagée. C'est une frontière de sécurité, la duplication est voulue.

```php
final class CarDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'slug'         => $this->slug,
            'brand'        => ['id' => $this->brand->id, 'slug' => $this->brand->slug, 'name' => $this->brand->name],
            'model'        => $this->model,
            'year'         => $this->year,
            'mileage_km'   => $this->mileage_km,
            'price_xaf'    => $this->price_xaf,
            'fuel'         => ['value' => $this->fuel->value, 'label' => $this->fuel->label()],
            'transmission' => ['value' => $this->transmission->value, 'label' => $this->transmission->label()],
            'condition'    => ['value' => $this->condition->value, 'label' => $this->condition->label()],
            'color'        => $this->color,
            'description'  => $this->description,
            'status'       => ['value' => $this->status->value, 'label' => $this->status->label()],
            'sold_at'      => $this->sold_at?->toIso8601String(),
            'photos'       => PhotoResource::collection($this->photos),
            'videos'       => VideoResource::collection($this->videos),
            'whatsapp_url' => $this->status->allowsContact() ? $this->whatsappUrl() : null,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
```

Règles :

- **Aucun champ omis.** Un champ optionnel vaut `null` explicitement. Le contrat le garantit, le front en dépend.
- Toute énumération sort en `{ value, label }`.
- Toute date en ISO 8601.
- Aucun champ absent du contrat, même utile.

## Modèles

- `$fillable` explicite, jamais `$guarded = []`.
- `casts()` pour toutes les énumérations et tous les booléens.
- Aucune logique métier dans un modèle : relations, casts, accesseurs de présentation simples.
- Aucun appel réseau depuis un modèle, jamais.

`Photo` et `Video` héritent de `Media` avec un scope global et un `kind` fixé automatiquement — voir [02 — Architecture applicative](../01-architecture/02-architecture-applicative.md).

## Requêtes de lecture

Les lectures complexes vivent dans `app/Queries/`, pas dans un contrôleur.

```php
final class CarCatalogQuery
{
    public function __invoke(CatalogFilters $f): LengthAwarePaginator
    {
        return Car::query()
            ->with(['brand', 'photos' => fn ($q) => $q->where('role', MediaRole::Main)])
            ->whereIn('status', $f->includeSold
                ? [CarStatus::Available, CarStatus::Reserved, CarStatus::Sold]
                : [CarStatus::Available, CarStatus::Reserved])
            ->whereNotNull('published_at')
            ->when($f->brandSlug, fn ($q, $s) => $q->whereRelation('brand', 'slug', $s))
            ->when($f->priceMax, fn ($q, $p) => $q->where('price_xaf', '<=', $p))
            ->when($f->yearMin, fn ($q, $y) => $q->where('year', '>=', $y))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate($f->perPage);
    }
}
```

**Le chargement anticipé des relations est obligatoire.** `Model::preventLazyLoading()` est actif en développement et en test : une requête N+1 fait échouer les tests, elle ne se découvre pas en production.

## Erreurs

Un seul format d'erreur pour toute l'API, produit par un gestionnaire d'exceptions unique.

```json
{
  "error": {
    "code": "QUOTA_EXCEEDED",
    "message": "Quota mensuel de suppression de fond atteint (50/50).",
    "details": { "provider": "removebg", "used": 50, "limit": 50, "resets_at": "2026-09-01T00:00:00Z" }
  }
}
```

- `code` : identifiant stable, en majuscules. **Le front compare sur `code`, jamais sur `message`.**
- `message` : français, affichable tel quel au mécanicien.
- `details` : facultatif, structuré.

Chaque exception métier porte son `code` et son statut HTTP. La correspondance est documentée dans [03-api/README.md](../03-api/README.md).

## Files d'attente

- Un job est **idempotent**. Rejoué, il ne produit pas d'effet en double — un job d'amélioration vérifie qu'un résultat n'existe pas déjà pour ce `(media_id, type, params)` avant de consommer un crédit.
- Un job dure moins de 60 secondes. Sinon on le découpe.
- `$tries` et `$backoff` explicites sur chaque job.
- `failed()` implémenté : journalisation, et remboursement du quota le cas échéant.
- Un job ne reçoit que des identifiants, jamais un modèle complet sérialisé.

## Intégrations externes

Toujours derrière un contrat de `app/Support/Contracts/`. Chaque contrat a une implémentation factice dans `app/Support/Integrations/Fake*` utilisée par les tests : **aucun test ne fait d'appel réseau**.

## Style

- `declare(strict_types=1);` en tête de chaque fichier.
- Types de retour explicites partout.
- `final` par défaut sur les classes ; l'ouvrir demande une raison.
- Pint (préréglage Laravel), Larastan niveau 6. Les deux bloquent la PR.
- Nommage du code en anglais, messages destinés à l'utilisateur en français.
