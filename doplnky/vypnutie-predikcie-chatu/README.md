# Vypnutie predikcie v chate

Tento doplnok popisuje, ako v Codespace zastaviť AI odpovede a návrhy v Copilot Chat. Netýka sa Laravel aplikácie, portu `8000`, automatického štartu ani záloh.

## Dôležité obmedzenie

Predikcia pri písaní v Copilot Chat patrí klientovi VS Code, nie Codespace kontajneru. Skripty `postStartCommand` a `postAttachCommand` preto túto funkciu nemôžu spoľahlivo vypnúť. Copilot Chat zároveň nemá samostatné workspace nastavenie iba pre predikciu textu v chatovom poli.

## Zastavenie Copilot Chat

V otvorenom Codespace otvorte Nastavenia VS Code a vyhľadajte `Chat: Disable AI Features`. Zapnite toto nastavenie pre workspace `WEB-Interia`.

Tým sa vypnú AI funkcie chatu v aktuálnom workspace, teda aj odpovede a návrhy počas písania. Nastavenie sa vykonáva na strane VS Code a nezačne ani nezastaví žiadny proces v aplikácii.

## Opätovné zapnutie

V rovnakých nastaveniach vypnite `Chat: Disable AI Features`. Copilot Chat bude opäť dostupný bez potreby reštartovať Laravel server alebo Codespace.

## Alternatíva pre dopĺňanie kódu

Ak chcete ponechať Copilot Chat, ale vypnúť iba predikcie v editore kódu, v Nastaveniach vypnite `Editor: Inline Suggest Enabled`. Toto nastavenie neriadi písanie v chatovom poli.