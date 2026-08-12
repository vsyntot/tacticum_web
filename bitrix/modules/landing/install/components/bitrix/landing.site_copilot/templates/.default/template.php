<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var array $arResult */
/** @var string $templateFolder */

use Bitrix\Landing\Copilot\Services\NameService;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

Extension::load([
	'landing_master',
	'landing.ui.button.basebutton',
	'landing.copilot.generation-observer',
	'ui.icon-set.actions',
	'ui.a11y',
	'ui.design-tokens',
	'intranet.sidepanel.bindings',
]);

Manager::setPageTitle(NameService::replaceCopilotName(Loc::getMessage('LANDING_SITE_PAGE_TITLE_MSGVER_1')));

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'landing-site-copilot-body');
$widgetBotId = (int)($arResult['AI_ASSISTANT_BOT_ID'] ?? 0);
$isAiAssistantChatExpandable = $widgetBotId > 0;
$isAiAssistantToggleDisabled = !$isAiAssistantChatExpandable;
$aiAssistantPanelClass = 'landing-site-copilot-ai landing-ai-assistant-panel';
if (!$isAiAssistantChatExpandable)
{
	$aiAssistantPanelClass .= ' landing-ai-assistant-panel--chat-minimized';
}
if ($isAiAssistantChatExpandable)
{
	Extension::load('landing.aiassistant.widgetpanel');
}
$imApplicationData = Json::encode($arResult['IM_APPLICATION_DATA'] ?? []);

$pathTemplate24 = getLocalPath('templates/' . Manager::getTemplateId(Manager::getMainSiteId()));

$asset = Asset::getInstance();
$cssFiles = [
	$pathTemplate24 . '/theme.css',
	$pathTemplate24 . '/assets/vendor/bootstrap/bootstrap.css',
	'/bitrix/components/bitrix/landing.landing_view/templates/.default/style.css',
	'/bitrix/components/bitrix/landing.selector/templates/.default/style.css',
];

foreach ($cssFiles as $cssFile)
{
	$asset->addCSS($cssFile);
}

$previewImagePath = 'background-image: url(' . $templateFolder . '/image/landing-site-ai-icon.svg);';
?>

<div class="<?= $aiAssistantPanelClass ?>">
	<div class="landing-site-copilot-ai-site landing-ai-assistant-panel__main">
		<div class="landing-ui-panel landing-ui-panel-top">
			<div class="landing-ui-panel-top-logo">
				<a href="/sites/" class="landing-ui-panel-top-logo-link" data-slider-ignore-autobinding="true" tabindex="0" target="_top">
					<span class="landing-ui-panel-top-logo-home-btn" data-hint="Выйти из редактора" data-hint-no-icon="" data-hint-init="y">
						<svg class="landing-ui-panel-top-logo-home-btn-icon" width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M11.902 19.6877V15.8046C11.902 15.5837 12.0811 15.4046 12.302 15.4046H14.5087C14.7296 15.4046 14.9087 15.5837 14.9087 15.8046V19.6877C14.9089 19.9086 15.0879 20.0876 15.3087 20.0878L18.8299 20.0891C19.0508 20.0893 19.2299 19.9103 19.23 19.6894C19.23 19.6893 19.23 19.6893 19.2299 19.6892V13.4563C19.2299 13.4365 19.2275 13.4142 19.2275 13.3943H20.4332C20.6633 13.3943 20.8604 13.2883 20.9909 13.0932C21.1189 12.9005 21.1425 12.6747 21.0581 12.4561C20.9519 12.1816 14.2383 5.92948 14.2047 5.90379C13.7957 5.59077 13.3216 5.58796 12.9131 5.89536C12.8759 5.92337 6.15525 12.1815 6.04901 12.4561C5.96462 12.6729 5.99059 12.9011 6.11629 13.0932C6.24671 13.2859 6.44145 13.3943 6.67162 13.3943H7.87965C7.87729 13.4142 7.87729 13.4365 7.87729 13.4563V19.6846C7.8776 19.9054 8.0565 20.0844 8.27729 20.0849L11.502 20.0874C11.7229 20.0879 11.9021 19.9089 11.9023 19.688C11.9023 19.6879 11.9023 19.6878 11.902 19.6877Z" fill="#525C69"></path>
						</svg>
					</span>
					<span class="landing-ui-panel-top-logo-text">
						<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LOGO') ?>
					</span>
					<span class="landing-ui-panel-top-logo-color">
						<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LOGO_24') ?>
					</span>
					<span class="landing-ui-panel-top-logo-icon far fa-clock-three"></span>
					<span class="landing-ui-panel-top-logo-text left-spaced">
						<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LOGO_SITES_MSGVER_1') ?>
					</span>
				</a>
			</div>
			<div class="landing-ui-panel-disabled">
				<div class="landing-ui-panel-top-selector">
					<label class="landing-selector-container" id="landing-selector">
						<span class="landing-selector-result-picture" id="landing-selector-picture" style="<?= $previewImagePath ?>"></span>
						<input
							class="landing-selector-input-text"
							id="landing-selector-input"
							tabindex="0"
							value="<?= NameService::replaceCopilotName(Loc::getMessage('LANDING_CMP_TOP_PANEL_SITE_NAME_MSGVER_1')) ?>"
						>
					</label>
				</div>
				<div class="landing-ui-panel-top-menu" id="landing-panel-settings">
					<div id="landing-popup-preview-btn" class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu">
						<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_PREVIEW_BTN_TEXT') ?>
					</div>
					<input
						class="ui-btn ui-btn-light-border ui-btn-round landing-ui-panel-top-menu-link-features"
						id="landing-popup-features-btn"
						type="button"
						tabindex="0"
						value="<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_FEATURES_BTN_TEXT') ?>"
					>
				</div>
			</div>
			<div class="landing-site-copilot-ai-chat-toggle-container landing-ai-assistant-panel__toggle-container">
				<button
					class="ui-btn ui-btn-light-border ui-btn-round landing-site-copilot-ai-chat-toggle landing-ai-assistant-panel__toggle"
					type="button"
					tabindex="0"
					aria-expanded="<?= $isAiAssistantChatExpandable ? 'true' : 'false' ?>"
					<?= $isAiAssistantToggleDisabled ? 'disabled' : '' ?>
				>
					<?= Loc::getMessage('LANDING_COPILOT_AI_CHAT_TOGGLE_CLOSE') ?>
				</button>
			</div>
		</div>
		<div class="landing-site-copilot-ai-placeholder"></div>
	</div>
	<?php if ($isAiAssistantChatExpandable): ?>
		<div class="landing-site-copilot-ai-chat landing-ai-assistant-panel__chat" aria-hidden="false">
			<div class="right-panel-ai-chat --ui-context-content-light">
				<div class="right-panel-ai-chat__content landing-ai-assistant-panel__widget"></div>
				<div class="right-panel-ai-chat__background landing-ai-assistant-panel__background"></div>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
	BX.ready(() => {
		<?php if ($isAiAssistantChatExpandable): ?>
			new BX.Landing.AiAssistant.WidgetPanel({
				rootContainer: document.querySelector('.landing-site-copilot-ai'),
				widgetBotId: <?= $widgetBotId ?>,
				imApplicationData: <?= $imApplicationData ?>,
				openText: '<?= CUtil::JSEscape(Loc::getMessage('LANDING_COPILOT_AI_CHAT_TOGGLE_OPEN')) ?>',
				closeText: '<?= CUtil::JSEscape(Loc::getMessage('LANDING_COPILOT_AI_CHAT_TOGGLE_CLOSE')) ?>',
				logPrefix: 'Landing site copilot default template',
			}).init();
		<?php endif; ?>

		let isPreviewImageCreated = false;
		let isBlocksCreated = false;
		let siteId = null;
		let landingId = null;
		let generationId = null;

		const redirectToEditor = (redirectSiteId, redirectLandingId, redirectGenerationId) => {
			if (
				redirectSiteId > 0
				&& redirectLandingId > 0
				&& redirectGenerationId > 0
			)
			{
				// todo: change hardcoded path, get url from component params
				window.location.href = `/sites/site/${redirectSiteId}/view/${redirectLandingId}/?site_generated=${redirectGenerationId}`;
			}
		};

		BX.PULL.subscribe({
			type: 'server',
			moduleId: 'landing',
			callback: (eventData) => {
				const eventParams = eventData.params || {};

				if (
					eventParams.generationId !== undefined
					&& generationId !== null
					&& eventParams.generationId !== generationId
				)
				{
					return;
				}

				if (eventData.command === 'LandingCopilotGeneration:onGenerationCreate')
				{
					generationId = eventParams.generationId;

					const observer = new BX.Landing.Copilot.GenerationObserver(generationId);
					observer.observe();
				}

				if (eventData.command === 'LandingCopilotGeneration:onPreviewImageCreate')
				{
					isPreviewImageCreated = true;
					if (isBlocksCreated && siteId && landingId)
					{
						redirectToEditor(siteId, landingId, generationId);
					}
				}

				if (eventData.command === 'LandingCopilotGeneration:onBlocksCreated')
				{
					if (eventParams.siteId && eventParams.landingId && eventParams.generationId)
					{
						isBlocksCreated = true;
						siteId = eventParams.siteId;
						landingId = eventParams.landingId;

						if (isPreviewImageCreated)
						{
							redirectToEditor(siteId, landingId, generationId);
						}
					}
				}
			},
		});
	});
</script>
