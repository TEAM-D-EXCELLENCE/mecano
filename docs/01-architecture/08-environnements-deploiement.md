# 08 — Environnements et déploiement

## Les trois environnements

| | Local | Aperçu | Production |
|---|---|---|---|
| Vitrine | `localhost:3000` | `*.vercel.app` (par PR) | `garage.com` |
| Backoffice | `localhost:3001` | `*.vercel.app` (par PR) | `admin.garage.com` |
| API | `localhost:8000` | serveur, base `mecano_preview` | `mecano-api.duckdns.org` |
| Base | MySQL local | `mecano_preview` | `mecano_prod` |
| Médias | Cloudinary `mecano/dev`, bucket R2 `mecano-videos-dev` | dossiers `preview` | dossiers `prod` |
| `APP_DEBUG` | `true` | `true` | **`false`** — vérifié au déploiement |
| Emails | `log` | `log` | `log` (aucun email en V1) |

Les dossiers de médias sont séparés par environnement. Sans cette séparation, un test en local supprimerait un fichier de production.

Décision différée R02 : une préproduction dédiée ou les aperçus Vercel suffisent. Les aperçus tiennent ce rôle jusqu'à la mise en production de M1.

---

## Mise en place initiale — dans cet ordre

Cet ordre n'est pas arbitraire : chaque étape conditionne la suivante.

