#!/usr/bin/env bash

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
DIM='\033[2m'
RESET='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
MITIGATED_CONF="$SCRIPT_DIR/nginx-mitigated.conf"
MITIGATED_EVENTS="$SCRIPT_DIR/events-mitigated.conf"
COMPOSE=(docker compose)

if [ ! -f "$MITIGATED_CONF" ] || [ ! -f "$MITIGATED_EVENTS" ]; then
  echo -e "${RED}Configurações mitigadas não encontradas em $SCRIPT_DIR${RESET}" >&2
  exit 1
fi

NGINX_RUNNING=0
for _ in $(seq 1 5); do
  if "${COMPOSE[@]}" exec -T nginx true >/dev/null 2>&1; then
    NGINX_RUNNING=1
    break
  fi
  sleep 1
done

if [ "$NGINX_RUNNING" -ne 1 ]; then
  echo -e "${RED}O serviço nginx não está em execução. Rode docker compose up -d primeiro.${RESET}" >&2
  exit 1
fi

echo ""
echo -e "${RED}${BOLD}╔══════════════════════════════════════════════╗${RESET}"
echo -e "${RED}${BOLD}║  ⚡ SLOWLORIS ATIVO — NGINX SATURADO         ║${RESET}"
echo -e "${RED}${BOLD}╚══════════════════════════════════════════════╝${RESET}"
echo ""

echo -e "${CYAN}${BOLD}Configurações de mitigação a aplicar:${RESET}"
echo -e "  ${CYAN}client_header_timeout${RESET}  60s (padrão)  →  ${GREEN}${BOLD}5s${RESET}   ${DIM}(conexões Slowloris cortadas antes de renovar)${RESET}"
echo -e "  ${CYAN}client_body_timeout${RESET}    60s (padrão)  →  ${GREEN}${BOLD}5s${RESET}"
echo -e "  ${CYAN}keepalive_timeout${RESET}      75s (padrão)  →  ${GREEN}${BOLD}15s${RESET}"
echo -e "  ${CYAN}worker_connections${RESET}     50 (laboratório) → ${GREEN}${BOLD}1024${RESET} ${DIM}(restaura capacidade realista)${RESET}"
echo -e "  ${CYAN}limit_conn addr${RESET}        sem limite    →  ${GREEN}${BOLD}20${RESET}   ${DIM}(defesa adicional após headers completos)${RESET}"
echo ""

echo -e "${YELLOW}${BOLD}Iniciando mitigação em:${RESET}"
echo -ne "  "
for i in 3 2 1; do
  echo -ne "${YELLOW}${BOLD}${i}...${RESET} "
  sleep 1
done
echo ""
echo ""

# ── Passo 1: Criar backup efêmero ──────────────────────────────────────────────
echo -e "${BOLD}→ [1/3] Preservando configuração vulnerável no container...${RESET}"
"${COMPOSE[@]}" exec -T nginx \
  cp /etc/nginx/conf.d/default.conf /tmp/default.conf.before-mitigation
"${COMPOSE[@]}" exec -T nginx \
  cp /etc/nginx/runtime/events.conf /tmp/events.conf.before-mitigation
echo -e "  ${GREEN}✓ Backup efêmero criado${RESET}"
echo ""

# ── Passo 2: Aplicar e validar configuração dentro do container ────────────────
echo -e "${BOLD}→ [2/3] Aplicando nova configuração dentro do container...${RESET}"
"${COMPOSE[@]}" exec -T nginx sh -c 'cat > /etc/nginx/conf.d/default.conf' < "$MITIGATED_CONF"
"${COMPOSE[@]}" exec -T nginx sh -c 'cat > /etc/nginx/runtime/events.conf' < "$MITIGATED_EVENTS"

if ! "${COMPOSE[@]}" exec -T nginx nginx -t; then
  "${COMPOSE[@]}" exec -T nginx \
    cp /tmp/default.conf.before-mitigation /etc/nginx/conf.d/default.conf
  "${COMPOSE[@]}" exec -T nginx \
    cp /tmp/events.conf.before-mitigation /etc/nginx/runtime/events.conf
  echo -e "  ${RED}✗ Configuração inválida; estado anterior restaurado${RESET}" >&2
  exit 1
fi

echo -e "  ${GREEN}✓ Configuração escrita no container${RESET}"
echo ""

# ── Passo 3: Recarregar o Nginx (sem reiniciar o container) ───────────────────
echo -e "${BOLD}→ [3/3] Recarregando o Nginx com nova configuração...${RESET}"
"${COMPOSE[@]}" exec -T nginx nginx -s reload
echo -e "  ${GREEN}✓ Nginx recarregado${RESET}"
echo ""

# ── Aguardar Nginx responder ──────────────────────────────────────────────────
echo -e "${DIM}Aguardando Nginx ficar disponível...${RESET}"
READY=0
for i in $(seq 1 15); do
  if "${COMPOSE[@]}" exec -T nginx wget -qO- http://127.0.0.1:8080/up > /dev/null 2>&1; then
    READY=1
    break
  fi
  echo -ne "  ${DIM}.${RESET}"
  sleep 1
done
echo ""

if [ "$READY" -eq 1 ]; then
  echo ""
  echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════╗${RESET}"
  echo -e "${GREEN}${BOLD}║  ✓  MITIGAÇÃO APLICADA COM SUCESSO           ║${RESET}"
  echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════╝${RESET}"
  echo ""
  echo -e "${GREEN}Nginx responde normalmente. Timeouts de 5s rejeitam${RESET}"
  echo -e "${GREEN}conexões Slowloris antes que elas renovem o timer.${RESET}"
  echo -e "${GREEN}O ataque continua ativo para demonstrar a eficácia da mitigação.${RESET}"
  echo ""
  echo -e "${BOLD}→ Volte ao navegador e clique em \"Verificar mitigação\".${RESET}"
else
  echo ""
  echo -e "${YELLOW}${BOLD}⚠  Nginx ainda iniciando. Aguarde alguns segundos${RESET}"
  echo -e "${YELLOW}   e clique em \"Verificar mitigação\" no navegador.${RESET}"
fi
echo ""
