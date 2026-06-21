@extends('layouts.app')

@section('title', 'FAQ | WEB-Interia')
@section('description', 'Najčastejšie otázky o službách, atypickej výrobe, termínoch a spolupráci s WEB-Interia.')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="display-6 mb-4">Často kladené otázky</h1>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqOneCollapse" aria-expanded="true" aria-controls="faqOneCollapse">
                        Viete realizovať aj neštandardné rozmery?
                    </button>
                </h2>
                <div id="faqOneCollapse" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Áno. Atypická výroba je jedna z našich hlavných služieb, vrátane prototypov a menších sérií.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwoCollapse" aria-expanded="false" aria-controls="faqTwoCollapse">
                        Ako dlho trvá nacenenie?
                    </button>
                </h2>
                <div id="faqTwoCollapse" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Predbežné nacenenie obvykle pripravíme do 2 pracovných dní, pri zložitejších zadaniach po technickej konzultácii.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThreeCollapse" aria-expanded="false" aria-controls="faqThreeCollapse">
                        Pomôžete aj s výberom materiálu?
                    </button>
                </h2>
                <div id="faqThreeCollapse" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-secondary">
                        Áno. Navrhneme materiál podľa použitia, mechanickej záťaže, prostredia a požadovanej životnosti.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection