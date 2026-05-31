(function (window, document) {
  'use strict';

  var counterId = 103471113;
  var scriptUrl = 'https://mc.yandex.ru/metrika/tag.js?id=' + counterId;

  window.ym = window.ym || function () {
    (window.ym.a = window.ym.a || []).push(arguments);
  };
  window.ym.l = window.ym.l || 1 * new Date();

  for (var index = 0; index < document.scripts.length; index += 1) {
    if (document.scripts[index].src === scriptUrl) {
      window.ym(counterId, 'init', {
        ssr: true,
        webvisor: true,
        clickmap: true,
        accurateTrackBounce: true,
        trackLinks: true,
      });
      return;
    }
  }

  var script = document.createElement('script');
  var firstScript = document.getElementsByTagName('script')[0];
  var parent = firstScript && firstScript.parentNode ? firstScript.parentNode : document.head || document.documentElement;
  script.async = true;
  script.src = scriptUrl;
  parent.insertBefore(script, firstScript || null);

  window.ym(counterId, 'init', {
    ssr: true,
    webvisor: true,
    clickmap: true,
    accurateTrackBounce: true,
    trackLinks: true,
  });
})(window, document);
