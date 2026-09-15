@extends('layouts.app')

@section('title', 'Interná zóna | WEB-Interia')

@section('content')
<section class="internal-shell py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="text-uppercase small text-primary fw-semibold mb-2">Interná zóna</p>
                <h1 class="h2 mb-2">Pracovný prehľad</h1>
                <p class="text-secondary mb-0">Prihlásený: {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</p>
            </div>
            <a class="btn btn-outline-primary" href="{{ route('customer.account.edit') }}">Môj účet</a>
        </div>

        @include('internal._navigation')

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4"><div class="internal-stat"><span>Produkty</span><strong>{{ $productCount }}</strong></div></div>
            <div class="col-12 col-md-4"><div class="internal-stat"><span>Zákazníci</span><strong>{{ $customerCount }}</strong></div></div>
            <div class="col-12 col-md-4"><div class="internal-stat"><span>Interní pracovníci</span><strong>{{ $workerCount }}</strong></div></div>
        </div>

        <div class="internal-panel">
            <h2 class="h4 mb-3">Pracovné moduly</h2>
            <div class="d-flex flex-wrap gap-2">
                @can('manage-products')
                    <a class="btn btn-primary" href="{{ route('internal.products.index') }}">Spravovať produkty</a>
                @endcan
                @can('manage-users')
                    <a class="btn btn-outline-primary" href="{{ route('internal.users.index') }}">Spravovať používateľov</a>
                @endcan
            </div>
        </div>

        <section class="mt-4 pt-4 border-top" aria-labelledby="advertisingGuideTitle">
            <h2 class="h4 mb-3" id="advertisingGuideTitle">Reklamy na úvodnej stránke</h2>
            <p>Reklamný pás pod videom zobrazí jednu až 30 reklám v poradí nastavenia. Môžete kombinovať obrázky a videá s rôznymi šírkami. Keď sa nezmestia vedľa seba, pás sa posúva vodorovne aj na počítači.</p>
            <details>
                <summary class="fw-semibold text-primary">Návod na vloženie obrázkov a videí</summary>
                <div class="pt-3">
                    <p>Priame nahrávanie cez administráciu zatiaľ nie je dostupné. Nasledujúci postup vyžaduje prístup k súborom webu; podklady môže vložiť správca webu.</p>
                    <ol>
                        <li class="mb-2">Obrázok vo formáte WebP, JPG alebo PNG vložte do <code>public/images</code>. Video MP4 (H.264) alebo WebM vložte do <code>public/videos</code>. Použite krátke reklamné video bez zvukových informácií a pripravte aj náhľadový obrázok.</li>
                        <li class="mb-2">V súbore <code>config/home.php</code> doplňte pole <code>advertisements</code> podľa príkladu nižšie. <code>title</code> je zrozumiteľný názov ponuky, <code>url</code> je cieľový odkaz. Cesty k médiám sa zapisujú bez časti <code>public</code>.</li>
                        <li class="mb-2">Pre obrázok použite <code>image</code>. Pre video použite <code>video</code> a <code>image</code> ponechajte ako náhľad. Video návštevník spustí sám a odkaz na ponuku je uvedený samostatne pod ním.</li>
                        <li class="mb-2">Voliteľné <code>weight</code> nastavuje šírkovú váhu 1 až 5 (predvolene 1). Napríklad hodnoty 1, 3, 2 vytvoria úzky, široký a stredný blok. Rovnako veľké váhy rozdelia priestor rovnomerne. Pás zostáva nízky; väčší počet reklám je dostupný posúvaním, nie v 30 stlačených políčkach naraz.</li>
                        <li class="mb-2">Po uložení spustite na serveri <code>php artisan config:clear</code> a obnovte úvodnú stránku. Overte zobrazenie aj cieľové odkazy na počítači a mobile.</li>
                    </ol>
                    <pre class="bg-light p-3 rounded overflow-auto"><code>'advertisements' =&gt; [
    [
        'title' =&gt; 'Názov obrázkovej ponuky',
        'image' =&gt; '/images/reklama.webp',
        'weight' =&gt; 1,
        'url' =&gt; '/materialy-eshop',
    ],
    [
        'title' =&gt; 'Názov video ponuky',
        'image' =&gt; '/images/reklama-video.webp',
        'video' =&gt; '/videos/reklama.mp4',
        'weight' =&gt; 3,
        'url' =&gt; '/vyroba-na-mieru',
    ],
],</code></pre>
                    <p>Pre jednu reklamu je vhodný široký podklad, napríklad 1200 × 240 px. Pri viacerých reklamách pripravte kompaktnejšie podklady a čitateľný text. Médiá sa neorezávajú. Súbory pred vložením optimalizujte pre rýchle načítanie.</p>
                    <p class="mb-0">Zobrazuje sa najviac prvých 30 položiek. Odstránením položky reklamu skryjete; prázdne pole <code>'advertisements' =&gt; []</code> ponechá voľný pás. Vkladajte iba verejné podklady, ku ktorým máte práva, a vlastné cesty alebo odkazy HTTPS. Odkaz na YouTube nie je súbor videa a do poľa <code>video</code> nepatrí.</p>
                </div>
            </details>
        </section>
    </div>
</section>
@endsection