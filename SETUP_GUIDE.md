# ShadowBridge Store — Frissítés Útmutató

## ✅ Végrehajtott módosítások

Az alábbi elemeket már beépítettük az `index.html`-be:

### 1. 🚧 Early Access Banner
**Pozíció:** Nav előtt, top szekció
- Jól látható teszt módú figyelmeztetés
- Monitor tier: ingyenes, regisztráció nélkül
- Arsenal tier: regisztrációval, jelenleg ingyenes

### 2. ⚠️ Arsenal Disclaimer
**Pozíció:** Arsenal tier kártya
- Csak regisztrált felhasználóknak
- Oktatási/jogos célra (pentesting, CTF, lab)
- Felelősségteljes használat

### 3. 💰 Support Szekció
**Pozíció:** Tiers után, táblázat előtt
- **Revolut:** Revtag + IBAN
- **Buy Me A Coffee:** Link
- **Bitcoin:** Cím placeholder
- Barátságos, profizionális szöveg

### 4. 📋 Privacy/Terms Linkek
**Pozíció:** Footer linkek között
- `/privacy` és `/terms` útvonalak

---

## 📌 Kész HTML Snippetek (újrafelhasználható)

### A. Early Access Banner
Használható más helyekre vagy a tábla helyébe:

```html
<!-- ── EARLY ACCESS BANNER ── -->
<div style="background:linear-gradient(90deg,rgba(139,92,246,.15),rgba(0,212,255,.1));border-bottom:1px solid rgba(139,92,246,.3);padding:12px 20px;text-align:center;font-size:.8rem;color:var(--fg);letter-spacing:.5px;">
  🚧 <strong>Teszt / Early Access módban</strong> — A platform jelenleg fejlesztés alatt van. <span style="color:var(--muted);">Monitor tier: ingyenes, regisztráció nélkül. Arsenal: regisztrációval ingyenes jelenleg.</span>
</div>
```

### B. Arsenal Disclaimer
Már beépítve, de szerkeszthető:

```html
<div style="background:rgba(255,59,92,.08);border:1px solid rgba(255,59,92,.2);border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:.72rem;color:var(--muted);line-height:1.5;">
  ⚠️ <strong>Registered users only</strong> · For authorized penetesting, CTF, lab use, and educational purposes · Responsible use expected.
</div>
```

### C. Support Szekció (teljes)
```html
<!-- Support Section -->
<div style="margin-top:64px;padding:40px;background:linear-gradient(135deg,rgba(0,212,255,.06),rgba(139,92,246,.06));border:1px solid rgba(0,212,255,.15);border-radius:14px;text-align:center;">
  <span class="section-tag" style="display:block;margin-bottom:10px;">Támogatás (nem kötelező)</span>
  <h3 style="font-size:1.3rem;margin-bottom:8px;font-weight:700;">Támogass minket</h3>
  <p style="color:var(--muted);margin-bottom:28px;max-width:520px;margin-left:auto;margin-right:auto;font-size:.9rem;">
    A platform fejlesztése időigényes projekt. Minden támogatás segít, hogy újabb modulokat fejlesszünk, javítsuk az AI-t, és a platformot mindig működőképesen tartsuk.
  </p>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;max-width:700px;margin:0 auto;">

    <!-- Revolut Card -->
    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(0,212,255,.15);border-radius:10px;padding:20px;text-align:center;">
      <div style="font-size:1.8rem;margin-bottom:8px;">💳</div>
      <div style="font-weight:700;margin-bottom:6px;color:var(--fg);">Revolut</div>
      <div style="font-size:.75rem;color:var(--muted);margin-bottom:12px;">EU-s transzfer, pillanatnyi.</div>
      <div style="background:rgba(255,255,255,.04);border-radius:6px;padding:8px;margin-bottom:10px;">
        <div style="font-size:.7rem;color:var(--muted);margin-bottom:4px;text-transform:uppercase;">Revtag</div>
        <div style="font-family:monospace;font-size:.8rem;color:var(--cyan);">@szg86</div>
      </div>
      <div style="background:rgba(255,255,255,.04);border-radius:6px;padding:8px;">
        <div style="font-size:.7rem;color:var(--muted);margin-bottom:4px;text-transform:uppercase;">IBAN</div>
        <div style="font-family:monospace;font-size:.75rem;color:var(--cyan2);">LT26 3250 0016 3929</div>
      </div>
    </div>

    <!-- Buy Me A Coffee Card -->
    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(251,191,36,.15);border-radius:10px;padding:20px;text-align:center;">
      <div style="font-size:1.8rem;margin-bottom:8px;">☕</div>
      <div style="font-weight:700;margin-bottom:6px;color:var(--fg);">Buy Me A Coffee</div>
      <div style="font-size:.75rem;color:var(--muted);margin-bottom:12px;">Kártya, Apple Pay, Google Pay.</div>
      <a href="https://buymeacoffee.com/86szabadosy" target="_blank" rel="noopener" class="btn btn-outline" style="width:100%;text-align:center;border-color:rgba(251,191,36,.4);color:#fbbf24;">
        Látogass el
      </a>
    </div>

    <!-- Bitcoin Card -->
    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,140,66,.15);border-radius:10px;padding:20px;text-align:center;">
      <div style="font-size:1.8rem;margin-bottom:8px;">₿</div>
      <div style="font-weight:700;margin-bottom:6px;color:var(--fg);">Bitcoin</div>
      <div style="font-size:.75rem;color:var(--muted);margin-bottom:12px;">Az anonimnak köszönheti.</div>
      <div style="background:rgba(255,255,255,.04);border-radius:6px;padding:10px;">
        <div style="font-size:.7rem;color:var(--muted);margin-bottom:6px;text-transform:uppercase;">Cím</div>
        <div style="font-family:monospace;font-size:.65rem;color:var(--orange);word-break:break-all;">[ITT LESZ]</div>
      </div>
    </div>

  </div>

  <div style="margin-top:24px;padding-top:24px;border-top:1px solid rgba(255,255,255,.08);">
    <p style="font-size:.8rem;color:var(--muted);">Közösség = Fejlesztés. Köszönjük az ötleteket, bug reportokat és tanács adást. — <strong>wizardg</strong></p>
  </div>
</div>
```

