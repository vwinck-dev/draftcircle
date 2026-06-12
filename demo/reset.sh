#!/usr/bin/env bash

set -euo pipefail

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RESET='\033[0m'

echo -e "${YELLOW}Reiniciando atacante e Nginx para o estado vulnerável inicial...${RESET}"
docker compose restart attacker nginx

for _ in $(seq 1 20); do
  if docker compose exec -T nginx wget -qO- http://127.0.0.1:8080/up >/dev/null 2>&1; then
    echo -e "${GREEN}Demo pronta. O ataque está parado e o Nginx voltou ao estado inicial.${RESET}"
    exit 0
  fi
  sleep 1
done

echo "O Nginx não respondeu dentro do tempo esperado." >&2
exit 1
