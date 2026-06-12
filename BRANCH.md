# Branch: `university-project/dos-and-mitigation-demo`

This branch adds a live, interactive DoS attack demonstration on top of the existing DraftCircle application (Laravel 11 + Vue 3 + Nginx + Docker). It was created for an Information Security university assignment (A3).

> **Presentation notes** (`APRESENTACAO.md`, `ROTEIRO_GRAVACAO.md`) are kept out of version control intentionally — they contain content in Portuguese specific to the live presentation and add no long-term value to the repository.

---

## What was added

### Infrastructure

| File | Purpose |
|------|---------|
| `docker/attacker/Dockerfile` | Python container that hosts the Slowloris attacker |
| `docker/attacker/api.py` | Minimal HTTP API (`POST /attack`, `POST /stop`) to control the attack from the app |
| `docker/nginx/nginx.conf` | Nginx main config — intentionally sets `worker_connections 50` to make the attack observable |
| `docker/nginx/events-vulnerable.conf` | Same low limit, used by the reset script |
| `demo/nginx-mitigated.conf` | Hardened Nginx config applied during the live mitigation |
| `demo/events-mitigated.conf` | Restores `worker_connections` to a realistic value as part of the mitigation |
| `demo/mitigate.sh` | Applies the hardened config inside the nginx container without a restart |
| `demo/reset.sh` | Rolls the lab back to the vulnerable state so the demo can be repeated |

### Application layer

| File | Purpose |
|------|---------|
| `app/Http/Controllers/DemoController.php` | Exposes `/demo/attack/start` and `/demo/attack/stop` — proxies commands to the attacker container |
| `resources/js/Components/DemoOverlay.vue` | Full-screen guided overlay rendered independently from Inertia |
| `resources/js/demo-overlay-mount.ts` | Separate Vite entry point that mounts the overlay on `#demo-root` |
| `resources/js/demoConfig.ts` | Slide definitions and step configuration |

### Modified files

- `compose.yaml` — adds the `attacker` service and ephemeral nginx config via tmpfs
- `routes/web.php` — registers `/demo/*` routes
- `resources/views/app.blade.php` — adds `<div id="demo-root">` for the overlay
- `vite.config.js` — adds the `demo-overlay` entry point

---

## How the demo works

1. **Normal state** — site is healthy, `worker_connections` is limited to 50 to make the attack feasible in a classroom.
2. **Attack** — clicking "Start Attack" in the overlay triggers `DemoController`, which calls `attacker:8888/attack`. The Python container opens 500 slow HTTP connections against `nginx:8080`, exhausting its connection slots. The site goes down.
3. **Mitigation** — while the site is down, run `./demo/mitigate.sh` in a terminal. The script writes a hardened config (5 s `client_header_timeout`, `limit_conn`, restored `worker_connections 1024`) into the running nginx container and reloads it without a restart.
4. **Recovery** — the attack is still running, but connections are now dropped before they can hold slots. The site responds normally again.

The overlay persists its step in `localStorage` (`draftcircle_demo_state`) so it survives the browser losing connection during the attack.

---

## Running the lab

```bash
# Start all containers (including the attacker)
docker compose up -d --build

# Seed the database if needed
docker compose exec app php artisan migrate:fresh --seed --force

# Ensure the lab starts in the vulnerable state
./demo/reset.sh

# Open the app and click "▶ Start Demo" on the login screen
open http://localhost

# When the site goes down, apply the mitigation from a second terminal
./demo/mitigate.sh
```

All attack traffic stays inside the Docker network. Do not point the attacker at external systems.

---

## Security scope

This demo is a controlled, single-origin Slowloris attack against a deliberately weakened Nginx configuration. The mitigation shown (`client_header_timeout`, `limit_conn`, increased `worker_connections`) is a valid first line of defense but is not sufficient against distributed or volumetric attacks. A production deployment would also need a CDN/WAF, OS-level connection limits, and observability tooling.