---

## 🎯 Placement Summary

| Elem | Pozíció | Fájl | Sorszám |
|---|---|---|---|
| **Early Access Banner** | Nav előtt, top | index.html | ~375–378 |
| **Arsenal Disclaimer** | Arsenal tier kártya | index.html | ~724–728 |
| **Support Szekció** | Tiers után, táblázat előtt | index.html | ~746–813 |
| **Privacy/Terms Linkek** | Footer | index.html | ~869–871 |

---

## 🔧 Kitöltendő Adatok

Jelenleg placeholder-ek ezek:

### Bitcoin Cím
```html
<div style="font-family:monospace;font-size:.65rem;color:var(--orange);word-break:break-all;">[BITCOIN CÍM]</div>
```
→ Helyettesítsd a tényleges BC1Q... címmel

### Ethereum Cím
```html
<div style="font-family:monospace;font-size:.65rem;color:#a78bfa;word-break:break-all;">[ETHEREUM CÍM]</div>
```
→ Helyettesítsd a tényleges 0x... címmel (ERC-20, Polygon, Base, stb.)

### Privacy/Terms Oldalak
A footer linkeket a tényleges `/privacy` és `/terms` útvonalakra szeretnéd irányítani.
Opciók:
- Külön HTML fájlok (`privacy.html`, `terms.html`)
- Dinamikus útvonalak (ha backend van)
- Google Docs / Termly integráció (embed)

---

## 🎨 Stíluscsomagok (optional refinements)

Ha szeretnéd az ensemble-t módosítani, íme a CSS változók az oldal tetején (`<style>`):

```css
--cyan: #00d4ff          /* Primer szín */
--cyan2: #00ff9d         /* Accent zöld */
--red: #ff3b5c           /* Arsenal/danger szín */
--orange: #ff8c42        /* Warning szín */
--purple: #8b5cf6        /* Teszt/magic szín */
--bg: #060810            /* Háttér */
--surface: #0d1117       /* Kártyák *)
--muted: #6b7280         /* Szöveg szürke *)
```

---

## 🚀 Gyors Deployment

```bash
cd ~/shadowbridge-web
git add -A
git commit -m "Add Early Access banner, Support section, Arsenal disclaimer & Privacy/Terms links"
git push origin main
```

---

## ✨ Jövőbeli Ötletek

- [ ] Live support chat (FloatUI vagy Crisp integráció)
- [ ] Monthly supporter spotlight ("Thanks to...")
- [ ] Donation progress bar (cél: $X/hó)
- [ ] Testimonials szekció (user reviews)
- [ ] FAQ szekció (pricing, tech, legal kérdések)

---

**Kész az oldal frissítésre!** Gyakorlati kérdés: Milyen Bitcoin címet akaransz megadni?
