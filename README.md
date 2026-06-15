<div align="center">

```
███████╗██╗  ██╗ █████╗ ██████╗  ██████╗ ██╗    ██╗██████╗ ██████╗ ██╗██████╗  ██████╗ ███████╗
██╔════╝██║  ██║██╔══██╗██╔══██╗██╔═══██╗██║    ██║██╔══██╗██╔══██╗██║██╔══██╗██╔════╝ ██╔════╝
███████╗███████║███████║██║  ██║██║   ██║██║ █╗ ██║██████╔╝██████╔╝██║██║  ██║██║  ███╗█████╗  
╚════██║██╔══██║██╔══██║██║  ██║██║   ██║██║███╗██║██╔══██╗██╔══██╗██║██║  ██║██║   ██║██╔══╝  
███████║██║  ██║██║  ██║██████╔╝╚██████╔╝╚███╔███╔╝██████╔╝██║  ██║██║██████╔╝╚██████╔╝███████╗
╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝╚═════╝  ╚═════╝  ╚══╝╚══╝ ╚═════╝ ╚═╝  ╚═╝╚═╝╚═════╝  ╚═════╝ ╚══════╝
```

**The Complete Cybersecurity Arsenal — 12-Module Platform**

[![Live](https://img.shields.io/badge/LIVE-shadowbridge.store-00d4ff?style=for-the-badge&logo=google-chrome&logoColor=black)](https://shadowbridge.store)
[![Early Access](https://img.shields.io/badge/Status-Early%20Access%20🚧-purple?style=for-the-badge)](https://shadowbridge.store)
[![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://python.org)
[![FastAPI](https://img.shields.io/badge/FastAPI-009688?style=for-the-badge&logo=fastapi&logoColor=white)](https://fastapi.tiangolo.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

*From real-time infrastructure monitoring to honeypots, SIEM, C2 orchestration, OSINT and AI-driven threat analysis — everything in one unified command center.*

[🌐 Live Demo](https://shadowbridge.store) • [📦 Modules](#-the-12-module-arsenal) • [🚀 Deploy](#-deploy) • [💰 Support](#-support)

</div>

---

## 🎯 What is ShadowBridge?

ShadowBridge is a **self-hosted, modular cybersecurity platform** built for security researchers, homelab operators, red/blue teams, and CTF players.

It connects 12 microservices into a single command center — real-time dashboards, honeypots, threat intelligence, C2 orchestration, AI analysis, and 100+ offensive tools — all accessible from your browser.

```bash
shadowbridge@nox ~$ status --all

✓ Monitor        8 machines online · 0 alerts
✓ SENTINEL        Cowrie honeypot active · 12 IPs in blacklist
⚠ SIEM           3 new IDS alerts since last check
✓ VAULT           2 pending crack jobs · ETA 4min
✓ ATLAS           14 TTPs tracked · 6 mitigated
✓ NOX-BRAIN       AI engine online · model: qwen2.5-coder
✓ HexStrike-AI    104 tools loaded · CTF mode ready
⚠ Villain C2      1 active session · 185.220.101.44

shadowbridge@nox ~$ _
```

---

## 🔧 The 12-Module Arsenal

| Module | Description | Type |
|--------|-------------|------|
| 🖥️ **Infrastructure Monitor** | Real-time CPU, RAM, disk, network across all machines. WebSocket-streamed, NAT-friendly. | Monitoring |
| 🍯 **SENTINEL** | Cowrie SSH honeypot + HTTP canary tokens + aggregation API. Every hit TTP-tagged automatically. | Deception |
| 🛡️ **SIEM** | Live Suricata EVE JSON + Wazuh alerts. Kill chain reconstruction from honeypot + IDS correlation. | Detection |
| 🤖 **NOX-BRAIN** | Local AI (Ollama) grounded on live threat context. Multi-model, multi-node AI fleet. | AI Analysis |
| 🌍 **OSIRIS** | OSINT engine — domain enum, IP reputation, breach data, social footprint in one dashboard. | Intelligence |
| ☠️ **Villain C2** | REST bridge over Villain C2. Manage sessions, generate implants, stream loot via API. | Offensive |
| 🔐 **VAULT** | Async hash cracking — John the Ripper + hashcat. RockYou, FastTrack, custom wordlists. | Credentials |
| 🎯 **ATLAS** | Purple team MITRE ATT&CK tracker. 14 tactics, 200+ techniques. Auto-populated from events. | Purple Team |
| ⚡ **NOX-COMMAND** | Kill chain orchestrator: OSINT → Recon → Vuln Scan → Exploitation → C2 → Loot → Report. | Orchestration |
| 💻 **HexStrike-AI** | 104+ security tools with AI parameterization — nmap, sqlmap, nikto, ffuf, hydra and more. | CTF / Pentest |
| 📡 **RuView** | WiFi DensePose — detect presence and vital signs from WiFi signals. No camera required. | Physical Layer |
| 🍯 **HoneyAI** | AI-generated SSH shell that fools attackers into believing they're on a real production server. | Deception |

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    SHADOWBRIDGE PLATFORM                      │
│                                                              │
│   Your Machines        ShadowBridge           AI Fleet       │
│  [agent.py WS] ──▶  [FastAPI + SQLite] ──▶  [Ollama]        │
│                             │                               │
│                             ▼                               │
│                    Threat Intelligence                        │
│                  [SIEM · Honeypot · ATLAS]                   │
│                             │                               │
│                             ▼                               │
│                       Your Browser                           │
│                      [Live Dashboard]                        │
└─────────────────────────────────────────────────────────────┘
```

**Key properties:**
- 🔒 **Agent-based, NAT-friendly** — machines connect outbound, no open ports needed
- 🧩 **Microservice architecture** — each module is independent, independently scalable
- 🤖 **AI-first** — every module feeds context to the AI analysis layer
- ⚡ **WebSocket-streamed** — zero-latency live data, no polling

---

## 🚀 Deploy

```bash
# Clone
git clone https://github.com/webwizardg99/shadowbridge-web
cd shadowbridge-web

# Deploy to your cPanel hosting (requires API token)
bash deploy.sh

# Or open index.html locally
open index.html
```

**Stack:** Pure HTML/CSS/JS — zero dependencies, zero build step. Drop it anywhere.

---

## 📦 Related Repositories

| Repo | Description |
|------|-------------|
| [shadow-lab](https://github.com/webwizardg99/shadow-lab) | Core monitoring backend — FastAPI + SQLite + WebSocket |
| [sentinel](https://github.com/webwizardg99/sentinel) | Multi-source honeypot aggregator |
| [atlas](https://github.com/webwizardg99/atlas) | Purple team MITRE ATT&CK TTP tracker |
| [honeyai](https://github.com/webwizardg99/honeyai) | AI-powered SSH honeypot response generator |
| [hexstrike-ai](https://github.com/webwizardg99/hexstrike-ai) | 150+ security tools MCP framework |

---

## 🎫 Tiers

| Feature | Monitor | Pro | Arsenal |
|---------|---------|-----|---------|
| Real-time machine stats | ✅ | ✅ | ✅ |
| Up to machines | 3 | 10 | ∞ |
| Honeypot + SIEM | — | ✅ | ✅ |
| AI analysis | — | ✅ | ✅ |
| C2 + VAULT + HexStrike | — | — | ✅ |
| OSINT + Kill chain | — | — | ✅ |
| **Price** | **Free** | **$12/mo** | **$49/mo** |

> 🚧 **Early Access:** Monitor is free without registration. Arsenal is currently free for registered users.

---

## 💰 Support

This is a solo-built platform. If it saves you time or sparks ideas, consider supporting:

| Method | Details |
|--------|---------|
| ☕ Buy Me A Coffee | [buymeacoffee.com/86szabadosy](https://buymeacoffee.com/86szabadosy) |
| 💳 Revolut | `@szg86` · IBAN: `LT26 3250 0016 3929` |
| ₿ Bitcoin | `[coming soon]` |
| Ξ Ethereum | `[coming soon]` |

---

## ⚠️ Disclaimer

ShadowBridge Arsenal modules (C2, credential cracking, offensive tools) are for **authorized security testing, CTF competitions, homelab research, and educational purposes only**. You are responsible for ensuring you have proper authorization before using any offensive capabilities.

---

## 📄 License

MIT License — see [LICENSE](LICENSE)

---

<div align="center">

**Built for the shadows. ◈**

[![shadowbridge.store](https://img.shields.io/badge/🌐_shadowbridge.store-00d4ff?style=for-the-badge)](https://shadowbridge.store)
[![GitHub stars](https://img.shields.io/github/stars/webwizardg99/shadowbridge-web?style=social)](https://github.com/webwizardg99/shadowbridge-web)

</div>
