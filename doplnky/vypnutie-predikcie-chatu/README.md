# Automaticke vypnutie predikcie

Tento doplnok pri každom otvorení Codespace automaticky vypne inline návrhy v editore a návrhy ďalšej úpravy. Copilot Chat zostáva zapnutý.

## Automaticke spustenie

Hook `.devcontainer/codespace-start.sh` spustí súbor `disable-predictions.php`. Ten bezpečne doplní tieto hodnoty do lokálneho `.vscode/settings.json` a zachová ostatné nastavenia:

```json
{
	"chat.disableAIFeatures": false,
	"editor.inlineSuggest.enabled": false,
	"github.copilot.nextEditSuggestions.enabled": false
}
```

Rovnaké hodnoty sú v `.devcontainer/devcontainer.json`, takže sa použijú aj pri úplnom vytvorení nového Codespace. Zmena konfigurácie existujúceho kontajnera môže vyvolať ponuku **Znovu postaviť teraz**.

## Rucne spustenie

Z koreňa projektu spustite:

```bash
php doplnky/vypnutie-predikcie-chatu/disable-predictions.php
```

## Opätovne zapnutie

V nastaveniach workspace zapnite `Editor: Inline Suggest Enabled` a `GitHub Copilot: Next Edit Suggestions Enabled`, ak chcete editorové návrhy znovu povoliť. Automatické vypnutie sa však pri ďalšom otvorení Codespace znova aplikuje, kým je tento doplnok zapojený v `.devcontainer/codespace-start.sh`.