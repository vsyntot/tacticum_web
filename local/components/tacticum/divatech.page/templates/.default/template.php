<?php

declare(strict_types=1);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}
$asset = '/local/templates/divatech/assets/';
?>
<main id="main" data-divatech-page>
    <section class="diva-hero" aria-labelledby="diva-title">
        <h1 id="diva-title">DIVA Technologies</h1>
        <a class="diva-hero-next" href="#rec497650901">Source your future<span aria-hidden="true">⌄</span></a>
    </section>
    <section id="rec497650901" class="diva-about diva-container">
        <div><h2>About us <span aria-hidden="true">|</span></h2><p>We provide IT experts for your projects. We are a team of skilled IT-professionals with a deep background in development and implementation of IT-products.</p></div>
        <img src="<?= $asset ?>about.png" alt="Digital technology on a laptop" width="560" height="350" loading="lazy">
    </section>
    <section id="rec497655335" class="diva-expertise">
        <h2>We possess a wide expertise in different areas</h2>
        <div class="diva-grid">
            <article class="diva-card diva-cdp"><img src="<?= $asset ?>cdp.jpg" alt="" loading="lazy"><h3>Client Data Platform (CDP)</h3></article>
            <article class="diva-card diva-cyber"><h3>Cyber Security</h3></article>
            <article class="diva-card diva-risk"><h3>Retail Risk Strategy Automation</h3></article>
            <article class="diva-card diva-marketing"><img src="<?= $asset ?>marketing.jpg" alt="" loading="lazy"><h3>Marketing Automation (RecSys)</h3></article>
            <article class="diva-card diva-markets"><img src="<?= $asset ?>markets.jpg" alt="" loading="lazy"><h3>Capital Markets</h3></article>
        </div>
    </section>
    <section id="rec497691984" class="diva-cooperation">
        <div class="diva-container">
            <h2>We are working with top-notch market technologies</h2>
            <p class="diva-intro">Our team includes both backend, frontend, mobile expertise. We know how to use different technologies, such as Artificial Intelligence and Machine Learning, Cyber Security and Digital Trust, Datafication, Recommender Systems.</p>
            <p class="diva-intro">There are two main areas of cooperation with us:</p>
            <div class="diva-offers">
                <article><h3>We can source you with our professionals</h3><p>We have 40+ Backend Developers.<br>We have 50+ Frontend Developers.<br>We have other roles, including QA Engineers, Business Analysts, System Analysts, DevOps Engineers and Data Scientists</p></article>
                <article><h3>We can make projects together</h3><p>We have an experience in these areas:<br>Banking, Retail, Oil&amp;Gas Industry</p></article>
            </div>
        </div>
    </section>
    <section id="rec497697185" class="diva-contact diva-container">
        <h2>Contact us</h2><p class="diva-intro">Contact us<?= $arResult['FORMS_ENABLED'] ? ' or fill out the feedback form' : '' ?></p>
        <div class="diva-contact-grid">
            <address><a href="tel:+905066212712">+90 506 621 27 12</a><a href="mailto:info@divatech.space">info@divatech.space</a></address>
            <?php if ($arResult['FORMS_ENABLED']): ?>
            <?php $formKind = 'contact'; require __DIR__ . '/form.php'; ?>
            <?php else: ?>
            <p>Send a request by email or call us. We will contact you soon.</p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php if ($arResult['FORMS_ENABLED']): ?>
<button class="diva-callback" type="button" aria-haspopup="dialog" aria-controls="diva-callback-dialog" aria-label="Request a call">☎</button>
<dialog id="diva-callback-dialog" aria-labelledby="diva-callback-title">
    <button class="diva-dialog-close" type="button" aria-label="Close">×</button>
    <h2 id="diva-callback-title">Send a request, please.</h2><p>We will contact you soon.</p>
    <?php $formKind = 'callback'; require __DIR__ . '/form.php'; ?>
    <p>You can also call us yourself:<br><a href="tel:+905066212712">+90 506 621 27 12</a></p>
</dialog>
<?php else: ?>
<a class="diva-callback" href="tel:+905066212712" aria-label="Call DIVA Technologies">☎</a>
<?php endif; ?>