1. **Brancher le domaine sur Vercel.** Avant tout code. La configuration des cookies du BFF, les origines CORS et les URL canoniques du SEO en dépendent. Le faire en fin de projet impose de tout reconfigurer.
2. Créer les deux projets Vercel (`mecano-web` → `apps/web`, `mecano-admin` → `apps/admin`), avec leurs domaines.
3. Créer le compte Cloudinary, le bucket R2 et son domaine personnalisé (`media.garage.com`).
4. Préparer le serveur : PHP 8.4, MySQL 8, Nginx, Composer, Supervisor.
5. Créer le certificat TLS de `mecano-api.duckdns.org` (Let's Encrypt, renouvellement automatique).
6. Renseigner les variables d'environnement des deux côtés.
7. Premier déploiement de M0, et vérifier la connexion de bout en bout.

---

## Serveur — API Laravel

### Attendu

PHP 8.4 (`bcmath`, `curl`, `fileinfo`, `gd` ou `imagick`, `intl`, `mbstring`, `pdo_mysql`, `zip`), MySQL 8, Nginx, Composer 2, Supervisor, `certbot`.

### Arborescence

```
/var/www/mecano/
├── releases/          une par déploiement, les 5 dernières conservées
├── current -> releases/2026-08-25-1430/
└── shared/
    ├── .env           jamais dans le dépôt
    └── storage/       journaux, cache, fichiers temporaires
```

Racine Nginx sur `/var/www/mecano/current/apps/api/public`.

### Séquence de déploiement

```bash
set -e                                    # toute erreur interrompt le déploiement
composer install --no-dev --optimize-autoloader
php artisan migrate --force               # jamais --seed en production
php artisan config:cache route:cache event:cache
php artisan queue:restart                 # les workers rechargent le nouveau code
sudo systemctl reload php8.4-fpm
```

`php artisan down` avant les migrations, `up` après, **uniquement** si la migration n'est pas rétrocompatible. Une migration rétrocompatible (colonne nullable ajoutée) ne justifie pas d'interruption.

`queue:restart` est obligatoire : sans lui, les workers continuent d'exécuter l'ancien code.

### Worker de file d'attente

```ini
# /etc/supervisor/conf.d/mecano-worker.conf
[program:mecano-worker]
command=php /var/www/mecano/current/apps/api/artisan queue:work --tries=3 --max-time=3600 --sleep=3
autostart=true
autorestart=true
numprocs=2
stopwaitsecs=70          # supérieur au job le plus long
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/mecano/worker.log
```

`--max-time=3600` fait redémarrer le worker chaque heure, ce qui évite les fuites de mémoire sur les traitements d'images.

### Planificateur

```cron
* * * * * cd /var/www/mecano/current/apps/api && php artisan schedule:run >> /dev/null 2>&1
```

Tâches planifiées : `PurgeOrphanUploads` (horaire), `AggregateCarEvents` (nocturne), purge de `car_events` au-delà de 12 mois (hebdomadaire), sauvegarde de la base (quotidienne).

---

## Vercel — les deux apps Next

Deux projets, un dépôt, un répertoire racine différent. Déploiement automatique sur fusion dans `main`, aperçu sur chaque PR.

### Variables — `apps/web`

| Variable | Exemple | Exposée au navigateur |
|---|---|---|
| `API_BASE_URL` | `https://mecano-api.duckdns.org/api/v1` | non |
| `NEXT_PUBLIC_SITE_URL` | `https://garage.com` | oui |
| `REVALIDATE_SECRET` | *(secret)* | **non — jamais** |

### Variables — `apps/admin`

| Variable | Exemple | Exposée au navigateur |
|---|---|---|
| `API_BASE_URL` | `https://mecano-api.duckdns.org/api/v1` | non |
| `COOKIE_NAME` | `mc_s` | non |
| `COOKIE_DOMAIN` | `admin.garage.com` | non |

`apps/admin` **n'a besoin d'aucune variable `NEXT_PUBLIC_`**. Si l'une devient nécessaire, c'est le signe qu'un secret est en train de fuir vers le navigateur : passer par le BFF.

### Aperçus et API

Les aperçus Vercel ont des URL dynamiques, qui ne peuvent pas être listées dans le CORS de l'API de production. Ils pointent donc vers l'API de préproduction, dont le CORS accepte le motif `*.vercel.app`. **Un aperçu ne parle jamais à la base de production.**

---

## Variables de l'API

```dotenv
APP_NAME=Mecano
APP_ENV=production
APP_DEBUG=false                 # jamais true en production
APP_URL=https://mecano-api.duckdns.org
APP_FRONTEND_URL=https://garage.com
APP_ADMIN_URL=https://admin.garage.com

DB_CONNECTION=mysql
DB_DATABASE=mecano_prod
DB_USERNAME=mecano
DB_PASSWORD=

QUEUE_CONNECTION=database       # Redis différé, R01
CACHE_STORE=file
SESSION_DRIVER=array            # API sans état : aucune session

SANCTUM_TOKEN_EXPIRATION=10080  # 7 jours, en minutes

CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_UPLOAD_FOLDER=mecano/prod/cars

R2_ACCOUNT_ID=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=mecano-videos
R2_PUBLIC_BASE_URL=https://media.garage.com

REMOVEBG_API_KEY=
REMOVEBG_MONTHLY_LIMIT=50

REVALIDATE_URL=https://garage.com/api/revalidate
REVALIDATE_SECRET=              # identique côté Vercel
```

`.env.example` liste toutes ces clés, vides et commentées. C'est le seul fichier d'environnement versionné.

---

## Sauvegardes

Décision différée R03, à figer avant la mise en production de M1. Proposition :

| Quoi | Fréquence | Rétention | Où |
|---|---|---|---|
| Base MySQL (`mysqldump` chiffré) | quotidienne, 3 h | 30 jours | Hors du serveur d'exécution |
| `shared/.env` | à chaque modification | — | Gestionnaire de secrets, hors dépôt |
| Médias | — | — | Cloudinary et R2 sont durables, pas de sauvegarde de notre côté |

**La restauration doit être testée une fois avant la mise en production**, sur une base de préproduction. Une sauvegarde jamais restaurée n'est pas une sauvegarde.

Point important : les médias vivent chez des tiers, mais les **clés de stockage** vivent en base. Une base perdue rend les médias inexploitables. C'est la seule chose réellement critique à sauvegarder.

---

## Retour arrière

| Cas | Procédure |
|---|---|
| Régression front | Vercel, « Promote to production » sur le déploiement précédent. Immédiat |
| Régression API sans migration | `current` repointé vers la release précédente, `php-fpm` rechargé, `queue:restart` |
| Régression API avec migration | `php artisan migrate:rollback --step=1`, puis repointage. **À éviter** — d'où la règle des migrations rétrocompatibles |
| Perte de base | Restauration du dernier `mysqldump`, puis vérification de cohérence des médias |

C'est précisément pour rendre le retour arrière possible que les migrations doivent être rétrocompatibles : une colonne ajoutée nullable permet de revenir en arrière sans toucher au schéma.

---

## Surveillance

Minimale et suffisante pour ce volume :

| Quoi | Comment | Seuil d'alerte |
|---|---|---|
| API en ligne | `GET /api/v1/health` sondé toutes les 5 min | 2 échecs consécutifs |
| Vitrine en ligne | Surveillance Vercel | — |
| File d'attente vivante | Nombre de jobs en attente | > 50, ou un job de plus de 10 min |
| Jobs en échec | Table `failed_jobs` | ≥ 1 |
| Échecs de revalidation | Journal | ≥ 1 — **silencieux côté utilisateur, donc à surveiller** |
| Quota remove.bg | Backoffice | 80 % du plafond mensuel |
| Crédits Cloudinary | Tableau de bord, mensuel | 70 % |
| Espace disque | Serveur | 80 % |

`GET /api/v1/health` renvoie l'état de la base, de la file d'attente et de l'accessibilité des fournisseurs. Elle n'expose aucune version ni détail interne.
